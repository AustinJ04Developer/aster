<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once '../settings/db.php';
require_once 'controller/model/JewelLoanModel.php';
require_once 'coe-report/tcpdf.php';

$loanDetailId  = (int)($_GET['id'] ?? 0);
$dNumber       = trim($_GET['dnum'] ?? ($_GET['d_number'] ?? ''));

if ($loanDetailId <= 0 && empty($dNumber)) {
    die("Error: Loan ID or D-Number is required.");
}

$model = new JewelLoanModel($conn);
$record = $model->getLoanRecordForPdf($loanDetailId, $dNumber);

if (!$record || empty($record['loan'])) {
    die("Error: Jewel Loan record not found.");
}

$cust = $record['customer'] ?? [];
$loan = $record['loan'] ?? [];
$items = $record['items'] ?? [];

// Values applicable to this saved loan (historical values preserved from DB record)
$dNum            = $loan['DNumber'] ?? $dNumber;
$loanDetailId    = (int)($loan['LoanDetailId'] ?? $loanDetailId);
$loanDate        = !empty($loan['LoanDate']) ? date('d-m-Y', strtotime($loan['LoanDate'])) : '';
$interestRate    = number_format((float)($loan['InterestRate'] ?? 0), 2);
$totalNetWeight  = number_format((float)($loan['TotalNetWeight'] ?? 0), 3);
$loanAmountVal   = (float)($loan['LoanAmount'] ?? 0);
$loanAmount      = number_format($loanAmountVal, 2);
$balanceLoanVal  = isset($loan['BalanceLoanAmount']) && (float)$loan['BalanceLoanAmount'] > 0 
    ? (float)$loan['BalanceLoanAmount'] 
    : $loanAmountVal;
$balanceLoanAmount = number_format($balanceLoanVal, 2);

// 1. Appraiser Charge
$appraiserChargeVal = (float)($loan['AppraiserCharge'] ?? 0);
$appraiserCharge    = number_format($appraiserChargeVal, 2);

// 2. Processing Fee Percentage (%)
$feePctVal = isset($loan['ProcessingFeePercentage']) && $loan['ProcessingFeePercentage'] !== null
    ? (float)$loan['ProcessingFeePercentage']
    : 0.00;
// If older historical record has 0 in column, fallback safely without tampering
$feePctFormatted = (fmod($feePctVal, 1.0) == 0.0)
    ? number_format($feePctVal, 0) . '%'
    : number_format($feePctVal, 2) . '%';

// 3. Processing Fee
$processingFeeVal = isset($loan['ProcessingFee']) && $loan['ProcessingFee'] !== null
    ? (float)$loan['ProcessingFee']
    : round(($loanAmountVal * $feePctVal) / 100, 2);
$processingFee = number_format($processingFeeVal, 2);

// 4. Payment Method & details
$paymentMethod = $loan['PaymentMethod'] ?? 'Cash';
$paymentDate   = !empty($loan['PaymentDate']) ? date('d-m-Y', strtotime($loan['PaymentDate'])) : $loanDate;

// Customer info
$custName     = $cust['Name'] ?? '';
$fathersName  = $cust['FathersName'] ?? '';
$mobile       = $cust['MobileNumber'] ?? '';
$address      = $cust['Address'] ?? '';
$pincode      = $cust['Pincode'] ?? '';
$aadhaar      = $cust['AadhaarNumber'] ?? '';
$nominee      = $cust['NomineeName'] ?? '';
$nomineeRel   = $cust['NomineeRelation'] ?? '';
$packetNumber = $cust['PacketNumber'] ?? '';
$loanPeriod   = $cust['LoanPeriod'] ?? '12 Months';

// Initialize TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Document metadata
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Aster Public Development Foundation');
$pdf->SetTitle('Jewel Loan Application - ' . $dNum);
$pdf->SetSubject('Jewel Loan Application');

// Remove default header/footer for clean custom letterhead
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(TRUE, 12);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9);

$pdf->AddPage();

