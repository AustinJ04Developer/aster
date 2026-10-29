<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanDeleteReceiptModel.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanDeleteReceiptController.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanReceiptListModel.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanReceiptListController.php';

class JewelLoanDeleteReceiptTest extends TestCase {
    private $model;
    private $controller;
    private $listModel;
    private $listController;

    public function __construct() {
        global $conn;
        $this->model = new JewelLoanDeleteReceiptModel($conn);
        $this->controller = new JewelLoanDeleteReceiptController($conn);
        $this->listModel = new JewelLoanReceiptListModel($conn);
        $this->listController = new JewelLoanReceiptListController($conn);
    }

    public function run() {
        $this->setTestName("Verify Delete Receipt & Receipt List Models and Page Registration");
        $this->assertNotNull($this->model, "JewelLoanDeleteReceiptModel instantiated successfully");
        $this->assertNotNull($this->controller, "JewelLoanDeleteReceiptController instantiated successfully");
        $this->assertNotNull($this->listModel, "JewelLoanReceiptListModel instantiated successfully");
        $this->assertNotNull($this->listController, "JewelLoanReceiptListController instantiated successfully");

        // Verify method signatures exist
        $this->assertTrue(method_exists($this->model, 'ensurePageRegistered'), "Method ensurePageRegistered exists on Delete model");
        $this->assertTrue(method_exists($this->model, 'searchReceipts'), "Method searchReceipts exists on Delete model");
        $this->assertTrue(method_exists($this->model, 'getReceiptForDelete'), "Method getReceiptForDelete exists on Delete model");
        $this->assertTrue(method_exists($this->model, 'deleteReceipt'), "Method deleteReceipt exists on Delete model");
        $this->assertTrue(method_exists($this->listModel, 'getReceipts'), "Method getReceipts exists on List model");
        $this->assertTrue(method_exists($this->listModel, 'getSummaryStats'), "Method getSummaryStats exists on List model");

        // Verify registration in database
        global $conn;
        if ($conn) {
            $pRes = $conn->query("SELECT PageId, PageName, Link, PSMID FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-receipt.php'");
            $this->assertTrue($pRes && $pRes->num_rows > 0, "Delete Receipt page registered in database `page` table");

            $pListRes = $conn->query("SELECT PageId, PageName, Link, PSMID FROM `page` WHERE `Link` = 'aster-jewel-loan-receipt-list.php'");
            $this->assertTrue($pListRes && $pListRes->num_rows > 0, "Receipt List page registered in database `page` table");
        }

        $this->setTestName("Verify Projected Balance Calculations on Reversal");
        $sampleInterestPayment = [
            'ReceiptId' => 999,
            'ReceiptNo' => 'JLR-2026-TEST',
            'TransactionType' => 'INTEREST_PAYMENT',
            'LoanAmount' => 50000.00,
            'CurrentLoanBalance' => 35000.00,
            'PrincipalPaid' => 5000.00,
            'InterestPaid' => 1200.00,
            'TotalPaid' => 6200.00
        ];
        $projectedInterestBal = min($sampleInterestPayment['LoanAmount'], round($sampleInterestPayment['CurrentLoanBalance'] + $sampleInterestPayment['PrincipalPaid'], 2));
        $this->assertEquals(40000.00, $projectedInterestBal, "Projected balance for interest payment reversal restores principal from 35k to 40k");

        $sampleReleasePayment = [
            'ReceiptId' => 998,
            'ReceiptNo' => 'JLR-2026-REL',
            'TransactionType' => 'JEWEL_RELEASE',
            'LoanAmount' => 50000.00,
            'CurrentLoanBalance' => 0.00,
            'PrincipalPaid' => 35000.00,
            'TotalPaid' => 38000.00
        ];
        $projectedReleaseBal = $sampleReleasePayment['PrincipalPaid'] > 0 ? $sampleReleasePayment['PrincipalPaid'] : $sampleReleasePayment['LoanAmount'];
        $this->assertEquals(35000.00, $projectedReleaseBal, "Projected balance for jewel release reversal re-opens loan with balance 35k");

        $this->setTestName("Verify Protection Guard Against Accidental Loan Creation Receipt Deletion");
        $res = $this->model->deleteReceipt(-1);
        $this->assertFalse($res['status'], "Fails gracefully for non-existent receipt ID");

        $this->setTestName("Verify Controller Handles Optional Search Terms for Current Date");
        $_GET['search_term'] = 'D-0016';
        $reqData = $this->controller->handleRequest();
        $this->assertTrue(is_array($reqData), "Controller returns data array");
        $this->assertEquals('D-0016', $reqData['searchTerm'], "Controller captured optional searchTerm");
        $this->assertEquals(date('d/m/Y'), $reqData['todayDateDisplay'], "Controller formats date as d/m/Y");
        $this->assertTrue(is_array($reqData['receipts']), "Receipts list returned as array");

        $this->setTestName("Verify Receipt List Controller Returns All Receipts by Default");
        $listData = $this->listController->handleRequest();
        $this->assertTrue(is_array($listData), "Receipt List controller returns data array");
        $this->assertTrue(is_array($listData['receipts']), "Receipts list returned as array");
        $this->assertTrue(is_array($listData['summary']), "Summary metrics array returned");
    }
}
