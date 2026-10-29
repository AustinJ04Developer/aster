<?php
/**
 * Jewel Loan Receipt List Controller
 * Manages receipt reports, search criteria, and aggregate metrics
 */
require_once __DIR__ . '/../../settings/db.php';
require_once __DIR__ . '/model/JewelLoanReceiptListModel.php';

class JewelLoanReceiptListController {
    private $conn;
    private $model;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanReceiptListModel($this->conn);
        if ($this->conn) {
            $this->model->ensurePageRegistered();
        }
    }

    public function handleRequest() {
        $filters = [
            'query' => trim($_GET['query'] ?? ($_POST['query'] ?? '')),
            'receipt_no' => trim($_GET['receipt_no'] ?? ($_POST['receipt_no'] ?? '')),
            'd_number' => trim($_GET['d_number'] ?? ($_POST['d_number'] ?? '')),
            'transaction_type' => trim($_GET['transaction_type'] ?? ($_POST['transaction_type'] ?? '')),
            'payment_method' => trim($_GET['payment_method'] ?? ($_POST['payment_method'] ?? '')),
            'date_from' => trim($_GET['date_from'] ?? ($_POST['date_from'] ?? '')),
            'date_to' => trim($_GET['date_to'] ?? ($_POST['date_to'] ?? ''))
        ];

        $receipts = $this->model->getReceipts($filters);
        $summary = $this->model->getSummaryStats($filters);

        return [
            'filters' => $filters,
            'receipts' => $receipts,
            'summary' => $summary,
            'todayDate' => date('Y-m-d')
        ];
    }
}

