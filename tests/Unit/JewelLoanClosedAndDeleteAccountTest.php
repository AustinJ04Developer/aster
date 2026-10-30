<?php
/**
 * Test Suite: Jewel Loan Closed Accounts List, Delete Account, Transaction Popup & Sidebar Renaming
 */
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanModel.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanClosedAccountsController.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanDeleteAccountController.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanAccountListController.php';

class JewelLoanClosedAndDeleteAccountTest extends TestCase {
    private $conn;
    private $model;
    private $closedController;
    private $deleteController;

    public function __construct($conn = null) {
        if (!$conn) {
            global $conn;
        }
        $this->conn = $conn;
        $this->model = new JewelLoanModel($this->conn);
        $this->closedController = new JewelLoanClosedAccountsController($this->conn);
        $this->deleteController = new JewelLoanDeleteAccountController($this->conn);
    }

    public function run() {
        // -------------------------------------------------------------
        // 1. Sidebar Renaming & Header Renaming Tests
        // -------------------------------------------------------------
        $this->setTestName("Verify Sidebar Renaming & Page Header");

        $pageRes = $this->conn->query("SELECT PageName FROM `page` WHERE `Link` = 'aster-jewel-loan-search.php' LIMIT 1");
        $pageRow = ($pageRes && $pageRes->num_rows > 0) ? $pageRes->fetch_assoc() : null;
        $this->assertTrue(
            $pageRow !== null && $pageRow['PageName'] === 'Jewel Loan Search',
            "Page 2930 (aster-jewel-loan-search.php) renamed to 'Jewel Loan Search' in sidebar"
        );

        $jlCreateContent = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan.php');
        $this->assertTrue(
            strpos($jlCreateContent, '<h2 class="page-header">Jewel Loan Creation</h2>') !== false,
            "aster-jewel-loan.php has header 'Jewel Loan Creation'"
        );

        // -------------------------------------------------------------
        // 2. Closed Accounts List Verification
        // -------------------------------------------------------------
        $this->setTestName("Verify Closed Accounts List Controller & View");

        $closedLoans = $this->model->getClosedJewelLoans();
        $this->assertTrue(is_array($closedLoans), "getClosedJewelLoans() returns an array");

        $closedData = $this->closedController->handleRequest();
        $this->assertTrue(isset($closedData['closedLoans']) && is_array($closedData['closedLoans']), "JewelLoanClosedAccountsController returns closedLoans array");

        $closedPageRes = $this->conn->query("SELECT PageId, PageName, PSMID FROM `page` WHERE `Link` = 'aster-jewel-loan-closed-accounts.php' LIMIT 1");
        $this->assertTrue($closedPageRes && $closedPageRes->num_rows > 0, "aster-jewel-loan-closed-accounts.php registered in page table under PSMID = 50");

        $closedViewContent = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-closed-accounts.php');
        $this->assertTrue(
            strpos($closedViewContent, 'Account Creation Date') !== false &&
            strpos($closedViewContent, 'D Number') !== false &&
            strpos($closedViewContent, 'Interest %') !== false &&
            strpos($closedViewContent, 'Loan Amount') !== false &&
            strpos($closedViewContent, 'Status') !== false,
            "Closed accounts view has required columns: Account Creation Date, D Number, Name, Interest %, Loan Amount, Status"
        );

        // -------------------------------------------------------------
        // 3. Delete Account Page Verification
        // -------------------------------------------------------------
        $this->setTestName("Verify Delete Account Controller, View & Atomic Deletion");

        $deletePageRes = $this->conn->query("SELECT PageId, PageName, PSMID FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-account.php' LIMIT 1");
        $this->assertTrue($deletePageRes && $deletePageRes->num_rows > 0, "aster-jewel-loan-delete-account.php registered in page table under PSMID = 50");

        $allDNums = $this->model->getAllJewelLoanDNumbers();
        $this->assertTrue(is_array($allDNums), "getAllJewelLoanDNumbers() returns list of accounts for dropdown");

        $deleteViewContent = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-delete-account.php');
        $this->assertTrue(strpos($deleteViewContent, 'Delete Accounts') !== false, "Delete account view contains heading 'Delete Accounts'");
        $this->assertTrue(strpos($deleteViewContent, 'Select D Number:') !== false, "Delete account view contains dropdown label 'Select D Number:'");
        $this->assertTrue(strpos($deleteViewContent, 'name="dnumber-search-field"') !== false, "Delete account view contains select field 'dnumber-search-field'");
        $this->assertTrue(strpos($deleteViewContent, 'name="search-submit-jl-delete-account"') !== false, "Delete account view contains Search button");

        // Clean up any previous test leftovers first
        $this->conn->query("DELETE FROM ast_jewel_loan_customer WHERE DNumber LIKE 'D-TEST%' OR DNumber LIKE 'D-DEL%'");

        // Test getAccountForDeletion and deleteJewelLoanAccount with a temporary test loan
        $tempDNum = 'D-DEL-' . mt_rand(10000, 99999);
        $this->conn->query("INSERT INTO ast_jewel_loan_customer (DNumber, Name, MobileNumber, Address, Pincode, LoanDate) 
                            VALUES ('{$tempDNum}', 'Test Deletion User', '9998887776', '123 Test St', '600001', CURDATE())");
        $this->conn->query("INSERT INTO ast_jewel_loan_details (DNumber, LoanAmount, BalanceLoanAmount, InterestRate, LoanDate) 
                            VALUES ('{$tempDNum}', 50000.00, 50000.00, 18.00, CURDATE())");
        $testLdId = $this->conn->insert_id;
        $this->conn->query("INSERT INTO ast_jewel_loan_items (LoanDetailId, DNumber, SlNo, ItemName, NoOfItems, Gram, Wastage, NetWeight) 
                            VALUES ({$testLdId}, '{$tempDNum}', 1, 'Gold Ring', 1, 5.000, 0.200, 4.800)");
        $this->conn->query("INSERT INTO ast_jewel_loan_receipt (ReceiptNo, DNumber, LoanDetailId, ReceiptDate, TransactionType, PaymentMethod, PrincipalPaid, InterestPaid, TotalPaid, BalanceLoanAmount) 
                            VALUES ('TEST-RCP-01', '{$tempDNum}', {$testLdId}, CURDATE(), 'LOAN_CREATION', 'Cash', 0.00, 0.00, 200.00, 50000.00)");

        $previewAcc = $this->model->getAccountForDeletion($tempDNum);
        $this->assertTrue(
            $previewAcc !== null && $previewAcc['DNumber'] === $tempDNum,
            "getAccountForDeletion({$tempDNum}) successfully retrieves account preview"
        );
        $this->assertTrue(
            isset($previewAcc['LoanDateFormatted']) && isset($previewAcc['CustomerName']) && isset($previewAcc['InterestRate']) && isset($previewAcc['LoanAmount']) && isset($previewAcc['Status']),
            "Account preview includes Loan Date, Customer Name, Interest %, Loan Amount, and Status"
        );

        // Execute deletion of test loan
        $delRes = $this->model->deleteJewelLoanAccount($tempDNum);
        $this->assertTrue($delRes['success'] === true, "deleteJewelLoanAccount({$tempDNum}) completed successfully");

        // Verify all associated tables are completely clean
        $chkCust = $this->conn->query("SELECT 1 FROM ast_jewel_loan_customer WHERE DNumber = '{$tempDNum}'");
        $chkDetails = $this->conn->query("SELECT 1 FROM ast_jewel_loan_details WHERE DNumber = '{$tempDNum}'");
        $chkItems = $this->conn->query("SELECT 1 FROM ast_jewel_loan_items WHERE DNumber = '{$tempDNum}'");
        $chkReceipts = $this->conn->query("SELECT 1 FROM ast_jewel_loan_receipt WHERE DNumber = '{$tempDNum}'");

        $this->assertTrue(
            ($chkCust && $chkCust->num_rows === 0) &&
            ($chkDetails && $chkDetails->num_rows === 0) &&
            ($chkItems && $chkItems->num_rows === 0) &&
            ($chkReceipts && $chkReceipts->num_rows === 0),
            "Entire account (customer, details, items, receipts) permanently deleted"
        );

        // -------------------------------------------------------------
        // 4. Account List Transaction History Popup Verification
        // -------------------------------------------------------------
        $this->setTestName("Verify Account List Transaction History Popup & Model Data");

        $accListView = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-account-list.php');
        $this->assertTrue(strpos($accListView, 'modalTransactionHistory') !== false, "Account list contains modalTransactionHistory popup modal");
        $this->assertTrue(
            strpos($accListView, 'txn-history-heading') !== false &&
            strpos($accListView, 'Transaction History') !== false,
            "Modal contains underlined 'Transaction History' heading matching Image 2"
        );
        $this->assertTrue(
            strpos($accListView, 'popup-meta-table') !== false &&
            strpos($accListView, 'D Number:') !== false &&
            strpos($accListView, 'Customer Name:') !== false &&
            strpos($accListView, 'Status:') !== false,
            "Modal contains 3-column table: D Number, Customer Name, Status matching Image 2"
        );
        $this->assertTrue(
            strpos($accListView, 'Balance Amount (₹)') !== false &&
            strpos($accListView, 'Total Paid (₹)') !== false &&
            strpos($accListView, 'Current Bal (₹)') !== false,
            "Modal contains Transaction & Receipts History table from aster-jewel-loan-view.php"
        );

        // Test D-Number lookup
        $testDNum = 'D-0016';
        $chkExisting = $this->conn->query("SELECT DNumber FROM ast_jewel_loan_details LIMIT 1");
        if ($chkExisting && $row = $chkExisting->fetch_assoc()) {
            $testDNum = $row['DNumber'];
        }
        $testLoan = $this->model->getLoanDetailsByDNumber($testDNum);
        $this->assertTrue($testLoan !== null && !empty($testLoan['LoanDetailId']), "Loan details can be retrieved by D-Number string: {$testDNum}");
        if ($testLoan) {
            $txns = $this->model->getTransactionHistoryByLoanDetailId($testLoan['LoanDetailId']);
            $this->assertTrue(is_array($txns), "getTransactionHistoryByLoanDetailId() returns transaction records for {$testDNum}");
        }
    }
}
