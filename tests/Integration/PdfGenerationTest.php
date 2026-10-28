<?php
require_once __DIR__ . '/../TestCase.php';

class PdfGenerationTest extends TestCase {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function run() {
        $this->setTestName("TCPDF Library Loadability");
        $tcpdfPath = __DIR__ . '/../../application/views/coe-report/tcpdf.php';
        $this->assertTrue(file_exists($tcpdfPath), "TCPDF library file exists at application/views/coe-report/tcpdf.php");
        require_once $tcpdfPath;
        $this->assertTrue(class_exists('TCPDF'), "TCPDF class is successfully loaded and defined");

        $this->setTestName("Receipt PDF Generator Rendering Integrity");
        // Test rendering a PDF instance using TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('ASTER Nidhi Test');
        $pdf->SetAuthor('ASTER Software');
        $pdf->SetTitle('Automated Test PDF');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTML('<h2>ASTER Jewel Loan Test PDF</h2><p>Testing PDF engine output stream</p>', true, false, true, false, '');

        $pdfOutput = $pdf->Output('test.pdf', 'S');
        $this->assertNotNull($pdfOutput, "PDF stream output generated");
        $this->assertTrue(strlen($pdfOutput) > 500, "PDF stream length is valid (" . strlen($pdfOutput) . " bytes)");
        $this->assertTrue(substr($pdfOutput, 0, 4) === '%PDF', "Output begins with standard %PDF signature");

        $this->setTestName("PDF Scripts Clean Buffer & Variable Integrity");
        // Verify aster-jewel-loan-pdf.php has buffer cleanup
        $contentLoanPdf = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-pdf.php');
        $this->assertTrue(strpos($contentLoanPdf, 'ob_end_clean') !== false, "aster-jewel-loan-pdf.php contains buffer cleanup guard");

        // Verify aster-jewel-loan-receipt-pdf.php has buffer cleanup
        $contentReceiptPdf = file_get_contents(__DIR__ . '/../../application/views/aster-jewel-loan-receipt-pdf.php');
        $this->assertTrue(strpos($contentReceiptPdf, 'ob_end_clean') !== false, "aster-jewel-loan-receipt-pdf.php contains buffer cleanup guard");
    }
}