// Build HTML content
$html = '
<style>
    .header-table { width: 100%; border-bottom: 2px solid #337ab7; margin-bottom: 8px; }
    .org-title { font-size: 15pt; font-weight: bold; color: #1a4d79; text-align: center; }
    .doc-subtitle { font-size: 11pt; font-weight: bold; color: #333333; text-align: center; margin-top: 2px; }
    .section-title { font-size: 10pt; font-weight: bold; color: #1a4d79; background-color: #f0f4f8; padding: 4px; border: 1px solid #d0dbe5; margin-top: 6px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table td { padding: 4px 6px; font-size: 9pt; }
    .label { font-weight: bold; color: #333333; width: 22%; }
    .value { color: #111111; width: 28%; }
    .grid-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .grid-table th { background-color: #f5f5f5; font-weight: bold; text-align: center; border: 1px solid #cccccc; padding: 4px; font-size: 8.5pt; }
    .grid-table td { border: 1px solid #cccccc; padding: 4px; font-size: 8.5pt; }
    .highlight-label { font-weight: bold; color: #0d3b66; width: 25%; }
    .highlight-value { font-weight: bold; color: #0d3b66; width: 25%; }
    .footer-note { font-size: 7.5pt; color: #666666; font-style: italic; }
    .sig-table { width: 100%; margin-top: 25px; }
    .sig-table td { text-align: center; font-size: 8.5pt; font-weight: bold; padding-top: 35px; }
</style>

<table class="header-table" cellpadding="2">
    <tr>
        <td class="org-title">ASTER PUBLIC DEVELOPMENT FOUNDATION</td>
    </tr>
    <tr>
        <td class="doc-subtitle">JEWEL LOAN APPLICATION / SANCTION ADVICE</td>
    </tr>
</table>

<table class="data-table" cellpadding="3">
    <tr>
        <td class="label">Customer D-No:</td>
        <td class="value"><strong>' . htmlspecialchars($dNum) . '</strong></td>
        <td class="label">Loan Date:</td>
        <td class="value">' . htmlspecialchars($loanDate) . '</td>
    </tr>
    <tr>
        <td class="label">Loan ID:</td>
        <td class="value"><strong>JL-' . str_pad((string)$loanDetailId, 4, '0', STR_PAD_LEFT) . '</strong></td>
        <td class="label">Packet / Locker No:</td>
        <td class="value">' . htmlspecialchars($packetNumber) . '</td>
    </tr>
</table>

<div class="section-title">CUSTOMER DETAILS</div>
<table class="data-table" cellpadding="3">
    <tr>
        <td class="label">Customer Name:</td>
        <td class="value">' . htmlspecialchars($custName) . '</td>
        <td class="label">Father / Spouse:</td>
        <td class="value">' . htmlspecialchars($fathersName) . '</td>
    </tr>
    <tr>
        <td class="label">Mobile Number:</td>
        <td class="value">' . htmlspecialchars($mobile) . '</td>
        <td class="label">Aadhaar Number:</td>
        <td class="value">' . htmlspecialchars($aadhaar) . '</td>
    </tr>
    <tr>
        <td class="label">Address:</td>
        <td class="value" colspan="3">' . htmlspecialchars($address . ($pincode ? ' - ' . $pincode : '')) . '</td>
    </tr>
    <tr>
        <td class="label">Nominee Name:</td>
        <td class="value">' . htmlspecialchars($nominee) . '</td>
        <td class="label">Nominee Relation:</td>
        <td class="value">' . htmlspecialchars($nomineeRel) . '</td>
    </tr>
</table>

<div class="section-title">PLEDGED GOLD ITEMS</div>
<table class="grid-table" cellpadding="3">
    <thead>
        <tr>
            <th style="width: 8%;">Sl No</th>
            <th style="width: 38%; text-align: left;">Item Description</th>
            <th style="width: 14%;">No. of Items</th>
            <th style="width: 14%;">Gross Wt (g)</th>
            <th style="width: 12%;">Wastage (g)</th>
            <th style="width: 14%;">Net Wt (g)</th>
        </tr>
    </thead>
    <tbody>';

$totItems = 0;
$totGross = 0.0;
$totWastage = 0.0;
$totNet = 0.0;

if (!empty($items)) {
    foreach ($items as $idx => $it) {
        $cnt = (int)($it['NoOfItems'] ?? 1);
        $gr = (float)($it['Gram'] ?? 0);
        $wst = (float)($it['Wastage'] ?? 0);
        $net = (float)($it['NetWeight'] ?? ($gr - $wst));

        $totItems += $cnt;
        $totGross += $gr;
        $totWastage += $wst;
        $totNet += $net;

        $html .= '
        <tr>
            <td align="center">' . ($idx + 1) . '</td>
            <td>' . htmlspecialchars($it['ItemName']) . '</td>
            <td align="center">' . $cnt . '</td>
            <td align="right">' . number_format($gr, 3) . '</td>
            <td align="right">' . number_format($wst, 3) . '</td>
            <td align="right">' . number_format($net, 3) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">No gold items recorded.</td></tr>';
}

$html .= '
        <tr style="background-color: #fafafa; font-weight: bold;">
            <td colspan="2" align="right">Total:</td>
            <td align="center">' . $totItems . '</td>
            <td align="right">' . number_format($totGross, 3) . '</td>
            <td align="right">' . number_format($totWastage, 3) . '</td>
            <td align="right">' . number_format($totNet, 3) . '</td>
        </tr>
    </tbody>
</table>

<div class="section-title">LOAN & FINANCIAL CHARGES DETAILS</div>
<table class="data-table" cellpadding="3">
    <tr>
        <td class="label">Total Net Weight:</td>
        <td class="value">' . htmlspecialchars($totalNetWeight) . ' g</td>
        <td class="label">Interest Rate:</td>
        <td class="value">' . htmlspecialchars($interestRate) . '% p.a.</td>
    </tr>
    <tr>
        <td class="label">Loan Period:</td>
        <td class="value">' . htmlspecialchars($loanPeriod) . '</td>
        <td class="label"><strong>Loan Amount:</strong></td>
        <td class="value"><strong>Rs. ' . htmlspecialchars($loanAmount) . '</strong></td>
    </tr>
    <tr>
        <td class="label">Payment Method:</td>
        <td class="value"><strong>' . htmlspecialchars($paymentMethod) . '</strong></td>
        <td class="label">Balance Loan Amount:</td>
        <td class="value"><strong>Rs. ' . htmlspecialchars($balanceLoanAmount) . '</strong></td>
    </tr>
    <tr style="background-color: #f7fafc;">
        <td class="highlight-label">1. Appraiser Charge:</td>
        <td class="highlight-value">Rs. ' . htmlspecialchars($appraiserCharge) . '</td>
        <td class="highlight-label">2. Processing Fee (%):</td>
        <td class="highlight-value">' . htmlspecialchars($feePctFormatted) . '</td>
    </tr>
    <tr style="background-color: #f7fafc;">
        <td class="highlight-label">3. Processing Fee:</td>
        <td class="highlight-value">Rs. ' . htmlspecialchars($processingFee) . '</td>
        <td class="label">Disbursed Amount:</td>
        <td class="value">Rs. ' . htmlspecialchars($loanAmount) . '</td>
    </tr>
    <tr>
        <td class="label">Payment Date:</td>
        <td class="value">' . htmlspecialchars($paymentDate) . '</td>
        <td class="label"></td>
        <td class="value"></td>
    </tr>
</table>

<br>
<div class="footer-note">
    * Note: Appraiser Charge is collected at the time of loan disbursement. Processing Fee is collected upon jewel release / redemption.
</div>

<table class="sig-table">
    <tr>
        <td style="width: 33%;">___________________________<br>Customer Signature / Thumb</td>
        <td style="width: 34%;">___________________________<br>Appraiser Signature</td>
        <td style="width: 33%;">___________________________<br>Authorized Signatory / Manager</td>
    </tr>
</table>
';

$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Close and output PDF (Inline view in browser)
$pdfFileName = 'Jewel_Loan_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $accNo) . '.pdf';
$pdf->Output($pdfFileName, 'I');
exit;
