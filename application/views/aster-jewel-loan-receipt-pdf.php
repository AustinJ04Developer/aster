<?php
session_start();
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once '../settings/db.php';
require_once 'controller/model/JewelLoanModel.php';
require_once 'coe-report/tcpdf.php';

$receiptId = (int)($_GET['receipt_id'] ?? 0);
$receiptNo = trim($_GET['receipt_no'] ?? '');

if ($receiptId <= 0 && empty($receiptNo)) {
    die("Error: Receipt ID or Receipt Number is required.");
}

$model = new JewelLoanModel($conn);
$receiptData = $model->getReceiptById($receiptId, $receiptNo);

if (!$receiptData || empty($receiptData['receipt'])) {
    die("Error: Receipt record not found.");
}

$receipt = $receiptData['receipt'];
$loan    = $receiptData['loan'] ?? [];
$cust    = $receiptData['customer'] ?? [];
$items   = $receiptData['items'] ?? [];

$rNo        = $receipt['ReceiptNo'];
$rDate      = !empty($receipt['ReceiptDate']) ? date('d-m-Y', strtotime($receipt['ReceiptDate'])) : date('d-m-Y');
$dNum       = $receipt['DNumber'];
$loanId     = (int)$receipt['LoanDetailId'];
$txnTypeRaw = $receipt['TransactionType'];
$payMethod  = $receipt['PaymentMethod'];

$txnTypeLabels = [
    'LOAN_CREATION'    => 'JEWEL LOAN DISBURSEMENT RECEIPT',
    'INTEREST_PAYMENT' => 'JEWEL LOAN INTEREST PAYMENT RECEIPT',
    'JEWEL_RELEASE'    => 'JEWEL RELEASE / CLOSURE RECEIPT'
];
$receiptTitle = $txnTypeLabels[$txnTypeRaw] ?? 'JEWEL LOAN TRANSACTION RECEIPT';

$principalPaid   = (float)$receipt['PrincipalPaid'];
$interestPaid    = (float)$receipt['InterestPaid'];
$appraiserCharge = (float)$receipt['AppraiserCharge'];
$processingFee   = (float)$receipt['ProcessingFee'];
$totalPaid       = (float)$receipt['TotalPaid'];
$balanceAmount   = (float)$receipt['BalanceLoanAmount'];
$interestMonths  = (float)$receipt['InterestMonths'];
$periodFrom      = !empty($receipt['InterestPeriodFrom']) ? date('d-m-Y', strtotime($receipt['InterestPeriodFrom'])) : '';
$periodTo        = !empty($receipt['InterestPeriodTo']) ? date('d-m-Y', strtotime($receipt['InterestPeriodTo'])) : '';

$custName    = $cust['Name'] ?? 'N/A';
$fathersName = $cust['FathersName'] ?? '';
$mobile      = $cust['MobileNumber'] ?? '';
$address     = $cust['Address'] ?? '';
$pincode     = $cust['Pincode'] ?? '';
$aadhaar     = $cust['AadhaarNumber'] ?? '';
$loanDate = !empty($loan['LoanDate']) ? date('d-m-Y', strtotime($loan['LoanDate'])) : '-';
$monthlyDueDate = !empty($receipt['MonthlyDueDate']) ? date('d-m-Y', strtotime($receipt['MonthlyDueDate'])) : (!empty($loan['LoanDate']) ? date('d-m-Y', strtotime($loan['LoanDate'] . ' +1 month')) : '-');
$closingDueDate = !empty($receipt['ClosingDueDate']) ? date('d-m-Y', strtotime($receipt['ClosingDueDate'])) : (!empty($loan['LoanDate']) ? date('d-m-Y', strtotime($loan['LoanDate'] . ' +1 year')) : '-');
$intRate = isset($loan['InterestRate']) ? number_format((float)$loan['InterestRate'], 2) . '%' : '18.00%';

// Calculate monthly due date using model if possible
if (!empty($loan['LoanDate'])) {
    $calcDates = $model->calculateInterestDue($loan['LoanDate'], (float)$loan['LoanAmount'], (float)$loan['InterestRate']);
    if (!empty($calcDates['monthlyDueDate'])) {
        $monthlyDueDate = date('d-m-Y', strtotime($calcDates['monthlyDueDate']));
    }
    if (!empty($calcDates['closingDueDate'])) {
        $closingDueDate = date('d-m-Y', strtotime($calcDates['closingDueDate']));
    }
}

// Initialize TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Aster Public Development Foundation');
$pdf->SetTitle('Receipt - ' . $rNo);
$pdf->SetSubject('Jewel Loan Receipt');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(TRUE, 12);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

