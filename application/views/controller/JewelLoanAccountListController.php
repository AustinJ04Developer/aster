<?php
/**
 * Jewel Loan Account List Controller
 * Handles filtering jewel loans created till a given date and serving transaction history
 */
require_once __DIR__ . '/../../settings/db.php';
require_once 'model/JewelLoanModel.php';

class JewelLoanAccountListController {
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
        // AJAX endpoint: get transaction history modal details for a loan
        if (isset($_GET['ajax_txn_history'])) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $param = trim($_GET['ajax_txn_history']);
            $loanRecord = null;
            $loanDetailId = 0;

            if (is_numeric($param)) {
                $loanDetailId = (int)$param;
                $loanRecord = $this->model->getLoanRecordForPdf($loanDetailId);
            } else {
                // Look up by DNumber
                $loan = $this->model->getLoanDetailsByDNumber($param);
                if ($loan && !empty($loan['LoanDetailId'])) {
                    $loanDetailId = (int)$loan['LoanDetailId'];
                    $loanRecord = $this->model->getLoanRecordForPdf($loanDetailId);
                }
            }
            
            if (!$loanRecord || empty($loanRecord['loan'])) {
                echo json_encode(['status' => 'error', 'message' => 'Loan record not found.']);
                exit;
            }

            $loan = $loanRecord['loan'];
            $customer = $loanRecord['customer'];
            $txns = $this->model->getTransactionHistoryByLoanDetailId($loanDetailId);

            // Compute monthly due date & status
            $loanDateObj = new DateTime($loan['LoanDate']);
            $loanDateFormatted = $loanDateObj->format('d/m/Y');
            $nextDueDateStr = $this->model->calculateNextMonthlyDueDate($loan['LoanDate'], $loanDetailId);
            $dueDateFormatted = !empty($nextDueDateStr) ? date('d/m/Y', strtotime($nextDueDateStr)) : '-';

            $bal = (float)$loan['BalanceLoanAmount'];
            $hasRelease = false;
            foreach ($txns as $t) {
                if ($t['TransactionType'] === 'JEWEL_RELEASE') {
                    $hasRelease = true;
                    break;
                }
            }
            $status = ($hasRelease || $bal <= 0.00) ? 'Inactive' : 'Active';

            echo json_encode([
                'status' => 'success',
                'loan' => [
                    'LoanDetailId' => $loan['LoanDetailId'],
                    'DNumber' => $loan['DNumber'],
                    'CustomerName' => !empty($customer['Name']) ? $customer['Name'] : 'Customer',
                    'LoanDate' => $loan['LoanDate'],
                    'LoanDateFormatted' => $loanDateFormatted,
                    'InterestRate' => number_format((float)$loan['InterestRate'], 2) . '%',
                    'LoanAmount' => number_format((float)$loan['LoanAmount'], 2),
                    'BalanceLoanAmount' => number_format((float)$loan['BalanceLoanAmount'], 2),
                    'DueDateFormatted' => $dueDateFormatted,
                    'Status' => $status,
                    'MobileNumber' => !empty($customer['MobileNumber']) ? $customer['MobileNumber'] : '-',
                    'TotalNetWeight' => !empty($loan['TotalNetWeight']) ? number_format((float)$loan['TotalNetWeight'], 3) . ' g' : '-'
                ],
                'transactions' => $txns
            ]);
            exit;
        }

        // Standard Page Request Handling
        $selectedDateInput = trim($_GET['date'] ?? ($_POST['date'] ?? date('d/m/Y')));
        $isSearchTriggered = isset($_GET['show_all']) || isset($_POST['show_all']) || isset($_GET['date']);

        $dbDate = date('Y-m-d');
        if (!empty($selectedDateInput)) {
            if (strpos($selectedDateInput, '/') !== false) {
                $parts = explode('/', $selectedDateInput);
                if (count($parts) === 3) {
                    $dbDate = sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
                }
            } elseif (strpos($selectedDateInput, '-') !== false) {
                $parts = explode('-', $selectedDateInput);
                if (count($parts) === 3) {
                    if (strlen($parts[0]) === 4) {
                        $dbDate = $selectedDateInput;
                    } else {
                        $dbDate = sprintf('%04d-%02d-%02d', (int)$parts[2], (int)$parts[1], (int)$parts[0]);
                    }
                }
            }
        }

        $loans = [];
        if ($isSearchTriggered) {
            $loans = $this->model->getJewelLoansCreatedTillDate($dbDate);
        }

        return [
            'selectedDate'      => $selectedDateInput,
            'dbDate'            => $dbDate,
            'isSearchTriggered' => $isSearchTriggered,
            'loans'             => $loans
        ];
    }
}
?>
