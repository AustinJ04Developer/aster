<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/settings/db.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanModel.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanAccountListController.php';

class JewelLoanAccountListTest extends TestCase {
    private $model;
    private $controller;

    public function __construct() {
        global $conn;
        $this->model = new JewelLoanModel($conn);
        $this->controller = new JewelLoanAccountListController($conn);
    }

    public function run() {
        $this->setTestName("Verify Page & Privileges in Database");
        global $conn;
        $pRes = $conn->query("SELECT PageId, PageName, Link, PSMID FROM page WHERE Link = 'aster-jewel-loan-account-list.php'");
        $this->assertTrue($pRes && $pRes->num_rows > 0, "Account List page registered in database `page` table");
        if ($pRes && $row = $pRes->fetch_assoc()) {
            $this->assertEquals('Account List', $row['PageName'], "Page title is 'Account List'");
            $this->assertEquals(50, (int)$row['PSMID'], "Page linked to PSMID 50 (Jewel Loan)");
            $pageId = (int)$row['PageId'];

            $privRes = $conn->query("SELECT COUNT(*) AS total_roles FROM user_privilages WHERE PageId = {$pageId}");
            $pCount = $privRes ? (int)$privRes->fetch_assoc()['total_roles'] : 0;
            $this->assertGreaterThan(0, $pCount, "User privileges granted for Account List page (roles: {$pCount})");
        }

        $this->setTestName("Filter Accounts Created Till Date");
        $todayDate = date('Y-m-d');
        $accounts = $this->model->getJewelLoansCreatedTillDate($todayDate);
        $this->assertTrue(is_array($accounts), "Returns array of loan accounts");
        $this->assertGreaterThan(0, count($accounts), "Found existing loans created till today");

        // Verify required fields on first account
        if (!empty($accounts)) {
            $first = $accounts[0];
            $this->assertNotNull($first['LoanDateFormatted'], "Contains Date (the date the loan is created)");
            $this->assertNotNull($first['DNumber'], "Contains D Number");
            $this->assertNotNull($first['CustomerName'], "Contains Customer Name");
            $this->assertNotNull($first['InterestRate'], "Contains Default % (18%, 24%, 30%)");
            $this->assertNotNull($first['LoanAmount'], "Contains Loan Amount");
            $this->assertNotNull($first['BalanceLoanAmount'], "Contains Balance Amount");
            $this->assertNotNull($first['DueDateFormatted'], "Contains Due Date");
            $this->assertTrue(in_array($first['Status'], ['Active', 'Inactive']), "Contains Status (Active / Inactive)");
        }

        $this->setTestName("Transaction History Timeline Query");
        $loanDetailId = $accounts[0]['LoanDetailId'];
        $txns = $this->model->getTransactionHistoryByLoanDetailId($loanDetailId);
        $this->assertTrue(is_array($txns), "Returns array of transaction receipts");

        $this->setTestName("Controller Handling of Show All Filter");
        $_GET['date'] = date('d/m/Y');
        $_GET['show_all'] = '1';
        $reqData = $this->controller->handleRequest();
        $this->assertEquals($_GET['date'], $reqData['selectedDate'], "Controller parsed selected date input");
        $this->assertTrue($reqData['isSearchTriggered'], "Search triggered flag set to true");
        $this->assertGreaterThan(0, count($reqData['loans']), "Controller returns filtered loans for view");
    }
}