$html = '
<style>
    .header-table { width: 100%; border-bottom: 2px solid #0088cc; margin-bottom: 8px; }
    .org-title { font-size: 15pt; font-weight: bold; color: #1a4d79; text-align: center; }
    .doc-subtitle { font-size: 11pt; font-weight: bold; color: #0088cc; text-align: center; margin-top: 2px; }
    .section-title { font-size: 10pt; font-weight: bold; color: #1a4d79; background-color: #f0f4f8; padding: 4px; border: 1px solid #d0dbe5; margin-top: 6px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table td { padding: 4px 6px; font-size: 9pt; }
    .label { font-weight: bold; color: #333333; width: 24%; }
    .value { color: #111111; width: 26%; }
    .grid-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .grid-table th { background-color: #f5f5f5; font-weight: bold; text-align: center; border: 1px solid #cccccc; padding: 5px; font-size: 8.5pt; }
    .grid-table td { border: 1px solid #cccccc; padding: 5px; font-size: 8.5pt; }
    .total-row td { background-color: #eef6fc; font-weight: bold; color: #005580; }
    .sig-table { width: 100%; margin-top: 35px; }
    .sig-table td { text-align: center; font-size: 8.5pt; font-weight: bold; padding-top: 35px; }
</style>

<table class="header-table" cellpadding="2">
    <tr>
        <td class="org-title">ASTER PUBLIC DEVELOPMENT FOUNDATION</td>
    </tr>
    <tr>
        <td class="doc-subtitle">' . htmlspecialchars($receiptTitle) . '</td>
    </tr>
</table>

<table class="data-table" cellpadding="3">
    <tr>
        <td class="label">Receipt Number:</td>
        <td class="value"><strong>' . htmlspecialchars($rNo) . '</strong></td>
        <td class="label">Receipt Date:</td>
        <td class="value">' . htmlspecialchars($rDate) . '</td>
    </tr>
    <tr>
        <td class="label">Customer D-Number:</td>
        <td class="value"><strong>' . htmlspecialchars($dNum) . '</strong></td>
        <td class="label">Loan ID:</td>
        <td class="value"><strong>JL-' . str_pad((string)$loanId, 4, '0', STR_PAD_LEFT) . '</strong></td>
    </tr>
    <tr>
        <td class="label">Loan Date:</td>
        <td class="value">' . htmlspecialchars($loanDate) . '</td>
        <td class="label">Interest Rate:</td>
        <td class="value"><strong>' . htmlspecialchars($intRate) . '</strong></td>
    </tr>
    <tr>
        <td class="label">Monthly Due Date:</td>
        <td class="value">' . htmlspecialchars($monthlyDueDate) . '</td>
        <td class="label">Closing Due Date:</td>
        <td class="value">' . htmlspecialchars($closingDueDate) . '</td>
    </tr>
    <tr>
        <td class="label">Transaction Type:</td>
        <td class="value"><strong>' . htmlspecialchars($txnTypeRaw) . '</strong></td>
        <td class="label">Payment Mode:</td>
        <td class="value"><strong>' . htmlspecialchars($payMethod) . '</strong></td>
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
</table>

<div class="section-title">TRANSACTION BREAKDOWN & PAYMENT DETAILS</div>
<table class="grid-table" cellpadding="4">
    <thead>
        <tr>
            <th style="width: 10%;">Sl No</th>
            <th style="width: 60%; text-align: left;">Description</th>
            <th style="width: 30%; text-align: right;">Amount (Rs.)</th>
        </tr>
    </thead>
    <tbody>';

$sl = 1;
if ($principalPaid > 0) {
    $desc = ($txnTypeRaw === 'LOAN_CREATION') ? 'Principal Loan Amount Sanctioned / Disbursed' : 'Principal Repayment';
    $html .= '<tr>
        <td style="text-align: center;">' . $sl++ . '</td>
        <td>' . $desc . '</td>
        <td style="text-align: right;">' . number_format($principalPaid, 2) . '</td>
    </tr>';
}

if ($interestPaid > 0) {
    $periodText = '';
    if (!empty($periodFrom) && !empty($periodTo)) {
        $periodText = ' (Period: ' . $periodFrom . ' to ' . $periodTo . ' - ' . $interestMonths . ' Mos)';
    }
    $html .= '<tr>
        <td style="text-align: center;">' . $sl++ . '</td>
        <td>Interest Received' . htmlspecialchars($periodText) . '</td>
        <td style="text-align: right;">' . number_format($interestPaid, 2) . '</td>
    </tr>';
}

$defaultInterestPaid = (float)($receipt['DefaultInterestPaid'] ?? 0);
if ($defaultInterestPaid > 0) {
    $html .= '<tr>
        <td style="text-align: center;">' . $sl++ . '</td>
        <td>Default / Overdue Interest</td>
        <td style="text-align: right;">' . number_format($defaultInterestPaid, 2) . '</td>
    </tr>';
}

if ($appraiserCharge > 0) {
    $html .= '<tr>
        <td style="text-align: center;">' . $sl++ . '</td>
        <td>Appraiser Charges</td>
        <td style="text-align: right;">' . number_format($appraiserCharge, 2) . '</td>
    </tr>';
}

if ($processingFee > 0) {
    $html .= '<tr>
        <td style="text-align: center;">' . $sl++ . '</td>
        <td>Processing Fee</td>
        <td style="text-align: right;">' . number_format($processingFee, 2) . '</td>
    </tr>';
}

$html .= '
        <tr class="total-row">
            <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL PAID:</td>
            <td style="text-align: right; font-weight: bold; font-size: 10pt;">Rs. ' . number_format($totalPaid, 2) . '</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold; color: #a94442;">OUTSTANDING LOAN BALANCE:</td>
            <td style="text-align: right; font-weight: bold; color: #a94442;">Rs. ' . number_format($balanceAmount, 2) . '</td>
        </tr>
    </tbody>
</table>';

// Payment details breakdown
$html .= '<div class="section-title">PAYMENT INSTRUMENT DETAILS</div>
<table class="data-table" cellpadding="3">';
if ($payMethod === 'Cash') {
    $html .= '<tr><td class="label">Payment Mode:</td><td class="value" colspan="3">Cash in Office Counter</td></tr>';
} elseif ($payMethod === 'Cheque') {
    $html .= '<tr>
        <td class="label">Cheque Number:</td><td class="value">' . htmlspecialchars($receipt['ChequeNumber']) . '</td>
        <td class="label">From Bank:</td><td class="value">' . htmlspecialchars($receipt['ChequeFromBank']) . '</td>
    </tr>
    <tr>
        <td class="label">To Bank (Aster):</td><td class="value" colspan="3">' . htmlspecialchars($receipt['ChequeToBank']) . '</td>
    </tr>';
} elseif ($payMethod === 'RTGS') {
    $html .= '<tr>
        <td class="label">RTGS / UTR No:</td><td class="value">' . htmlspecialchars($receipt['RtgsNumber']) . '</td>
        <td class="label">From Bank:</td><td class="value">' . htmlspecialchars($receipt['RtgsFromBank']) . '</td>
    </tr>
    <tr>
        <td class="label">To Bank (Aster):</td><td class="value" colspan="3">' . htmlspecialchars($receipt['RtgsToBank']) . '</td>
    </tr>';
} elseif ($payMethod === 'UPI') {
    $html .= '<tr>
        <td class="label">UPI App / Platform:</td><td class="value">' . htmlspecialchars($receipt['UpiType']) . '</td>
        <td class="label">UPI Reference No:</td><td class="value">' . htmlspecialchars($receipt['UpiTransactionNumber']) . '</td>
    </tr>
    <tr>
        <td class="label">Credited Bank:</td><td class="value" colspan="3">' . htmlspecialchars($receipt['UpiToBank']) . '</td>
    </tr>';
}
$html .= '</table>';

// If jewel release, display jewel items delivered
if ($txnTypeRaw === 'JEWEL_RELEASE' && !empty($items)) {
    $html .= '<div class="section-title">PLEDGED JEWEL RELEASE CONFIRMATION</div>
    <table class="grid-table" cellpadding="3">
        <thead>
            <tr>
                <th style="width: 10%;">Sl No</th>
                <th style="width: 45%; text-align: left;">Item Description</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 15%;">Gross (g)</th>
                <th style="width: 15%;">Net (g)</th>
            </tr>
        </thead>
        <tbody>';
    $totG = 0; $totN = 0; $totQty = 0;
    foreach ($items as $idx => $it) {
        $totG += (float)$it['Gram'];
        $totN += (float)$it['NetWeight'];
        $totQty += (int)$it['NoOfItems'];
        $html .= '<tr>
            <td style="text-align: center;">' . ($idx + 1) . '</td>
            <td>' . htmlspecialchars($it['ItemName']) . '</td>
            <td style="text-align: center;">' . (int)$it['NoOfItems'] . '</td>
            <td style="text-align: right;">' . number_format((float)$it['Gram'], 3) . '</td>
            <td style="text-align: right;">' . number_format((float)$it['NetWeight'], 3) . '</td>
        </tr>';
    }
    $html .= '<tr class="total-row">
            <td colspan="2" style="text-align: right; font-weight: bold;">Total Released:</td>
            <td style="text-align: center;">' . $totQty . '</td>
            <td style="text-align: right;">' . number_format($totG, 3) . '</td>
            <td style="text-align: right;">' . number_format($totN, 3) . '</td>
        </tr>
    </tbody>
    </table>
    <p style="font-size: 8pt; color: #2e6da4; margin-top: 5px;"><em>I hereby confirm that I have received all the pledged ornaments mentioned above in safe and good condition.</em></p>';
}

$html .= '
<table class="sig-table">
    <tr>
        <td style="width: 33%;">Customer Signature</td>
        <td style="width: 34%;">Cashier / Officer</td>
        <td style="width: 33%;">Authorized Signatory</td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Clean output buffer to ensure pure PDF stream without header conflicts
if (ob_get_length()) {
    ob_end_clean();
}

$pdf->Output('Jewel_Loan_Receipt_' . $rNo . '.pdf', 'I');
?>
