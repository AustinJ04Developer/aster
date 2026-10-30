<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../application/views/controller/model/JewelLoanModel.php';
require_once __DIR__ . '/../../application/views/controller/JewelLoanReceiptController.php';

class JewelLoanReceiptPopupAndDropdownTest extends TestCase {
    private $model;
    private $controller;

    public function __construct() {
        global $conn;
        $this->model = new JewelLoanModel($conn);
        $this->controller = new JewelLoanReceiptController($conn);
    }

    public function run() {
        $this->setTestName("Verify JewelLoanModel & Controller D-Number Methods");
        $this->assertNotNull($this->model, "JewelLoanModel instantiated successfully");
        $this->assertNotNull($this->controller, "JewelLoanReceiptController instantiated successfully");
        $this->assertTrue(method_exists($this->model, 'getAllActiveLoanDNumbers'), "getAllActiveLoanDNumbers exists on JewelLoanModel");

        $this->setTestName("Verify Receipt List HTML Modal Markup");
        $listHtml = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-receipt-list.php');
        $this->assertContains('id="receiptViewModal"', $listHtml, "Receipt List contains receiptViewModal Bootstrap popup markup");
        $this->assertContains('btn-view-receipt-popup', $listHtml, "Receipt List has btn-view-receipt-popup action buttons");
        $this->assertContains('id="receipt-modal-body-content"', $listHtml, "Receipt List has receipt-modal-body-content for displaying the voucher");
        $this->assertContains('ajax_receipt_voucher', $listHtml, "Receipt List triggers ajax_receipt_voucher to fetch voucher");
        $this->assertFalse(strpos($listHtml, 'id="btn-print-modal-receipt"') !== false, "Print voucher button removed from popup modal as requested");
        $this->assertFalse(strpos($listHtml, 'id="btn-pdf-modal-receipt"') !== false, "Download PDF button removed from popup modal as requested");
        $this->assertFalse(strpos($listHtml, 'btn-view-receipt-popup" target="_blank"') !== false, "View receipt popup button does not open in a separate tab page");

        $this->setTestName("Verify Receipt Search & Dropdown Markup (No Cache / Database Powered)");
        $receiptHtml = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-receipt.php');
        $this->assertFalse(strpos($receiptHtml, '<datalist id="active_dnumbers_list">') !== false, "Datalist is removed to prevent browser autofill cache interference");
        $this->assertContains('id="dnumber-dropdown-menu"', $receiptHtml, "Custom dropdown menu id present");
        $this->assertContains('id="btn-toggle-dnumber-dropdown"', $receiptHtml, "Dropdown toggle caret button present");
        $this->assertContains('dnumber-dropdown-item', $receiptHtml, "Dropdown items populated from database present");
        $this->assertContains('autocomplete="off"', $receiptHtml, "Autocomplete disabled to avoid browser cache");

        $this->setTestName("Verify Popup Standalone View Handler");
        $this->assertContains('$isPopup', $receiptHtml, "Popup mode handler exists in aster-jewel-loan-receipt.php");
        $this->assertContains('exit;', $receiptHtml, "Popup view exits without rendering outer navbar/footer");

        $this->setTestName("Verify PRINCIPAL_PAYMENT and LOAN_CREATION TotalPaid Domain Rules");
        global $conn;
        // Check schema allows PRINCIPAL_PAYMENT
        $colRes = mysqli_query($conn, "SHOW COLUMNS FROM ast_jewel_loan_receipt LIKE 'TransactionType'");
        $colRow = mysqli_fetch_assoc($colRes);
        $this->assertContains('PRINCIPAL_PAYMENT', $colRow['Type'], "ast_jewel_loan_receipt.TransactionType enum includes PRINCIPAL_PAYMENT");

        // Verify LOAN_CREATION receipts have PrincipalPaid = 0.00 and TotalPaid = AppraiserCharge
        $creationRes = mysqli_query($conn, "SELECT ReceiptNo, PrincipalPaid, AppraiserCharge, TotalPaid FROM ast_jewel_loan_receipt WHERE TransactionType = 'LOAN_CREATION' LIMIT 5");
        while ($cRow = mysqli_fetch_assoc($creationRes)) {
            $this->assertEquals(0.00, (float)$cRow['PrincipalPaid'], "LOAN_CREATION receipt {$cRow['ReceiptNo']} has PrincipalPaid = 0.00");
            $this->assertEquals((float)$cRow['AppraiserCharge'], (float)$cRow['TotalPaid'], "LOAN_CREATION receipt {$cRow['ReceiptNo']} TotalPaid equals AppraiserCharge");
        }

        $this->setTestName("Verify Removal of Loan ID Displays (Only D Number Used Everywhere)");
        $loanPdfCode = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-pdf.php');
        $receiptPdfCode = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-receipt-pdf.php');
        $this->assertFalse(strpos($loanPdfCode, 'Loan ID:') !== false, "Loan ID label removed from jewel loan sanction PDF");
        $this->assertFalse(strpos($receiptPdfCode, 'Loan ID:') !== false, "Loan ID label removed from jewel loan receipt PDF");
        $this->assertContains('Customer D-No:', $loanPdfCode, "Sanction PDF uses Customer D-No as identifier");
        $this->assertContains('Customer D-Number:', $receiptPdfCode, "Receipt PDF uses Customer D-Number as identifier");
    }
}
