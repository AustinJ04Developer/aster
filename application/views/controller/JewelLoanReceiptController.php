<?php
/**
 * Jewel Loan Receipt Controller
 * Handles receipt creation, interest calculation, jewel release, receipt search, and receipt view
 */
require_once __DIR__ . '/../../settings/db.php';
require_once 'model/JewelLoanModel.php';

class JewelLoanReceiptController {
    private $conn;
    private $model;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanModel($this->conn);
    }

    public function handleRequest() {
        $data = [
            'success' => '',
            'errors' => [],
            'selectedLoan' => null,
            'receipt' => null,
            'recentLoans' => [],
            'companyBanks' => $this->model->getCompanyBanks(),
            'upiTypes' => $this->model->getUpiTypes(),
            'nextReceiptNo' => $this->model->getNextReceiptNumber(),
            'searchQuery' => trim($_GET['q'] ?? ($_POST['search_query'] ?? '')),
            'action' => $_GET['action'] ?? ($_POST['receipt_action'] ?? 'search'),
            'activeDNumbers' => $this->model->getAllActiveLoanDNumbers()
        ];

        // AJAX search endpoint for D-Number lookup
        if (isset($_GET['ajax_calculate_schedule'])) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $ldId = (int)($_GET['loan_detail_id'] ?? 0);
            $asOf = trim($_GET['as_of_date'] ?? date('Y-m-d'));
            $target = trim($_GET['target_date'] ?? '');
            $sched = $this->model->calculateLoanInterestSchedule($ldId, $asOf, $target);
            if ($sched) {
                echo json_encode(['status' => 'success', 'data' => $sched]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to calculate schedule']);
            }
            exit;
        }

        if (isset($_GET['ajax_search_dnumber'])) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $searchD = trim($_GET['ajax_search_dnumber']);
            $normD = $this->model->normalizeDNumber($searchD);
            $summary = $this->model->getLoanReceiptSummary(0, $searchD);
            if (!$summary && $normD !== $searchD) {
                $summary = $this->model->getLoanReceiptSummary(0, $normD);
            }
            if ($summary && !empty($summary['loan'])) {
                echo json_encode(['status' => 'success', 'data' => $summary]);
            } else {
                echo json_encode(['status' => 'error', 'message' => "No matching loan found for D-Number '{$searchD}'."]);
            }
            exit;
        }

        // 1. Process Receipt Submission (Save or Generate Receipt)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['submit_receipt']) || isset($_POST['action_type']))) {
            $this->processReceiptSubmission($data);
        }

        // 2. Fetch specific receipt for viewing/printing if receipt_id or receipt_no provided
        $viewReceiptId = (int)($_GET['receipt_id'] ?? 0);
        $viewReceiptNo = trim($_GET['receipt_no'] ?? '');
        if ($viewReceiptId > 0 || !empty($viewReceiptNo)) {
            $data['receiptView'] = $this->model->getReceiptById($viewReceiptId, $viewReceiptNo);
        }

        // 3. D-Number / Loan Selection for Receipt Processing
        $dNumber = trim($_GET['dnum'] ?? ($_GET['d_number'] ?? ($_GET['search_d_number'] ?? ($_POST['d_number'] ?? ($_POST['search_d_number'] ?? '')))));
        $loanDetailId = (int)($_GET['id'] ?? ($_GET['loan_detail_id'] ?? ($_POST['loan_detail_id'] ?? 0)));

        if ($loanDetailId > 0 || !empty($dNumber)) {
            $data['selectedLoan'] = $this->model->getLoanReceiptSummary($loanDetailId, $dNumber);
            if (!$data['selectedLoan'] && !empty($dNumber)) {
                $normD = $this->model->normalizeDNumber($dNumber);
                if ($normD !== $dNumber) {
                    $data['selectedLoan'] = $this->model->getLoanReceiptSummary($loanDetailId, $normD);
                }
            }
            if (!$data['selectedLoan']) {
                $searchIdent = !empty($dNumber) ? $dNumber : "ID #{$loanDetailId}";
                $data['errors'][] = "No active loan account found for D-Number '{$searchIdent}'. Please verify and try again.";
            }
        }

        // 4. Default / Search loan listings
        $data['searchResults'] = $this->model->searchLoansForReceipt($data['searchQuery']);

        return $data;
    }

        private function processReceiptSubmission(&$data) {
        $loanDetailId   = (int)($_POST['loan_detail_id'] ?? 0);
        $dNumber        = trim($_POST['d_number'] ?? '');
        $txnType        = trim($_POST['transaction_type'] ?? 'INTEREST_PAYMENT');
        $paymentMethod  = trim($_POST['payment_method'] ?? 'Cash');
        
        $rawDate = trim($_POST['receipt_date'] ?? '');
        if (!empty($rawDate)) {
            if (strpos($rawDate, '/') !== false) {
                $dParts = explode('/', $rawDate);
                if (count($dParts) === 3) {
                    $receiptDate = sprintf('%04d-%02d-%02d', (int)$dParts[2], (int)$dParts[1], (int)$dParts[0]);
                } else {
                    $receiptDate = date('Y-m-d');
                }
            } else {
                $receiptDate = date('Y-m-d', strtotime($rawDate));
            }
        } else {
            $receiptDate = date('Y-m-d');
        }
        
        $currentBalance = (float)($_POST['current_balance_loan'] ?? 0);
        $totalPaid      = (float)($_POST['total_paid'] ?? 0);
        $periodFrom     = !empty($_POST['period_from']) ? date('Y-m-d', strtotime($_POST['period_from'])) : null;
        $periodTo       = !empty($_POST['period_to']) ? date('Y-m-d', strtotime($_POST['period_to'])) : null;
        $remarks        = trim($_POST['remarks'] ?? '');

        // Validation
        if ($loanDetailId <= 0 || empty($dNumber)) {
            $data['errors'][] = "Invalid loan selected. Loan ID and D-Number are required.";
            return;
        }

        if (!in_array($txnType, ['LOAN_CREATION', 'INTEREST_PAYMENT', 'JEWEL_RELEASE'], true)) {
            $data['errors'][] = "Invalid transaction type selected.";
            return;
        }

        $rateApplied = 18.00;
        $overdueRate = null;
        $overduePaid = 0.00;
        $overdueFrom = null;
        $overdueTo = null;
        $defaultInterestPaid = 0.00;
        $appraiserCharge = 0.00;
        $processingFee = 0.00;
        $breakdownJson = null;

        if ($txnType === 'JEWEL_RELEASE') {
            // For jewel release, calculate accrued interest up to release date
            $schedule = $this->model->calculateLoanInterestSchedule($loanDetailId, $receiptDate);
            $principalPaid = (float)($schedule['currentPrincipal'] ?? $currentBalance);
            $interestPaid = (float)($schedule['totalInterestDue'] ?? 0.00);
            
            // Check minimum 15-day rule for quick release within 15 days of loan date
            $origLoanDate = new DateTime($schedule['loanDate']);
            $relDate = new DateTime($receiptDate);
            $elapsedDays = $origLoanDate->diff($relDate)->days;
            if ($elapsedDays <= 15) {
                // Minimum 15-day interest @ 6%
                $interestPaid = $this->model->calculateDailyInterest($principalPaid, 6.00, 15);
                $rateApplied = 6.00;
            } else {
                $rateApplied = (float)($schedule['applicableBaseRate'] ?? 18.0);
            }

            // Processing fee: 1% of principal collected ONLY once at final settlement/release
            $pfPaid = $this->model->isProcessingFeePaid($loanDetailId);
            $processingFee = $pfPaid ? 0.00 : $this->model->getProcessingFee($schedule['originalPrincipal']);
            $appraiserCharge = 0.00; // Never collected at release
            $newBalance = 0.00;

            if ($totalPaid <= 0) {
                $totalPaid = round($principalPaid + $interestPaid + $processingFee, 2);
            }
            $periodFrom = $schedule['paidThroughDate'] ?? $receiptDate;
            $periodTo = $receiptDate;
            $breakdownJson = json_encode(['type' => 'JEWEL_RELEASE', 'schedule' => $schedule]);
        } else {
            // INTEREST_PAYMENT: Allocate payment to overdue interest, regular interest, and excess to principal
            $schedule = $this->model->calculateLoanInterestSchedule($loanDetailId, $receiptDate, $periodTo);
            $appraiserCharge = 0.00; // Never collected with interest
            $processingFee = 0.00;   // Never collected with interest

            if ($totalPaid <= 0 && $schedule) {
                $totalPaid = $schedule['totalInterestDue'];
            }

            $allocation = $this->model->allocatePayment($loanDetailId, $totalPaid, $receiptDate, $periodTo);
            if ($allocation) {
                $interestPaid = $allocation['totalInterestPaid'];
                $principalPaid = $allocation['principalPaid'];
                $newBalance = $allocation['newPrincipalBalance'];
                $overduePaid = $allocation['overdueInterestPaid'];
            } else {
                $interestPaid = min($totalPaid, $schedule['totalInterestDue']);
                $principalPaid = max(0.00, round($totalPaid - $interestPaid, 2));
                $newBalance = max(0.00, round($currentBalance - $principalPaid, 2));
            }

            $rateApplied = (float)($schedule['regularBreakdown']['rate'] ?? 18.0);
            if ($schedule['overdueBreakdown']['isOverdue']) {
                $overdueRate = (float)$schedule['overdueBreakdown']['rate'];
                $overdueFrom = $schedule['overdueBreakdown']['periodFrom'];
                $overdueTo = $schedule['overdueBreakdown']['periodTo'];
            }

            if (empty($periodFrom)) {
                $periodFrom = $schedule['overdueBreakdown']['isOverdue'] 
                    ? $schedule['overdueBreakdown']['periodFrom'] 
                    : $schedule['regularBreakdown']['periodFrom'];
            }
            if (empty($periodTo)) {
                $periodTo = $schedule['regularBreakdown']['periodTo'] ?: ($schedule['overdueBreakdown']['periodTo'] ?: $receiptDate);
            }

            $breakdownJson = json_encode(['type' => 'INTEREST_PAYMENT', 'schedule' => $schedule, 'allocation' => $allocation]);
        }

        if ($totalPaid <= 0) {
            $data['errors'][] = "Total paid amount must be greater than zero.";
            return;
        }

        $interestMonths = round((new DateTime($periodFrom))->diff(new DateTime($periodTo))->days / 30.4167, 2);

        // Payment details
        $chequeNumber = trim($_POST['cheque_number'] ?? '');
        $chequeFromBank = trim($_POST['cheque_from_bank'] ?? '');
        $chequeToBank = trim($_POST['cheque_to_bank'] ?? '');

        $rtgsNumber = trim($_POST['rtgs_number'] ?? '');
        $rtgsFromBank = trim($_POST['rtgs_from_bank'] ?? '');
        $rtgsToBank = trim($_POST['rtgs_to_bank'] ?? '');

        $upiType = trim($_POST['upi_type'] ?? '');
        $upiTxn = trim($_POST['upi_transaction_number'] ?? '');
        $upiToBank = trim($_POST['upi_to_bank'] ?? '');

        $actionType = trim($_POST['action_type'] ?? 'generate');

        $receiptData = [
            'ReceiptDate' => $receiptDate,
            'LoanDetailId' => $loanDetailId,
            'DNumber' => $dNumber,
            'TransactionType' => $txnType,
            'PaymentMethod' => $paymentMethod,
            'PrincipalPaid' => $principalPaid,
            'InterestPaid' => $interestPaid,
            'DefaultInterestPaid' => $defaultInterestPaid,
            'AppraiserCharge' => $appraiserCharge,
            'ProcessingFee' => $processingFee,
            'TotalPaid' => $totalPaid,
            'BalanceLoanAmount' => $newBalance,
            'InterestMonths' => $interestMonths,
            'InterestPeriodFrom' => $periodFrom,
            'InterestPeriodTo' => $periodTo,
            'ChequeNumber' => $chequeNumber,
            'ChequeFromBank' => $chequeFromBank,
            'ChequeToBank' => $chequeToBank,
            'RtgsNumber' => $rtgsNumber,
            'RtgsFromBank' => $rtgsFromBank,
            'RtgsToBank' => $rtgsToBank,
            'UpiType' => $upiType,
            'UpiTransactionNumber' => $upiTxn,
            'UpiToBank' => $upiToBank,
            'Remarks' => $remarks,
            'CreatedBy' => $_SESSION['UserId'] ?? 1,
            'RateApplied' => $rateApplied,
            'OverdueInterestRate' => $overdueRate,
            'OverdueInterestPaid' => $overduePaid,
            'OverduePeriodFrom' => $overdueFrom,
            'OverduePeriodTo' => $overdueTo,
            'BreakdownJson' => $breakdownJson
        ];

        $saveRes = $this->model->saveReceipt($receiptData);

        if ($saveRes['status']) {
            $receiptNo = htmlspecialchars($saveRes['receiptNo']);
            $receiptId = $saveRes['receiptId'];
            $pdfUrl = "aster-jewel-loan-receipt-pdf.php?receipt_id=" . $receiptId;
            $viewUrl = "aster-jewel-loan-receipt.php?receipt_id=" . $receiptId . "&view_only=1";

            if ($actionType === 'generate') {
                $data['success'] = "Receipt <strong>{$receiptNo}</strong> generated successfully! "
                    . "<a href='{$pdfUrl}' target='_blank' class='btn btn-danger btn-xs' style='margin-left: 10px;'><i class='fa fa-file-pdf-o'></i> Download PDF</a> "
                    . "<a href='{$viewUrl}' class='btn btn-success btn-xs' style='margin-left: 5px;'><i class='fa fa-print'></i> Preview / Print Receipt</a>";
                $data['autoOpenPdf'] = $pdfUrl;
            } else {
                $data['success'] = "Receipt record <strong>{$receiptNo}</strong> saved successfully! "
                    . "<a href='{$pdfUrl}' target='_blank' class='btn btn-info btn-xs' style='margin-left: 10px;'><i class='fa fa-file-pdf-o'></i> View PDF</a> "
                    . "<a href='{$viewUrl}' class='btn btn-default btn-xs' style='margin-left: 5px;'><i class='fa fa-eye'></i> Preview</a>";
            }
            $data['savedReceiptId'] = $receiptId;
            $data['savedReceiptNo'] = $saveRes['receiptNo'];

            $data['processStatus'] = [
                'type' => 'receipt',
                'title' => 'Receipt Saved Successfully!',
                'message' => 'The payment receipt and loan balance have been successfully committed to the database.',
                'receiptNo' => $saveRes['receiptNo'],
                'dNumber' => $dNumber,
                'customerName' => $selectedLoan['customer']['Name'] ?? '',
                'totalPaid' => $totalPaid,
                'receiptId' => $receiptId,
                'viewUrl' => $viewUrl,
                'pdfUrl' => $pdfUrl,
                'receiptUrl' => 'aster-jewel-loan-receipt.php?dnum=' . urlencode($dNumber),
                'timestamp' => date('d/m/Y h:i A')
            ];

            // Reload loan summary with updated balance
            $data['selectedLoan'] = $this->model->getLoanReceiptSummary($loanDetailId, $dNumber);
        } else {
            $data['errors'][] = "Failed to process receipt: " . $saveRes['error'];
        }
    }
}
?>
