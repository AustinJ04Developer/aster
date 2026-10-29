<?php
/**
 * Jewel Loan Delete Receipt Controller
 * Coordinates receipt lookup, deletion confirmation, and balance reversal workflows
 */
require_once __DIR__ . '/../../settings/db.php';
require_once __DIR__ . '/model/JewelLoanDeleteReceiptModel.php';

class JewelLoanDeleteReceiptController {
    private $conn;
    private $model;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanDeleteReceiptModel($this->conn);
        if ($this->conn) {
            $this->model->ensurePageRegistered();
        }
    }

    public function handleRequest() {
        $selectedReceiptId = (int)($_GET['inspect_id'] ?? ($_POST['inspect_id'] ?? 0));
        $searchTerm = trim($_GET['search_term'] ?? ($_POST['search_term'] ?? ''));

        $data = [
            'success' => $_SESSION['success'] ?? '',
            'error'   => $_SESSION['error'] ?? '',
            'selectedReceiptId' => $selectedReceiptId,
            'searchTerm' => $searchTerm,
            'selectedReceipt' => null,
            'receipts' => [],
            'todayDate' => date('Y-m-d'),
            'todayDateDisplay' => date('d/m/Y')
        ];

        // Clear one-time flash messages
        unset($_SESSION['success'], $_SESSION['error']);

        // 1. Handle Delete Action (POST or GET with explicit confirmation)
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['confirm_delete_receipt'])) {
            $delId = (int)($_POST['receipt_id'] ?? 0);
            $force = isset($_POST['force_delete_loan_creation']) && $_POST['force_delete_loan_creation'] === '1';
            
            $res = $this->model->deleteReceipt($delId, $force);
            if ($res['status']) {
                $_SESSION['success'] = $res['message'];
            } else {
                $_SESSION['error'] = $res['message'];
            }
            header("Location: aster-jewel-loan-delete-receipt.php");
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['receipt_id'])) {
            $delId = (int)$_GET['receipt_id'];
            $res = $this->model->deleteReceipt($delId);
            if ($res['status']) {
                $_SESSION['success'] = $res['message'];
            } else {
                $_SESSION['error'] = $res['message'];
            }
            header("Location: aster-jewel-loan-delete-receipt.php");
            exit;
        }

        // 2. Fetch specific receipt for preview / details
        if ($selectedReceiptId > 0) {
            $data['selectedReceipt'] = $this->model->getReceiptForDelete($selectedReceiptId);
        }

        // 3. Receipts list for the table (strictly restricted to today, filtered by search term if provided)
        $data['receipts'] = $this->model->searchReceipts(0, $searchTerm);

        return $data;
    }
}
