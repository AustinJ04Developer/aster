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
        $principalPaid  = (float)($_POST['principal_paid'] ?? 0);
        $interestPaid   = (float)($_POST['interest_paid'] ?? 0);
        $defaultInterestPaid = (float)($_POST['default_interest'] ?? 0);
        $appraiserCharge= (float)($_POST['appraiser_charge'] ?? 0);
        $processingFee  = (float)($_POST['processing_fee'] ?? 0);
        $totalPaid      = (float)($_POST['total_paid'] ?? 0);

        $interestMonths = (float)($_POST['interest_months'] ?? 0);
        $periodFrom     = !empty($_POST['period_from']) ? date('Y-m-d', strtotime($_POST['period_from'])) : null;
        $periodTo       = !empty($_POST['period_to']) ? date('Y-m-d', strtotime($_POST['period_to'])) : null;
        $remarks        = trim($_POST['remarks'] ?? '');

        // Validation
        if ($loanDetailId <= 0 || empty($dNumber)) {
            $data['errors'][] = "Invalid loan selected. Loan ID and D-Number are required.";
            return;
        }

        if (!in_array($txnType, ['LOAN_CREATION', 'INTEREST_PAYMENT', 'JEWEL_RELEASE'])) {
            $data['errors'][] = "Invalid transaction type selected.";
            return;
        }

        if ($txnType === 'JEWEL_RELEASE') {
            // For jewel release, full principal balance must be paid
            $principalPaid = $currentBalance;
            $newBalance = 0.00;
        } else {
            // Partial or interest-only payment
            $newBalance = max(0.00, $currentBalance - $principalPaid);
        }

        $calcTotal = $principalPaid + $interestPaid + $defaultInterestPaid + $appraiserCharge + $processingFee;
        if ($totalPaid <= 0 && $calcTotal > 0) {
            $totalPaid = $calcTotal;
        }

        if ($totalPaid <= 0) {
            $data['errors'][] = "Total paid amount must be greater than zero.";
            return;
        }

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

        $actionType = trim($_POST['action_type'] ?? 'generate'); // 'save' or 'generate'

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
            'CreatedBy' => $_SESSION['UserId'] ?? 1
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
