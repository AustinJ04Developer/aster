<?php
/**
 * Jewel Loan Delete Account Controller
 * Handles searching account for deletion and executing permanent deletion
 */
require_once __DIR__ . '/../../settings/db.php';
require_once 'model/JewelLoanModel.php';

class JewelLoanDeleteAccountController {
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
        // Handle Delete Action via GET or POST
        if (isset($_GET['delete_account']) && !empty($_GET['dnum'])) {
            $dNumToDelete = trim($_GET['dnum']);
            $res = $this->model->deleteJewelLoanAccount($dNumToDelete);
            if ($res['success']) {
                $_SESSION['success'] = $res['message'];
            } else {
                $_SESSION['error'] = $res['message'];
            }
            header("Location: aster-jewel-loan-delete-account.php");
            exit;
        }

        if (isset($_POST['action_delete_account']) && !empty($_POST['dnum'])) {
            $dNumToDelete = trim($_POST['dnum']);
            $res = $this->model->deleteJewelLoanAccount($dNumToDelete);
            if ($res['success']) {
                $_SESSION['success'] = $res['message'];
            } else {
                $_SESSION['error'] = $res['message'];
            }
            header("Location: aster-jewel-loan-delete-account.php");
            exit;
        }

        // Fetch all D Numbers for the search dropdown
        $allAccounts = $this->model->getAllJewelLoanDNumbers();

        // Check if search was performed
        $searchedAccount = null;
        $selectedDNumber = '';
        if (isset($_POST['search-submit-jl-delete-account']) || isset($_GET['search'])) {
            $selectedDNumber = trim($_POST['dnumber-search-field'] ?? ($_GET['dnum'] ?? ''));
            if (!empty($selectedDNumber)) {
                $searchedAccount = $this->model->getAccountForDeletion($selectedDNumber);
                if (!$searchedAccount) {
                    $_SESSION['error'] = "No jewel loan account found for D Number: " . htmlspecialchars($selectedDNumber);
                }
            } else {
                $_SESSION['error'] = "Please select a D Number to search.";
            }
        }

        return [
            'allAccounts'     => $allAccounts,
            'searchedAccount' => $searchedAccount,
            'selectedDNumber' => $selectedDNumber
        ];
    }
}
?>
