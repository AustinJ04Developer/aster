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
require_once 'controller/JewelLoanReceiptController.php';

$controller = new JewelLoanReceiptController($conn);
$data = $controller->handleRequest();

$success = $data['success'];
$errors  = $data['errors'];
$selectedLoan = $data['selectedLoan'] ?? null;
$searchResults = $data['searchResults'] ?? [];
$receiptView   = $data['receiptView'] ?? null;
$nextReceiptNo = $data['nextReceiptNo'] ?? '';
$companyBanks  = $data['companyBanks'] ?? [];
$upiTypes      = $data['upiTypes'] ?? [];
$searchQuery   = $data['searchQuery'] ?? '';
$activeDNumbers= $data['activeDNumbers'] ?? [];

// If viewing a single receipt directly in HTML print/preview mode
$isViewOnly = (isset($_GET['view_only']) || !empty($_GET['receipt_id']) || !empty($_GET['receipt_no'])) && !empty($receiptView);

// Current system date
$todayDateDisplay = date('d/m/Y');

/**
 * Helper: Convert numeric amount into Indian Currency words
 */
function getIndianCurrencyWords($number) {
    $number = round((float)$number, 2);
    if ($number <= 0) {
        return 'Zero Rupees Only';
    }
    $no = floor($number);
    $decimal = (int)round(($number - $no) * 100);
    $digits_length = strlen((string)$no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety'
    );
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $numPart = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($numPart) {
            $plural = (($counter = count($str)) && $numPart > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($numPart < 21) ? $words[$numPart] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                                     : $words[floor($numPart / 10) * 10] . ' ' . $words[$numPart % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
        } else {
            $str[] = null;
        }
    }
    $Rupees = implode('', array_reverse($str));
    $paise = '';
    if ($decimal > 0) {
        $paiseWords = ($decimal < 21)
            ? $words[$decimal]
            : trim($words[floor($decimal / 10) * 10] . ' ' . $words[$decimal % 10]);
        $paise = ($Rupees ? ' and ' : '') . $paiseWords . ' Paise';
    }
    return trim(($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Jewel Loan Receipt</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <style>
        .page-header-title {
            color: #337ab7;
            font-size: 22px;
            font-weight: 600;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .mandatory-star {
            color: #d9534f;
            font-weight: bold;
        }
        .mandatory-note {
            color: #666;
            font-size: 13px;
            text-align: right;
            margin-top: 18px;
            margin-bottom: 5px;
        }
        .receipt-container-card {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .section-divider {
            border: 0;
            height: 1px;
            background-color: #31b0d5;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .section-subheading {
            font-size: 15px;
            font-weight: bold;
            color: #204d74;
            margin-top: 5px;
            margin-bottom: 12px;
            padding-bottom: 4px;
            border-bottom: 1px solid #eee;
        }
        .form-label-bold {
            font-size: 13px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 4px;
            display: block;
        }
        .form-control-readonly {
            background-color: #f7f7f7 !important;
            cursor: default;
            color: #333333;
            font-weight: 600;
        }
        .highlight-amount-field {
            color: #d9534f !important;
            font-weight: bold !important;
            font-size: 14px !important;
        }
        .highlight-blue-field {
            color: #204d74 !important;
            font-weight: bold !important;
            font-size: 14px !important;
        }
        .radio-options-group {
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .radio-options-group label {
            margin-right: 20px;
            font-weight: normal;
            cursor: pointer;
            font-size: 14px;
        }
        .radio-options-group input[type="radio"] {
            margin-right: 5px;
        }
        .action-button-row {
            margin-top: 15px;
            margin-bottom: 10px;
            text-align: center;
        }
        .btn-receipt-new {
            background-color: #f0ad4e;
            border-color: #eea236;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 20px;
            margin-right: 10px;
        }
        .btn-receipt-new:hover, .btn-receipt-new:focus {
            background-color: #ec971f;
            border-color: #d58512;
            color: #ffffff;
        }
        .btn-receipt-save {
            background-color: #5bc0de;
            border-color: #46b8da;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 25px;
        }
        .btn-receipt-save:hover, .btn-receipt-save:focus {
            background-color: #31b0d5;
            border-color: #269abc;
            color: #ffffff;
        }
        .ornaments-table {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .ornaments-table thead tr th {
            background-color: #e8f3f8 !important;
            color: #0b3e5b !important;
            text-align: center;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #d0e4ee;
            font-size: 13px;
        }
        .ornaments-table tbody td {
            padding: 6px 8px;
            border: 1px solid #e3e3e3;
            vertical-align: middle;
        }
        .ornaments-table tbody td input {
            background-color: #fcfcfc !important;
            border: 1px solid #d5d5d5;
            border-radius: 3px;
            height: 30px;
            padding: 3px 8px;
            font-size: 13px;
            color: #333333;
        }

        /* Voucher View Specific Styling */
        .voucher-card {
            background: #ffffff;
            border: 2px solid #204d74;
            border-radius: 6px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .voucher-header {
            text-align: center;
            border-bottom: 2px solid #204d74;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .voucher-org-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a4d79;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .voucher-org-sub {
            font-size: 13px;
            color: #555;
            margin-bottom: 8px;
        }
        .voucher-badge {
            display: inline-block;
            background: #204d74;
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
            padding: 4px 18px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .voucher-table-meta {
            width: 100%;
            margin-bottom: 15px;
        }
        .voucher-table-meta td {
            padding: 5px 8px;
            font-size: 13px;
        }
        .voucher-table-breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .voucher-table-breakdown th {
            background: #f0f4f8;
            color: #204d74;
            border: 1px solid #ccc;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 13px;
        }
        .voucher-table-breakdown td {
            border: 1px solid #ccc;
            padding: 7px 10px;
            font-size: 13px;
        }
        .voucher-signatures {
            margin-top: 35px;
            padding-top: 10px;
        }
        .signature-col {
            text-align: center;
            border-top: 1px dashed #666;
            padding-top: 5px;
            font-weight: 600;
            font-size: 12px;
            color: #444;
        }

        /* Responsive Layout Tweaks */
        @media (max-width: 767px) {
            .receipt-container-card {
                padding: 15px 12px;
            }
            .page-header-title {
                font-size: 18px;
            }
            .mandatory-note {
                text-align: left;
                margin-top: 0;
            }
            .voucher-card {
                padding: 15px;
            }
            .action-button-row button {
                width: 100%;
                margin-bottom: 8px;
                margin-right: 0 !important;
            }
        }

        @media print {
            #side-menu, .navbar, .page-header, .no-print, .breadcrumb, footer, .alert, .action-button-row {
                display: none !important;
            }
            #page-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            .receipt-container-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .voucher-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 10px !important;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">

        <!-- ============================================================= -->
        <!-- BRANCH A: RECEIPT VOUCHER VIEW MODE (Dedicated View in Page) -->
        <!-- ============================================================= -->
        <?php if ($isViewOnly && !empty($receiptView['receipt'])): ?>
            <?php
            $r = $receiptView['receipt'];
            $l = $receiptView['loan'] ?? [];
            $c = $receiptView['customer'] ?? [];
            $itms = $receiptView['items'] ?? [];

            $rNo = htmlspecialchars($r['ReceiptNo']);
            $rDate = !empty($r['ReceiptDate']) ? date('d-m-Y', strtotime($r['ReceiptDate'])) : date('d-m-Y');
            $dNum = htmlspecialchars($r['DNumber']);
            $custName = htmlspecialchars($c['Name'] ?? 'Customer');
            $mobile = htmlspecialchars($c['MobileNumber'] ?? '-');
            $address = htmlspecialchars($c['Address'] ?? '-');
            $loanDateStr = !empty($l['LoanDate']) ? date('d-m-Y', strtotime($l['LoanDate'])) : '-';

            $principalPaid = (float)$r['PrincipalPaid'];
            $interestPaid = (float)$r['InterestPaid'];
            $defaultInterestPaid = (float)$r['DefaultInterestPaid'];
            $appraiserCharge = (float)$r['AppraiserCharge'];
            $processingFee = (float)$r['ProcessingFee'];
            $totalPaid = (float)$r['TotalPaid'];
            $remainingBalance = (float)$r['BalanceLoanAmount'];
            $wordsAmount = getIndianCurrencyWords($totalPaid);
            ?>

            <div class="row no-print" style="margin-top: 15px; margin-bottom: 15px;">
                <div class="col-sm-6">
                    <h3 style="color: #337ab7; margin: 0; font-weight: bold;">
                        <i class="fa fa-ticket"></i> Receipt Voucher - <?php echo $rNo; ?>
                    </h3>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print();">
                        <i class="fa fa-print"></i> Print Voucher
                    </button>
                    <a href="aster-jewel-loan-receipt-pdf.php?receipt_id=<?php echo urlencode($r['ReceiptId']); ?>" target="_blank" class="btn btn-danger btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </a>
                    <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($r['DNumber']); ?>" class="btn btn-info btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-eye"></i> View Loan Details
                    </a>
                    <a href="aster-jewel-loan-receipt.php" class="btn btn-default btn-sm" style="margin-left: 5px;">
                        <i class="fa fa-plus"></i> New Receipt
                    </a>
                </div>
            </div>

            <!-- Printable Voucher Container -->
            <div class="voucher-card">
                <div class="voucher-header">
                    <div class="voucher-org-title">ASTER PUBLIC DEVELOPMENT FOUNDATION</div>
                    <div class="voucher-org-sub">Jewel Loan Division &bull; Panachamoodu, Kanyakumari District</div>
                    <div class="voucher-badge">
                        <?php echo htmlspecialchars($r['TransactionType'] === 'JEWEL_RELEASE' ? 'JEWEL RELEASE / CLOSURE RECEIPT' : 'JEWEL LOAN RECEIPT VOUCHER'); ?>
                    </div>
                </div>

                <table class="voucher-table-meta">
                    <tr>
                        <td style="width: 20%;"><strong>Receipt No:</strong></td>
                        <td style="width: 30%; color: #d9534f; font-weight: bold; font-size: 15px;"><?php echo $rNo; ?></td>
                        <td style="width: 20%;"><strong>Receipt Date:</strong></td>
                        <td style="width: 30%; font-weight: 600;"><?php echo $rDate; ?></td>
                    </tr>
                    <tr>
                        <td><strong>D Number:</strong></td>
                        <td><span class="label label-primary" style="font-size: 13px;"><?php echo $dNum; ?></span></td>
                        <td><strong>Loan Pledge Date:</strong></td>
                        <td><?php echo $loanDateStr; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Customer Name:</strong></td>
                        <td style="font-weight: bold; font-size: 14px;"><?php echo $custName; ?></td>
                        <td><strong>Mobile Number:</strong></td>
                        <td><?php echo $mobile; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Customer Address:</strong></td>
                        <td colspan="3"><?php echo $address; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Payment Mode:</strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($r['PaymentMethod']); ?></strong>
                            <?php if ($r['PaymentMethod'] === 'Cheque' && !empty($r['ChequeNumber'])): ?>
                                <small>(Cheque: <?php echo htmlspecialchars($r['ChequeNumber']); ?>)</small>
                            <?php elseif ($r['PaymentMethod'] === 'RTGS' && !empty($r['RtgsNumber'])): ?>
                                <small>(UTR: <?php echo htmlspecialchars($r['RtgsNumber']); ?>)</small>
                            <?php elseif ($r['PaymentMethod'] === 'UPI' && !empty($r['UpiTransactionNumber'])): ?>
                                <small>(Ref: <?php echo htmlspecialchars($r['UpiTransactionNumber']); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><strong>Transaction:</strong></td>
                        <td>
                            <span class="label label-<?php echo ($r['TransactionType'] === 'JEWEL_RELEASE' ? 'success' : 'info'); ?>">
                                <?php echo htmlspecialchars($r['TransactionType']); ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="table-responsive">
                    <table class="voucher-table-breakdown">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">Sl.No</th>
                                <th>Particulars / Description</th>
                                <th style="width: 180px; text-align: right;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sl = 1; ?>
                            <?php if ($principalPaid > 0): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td>Principal Amount Repayment</td>
                                    <td class="text-right"><?php echo number_format($principalPaid, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($interestPaid > 0): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td>Interest Received <?php if (!empty($r['InterestMonths'])) echo '(' . (float)$r['InterestMonths'] . ' Month/Period)'; ?></td>
                                    <td class="text-right"><?php echo number_format($interestPaid, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($defaultInterestPaid > 0): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td>Default / Overdue Interest</td>
                                    <td class="text-right"><?php echo number_format($defaultInterestPaid, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($appraiserCharge > 0): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td>Appraiser Fee</td>
                                    <td class="text-right"><?php echo number_format($appraiserCharge, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($processingFee > 0): ?>
                                <tr>
                                    <td class="text-center"><?php echo $sl++; ?></td>
                                    <td>Loan Processing Fee</td>
                                    <td class="text-right"><?php echo number_format($processingFee, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($sl === 1): ?>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>Jewel Loan Payment Received</td>
                                    <td class="text-right"><?php echo number_format($totalPaid, 2); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f7f9fa;">
                                <th colspan="2" class="text-right" style="font-size: 14px;">Total Received Amount:</th>
                                <th class="text-right" style="color: #d9534f; font-size: 15px; font-weight: bold;">
                                    ₹<?php echo number_format($totalPaid, 2); ?>
                                </th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-right">Remaining Principal Balance:</th>
                                <th class="text-right">₹<?php echo number_format($remainingBalance, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="margin-top: 10px; font-size: 13px;">
                    <strong>Amount in Words:</strong> 
                    <span style="font-style: italic; color: #1a4d79;"><?php echo $wordsAmount; ?></span>
                </div>

                <?php if (!empty($itms) && $r['TransactionType'] === 'JEWEL_RELEASE'): ?>
                    <div style="margin-top: 15px;">
                        <strong>Released Pledged Items:</strong>
                        <div class="table-responsive" style="margin-top: 5px;">
                            <table class="table table-bordered table-condensed" style="font-size: 12px;">
                                <tr style="background: #fafafa;">
                                    <th>Sl</th>
                                    <th>Item</th>
                                    <th>No of Items</th>
                                    <th class="text-right">Gross (g)</th>
                                    <th class="text-right">Net Wt (g)</th>
                                </tr>
                                <?php foreach ($itms as $idx => $it): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($it['ItemName']); ?></td>
                                        <td><?php echo (int)$it['NoOfItems']; ?></td>
                                        <td class="text-right"><?php echo number_format((float)$it['Gram'], 3); ?></td>
                                        <td class="text-right"><?php echo number_format((float)$it['NetWeight'], 3); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row voucher-signatures">
                    <div class="col-xs-4 text-center">
                        <div class="signature-col">Customer Signature</div>
                    </div>
                    <div class="col-xs-4 text-center">
                        <div class="signature-col">Cashier / Prepared By</div>
                    </div>
                    <div class="col-xs-4 text-center">
                        <div class="signature-col">Authorized Officer</div>
                    </div>
                </div>
            </div>

        <!-- ============================================================= -->
        <!-- BRANCH B: RECEIPT ENTRY & SEARCH FORM MODE                   -->
        <!-- ============================================================= -->
        <?php else: ?>

            <div class="row no-print">
                <div class="col-sm-6 col-xs-12">
                    <h3 class="page-header-title">Jewel Loan Receipt</h3>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <p class="mandatory-note"><span class="mandatory-star">*</span> Fields are mandatory</p>
                </div>
            </div>
            <hr class="hr-background no-print" style="margin-top: 5px; margin-bottom: 20px;">

            <!-- Process Status Modal & In-Page Visual Confirmation -->
            <?php include_once "process-status-modal.php"; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible no-print" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <?php foreach ($errors as $e): ?>
                        <p style="margin: 0;"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($e); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Search Error Notification Box (Dynamic) -->
            <div id="dnumber-search-alert" class="alert alert-warning alert-dismissible no-print" style="display: none;" role="alert">
                <button type="button" class="close" onclick="$('#dnumber-search-alert').slideUp();"><span aria-hidden="true">&times;</span></button>
                <span id="dnumber-search-alert-msg"></span>
            </div>

            <?php
            // Prepare loan fields if loan selected
            $l = $selectedLoan['loan'] ?? null;
            $c = $selectedLoan['customer'] ?? null;
            $calc = $selectedLoan['calculation'] ?? null;
            $items = $selectedLoan['items'] ?? [];
            $loanReceiptHistory = $selectedLoan['receipts'] ?? [];

            $selDNumber = $l['DNumber'] ?? ($_GET['dnum'] ?? '');
            $customerName = $c['Name'] ?? '';
            $interestRateVal = isset($calc['interestRate']) ? number_format((float)$calc['interestRate'], 2) : (isset($l['InterestRate']) ? number_format((float)$l['InterestRate'], 2) : '18.00');
            $totDaysDisplay = $calc['totDaysDisplay'] ?? '0';
            $balanceLoanVal = isset($calc['principalBalance']) ? number_format((float)$calc['principalBalance'], 2, '.', '') : (isset($l['BalanceLoanAmount']) ? number_format((float)$l['BalanceLoanAmount'], 2, '.', '') : '0.00');
            $accruedInterestVal = isset($calc['accruedInterest']) ? number_format((float)$calc['accruedInterest'], 2, '.', '') : '0.00';
            $defaultInterestVal = isset($calc['defaultInterest']) ? number_format((float)$calc['defaultInterest'], 2, '.', '') : '0.00';
            $totalAmountDueVal = isset($calc['totalAmountDue']) ? number_format((float)$calc['totalAmountDue'], 2, '.', '') : '0.00';
            $gracePeriodDaysVal = isset($calc['gracePeriodDays']) ? (string)$calc['gracePeriodDays'] : '0';

            // Section 2 valuation fields
            $loanDateVal = !empty($l['LoanDate']) ? date('d/m/Y', strtotime($l['LoanDate'])) : '';
            $monthlyDueDateVal = !empty($calc['monthlyDueDate']) ? date('d/m/Y', strtotime($calc['monthlyDueDate'])) : '';
            $closingDueDateVal = !empty($calc['closingDueDate']) ? date('d/m/Y', strtotime($calc['closingDueDate'])) : (!empty($calc['dueDate']) ? date('d/m/Y', strtotime($calc['dueDate'])) : '');
            if (empty($closingDueDateVal) && !empty($l['LoanDate'])) {
                $dt = new DateTime($l['LoanDate']);
                $dt->modify('+1 year');
                $closingDueDateVal = $dt->format('d/m/Y');
            }
            $valuePerGramVal = isset($l['AmountPerGram']) && (float)$l['AmountPerGram'] > 0 ? number_format((float)$l['AmountPerGram'], 2, '.', '') : '0.00';
            $valuationAmountVal = isset($l['TotalAmount']) && (float)$l['TotalAmount'] > 0 ? number_format((float)$l['TotalAmount'], 2, '.', '') : '0.00';
            $totalNetGramsVal = isset($l['TotalNetWeight']) ? number_format((float)$l['TotalNetWeight'], 3, '.', '') : '0.000';
            
            // Sum gross and wastage from ornaments
            $sumGross = 0.0;
            $sumWastage = 0.0;
            if (!empty($items)) {
                foreach ($items as $it) {
                    $sumGross += (float)$it['Gram'];
                    $sumWastage += (float)$it['Wastage'];
                }
            }
            $totalGrossVal = $sumGross > 0 ? number_format($sumGross, 3, '.', '') : $totalNetGramsVal;
            $totalWastageVal = number_format($sumWastage, 3, '.', '');

            $permissiblePerGramVal = isset($l['MaxAmountPerGram']) && (float)$l['MaxAmountPerGram'] > 0 ? number_format((float)$l['MaxAmountPerGram'], 2, '.', '') : '0.00';
            $permissibleAmountVal = isset($l['AllowedAmount']) && (float)$l['AllowedAmount'] > 0 ? number_format((float)$l['AllowedAmount'], 2, '.', '') : '0.00';
            $originalLoanAmountVal = isset($l['LoanAmount']) ? number_format((float)$l['LoanAmount'], 2, '.', '') : '0.00';

            // Section 3 Customer fields
            $custOccupation = $c['Job'] ?? ($c['Occupation'] ?? '');
            $custFathersName = $c['FathersName'] ?? ($c['SDWName'] ?? '');
            $custAddress = $c['Address'] ?? ($c['PermenantAddress'] ?? '');
            $custDistrict = $c['DistrictName'] ?? ($c['District'] ?? '');
            $custContactNumber = $c['MobileNumber'] ?? ($c['MobileNo'] ?? '');
            $custPincode = $c['Pincode'] ?? ($c['PinCode'] ?? '');
            ?>

            <!-- MAIN RECEIPT FORM CONTAINER -->
            <div class="receipt-container-card">
                <form method="post" action="aster-jewel-loan-receipt.php" id="jewel-loan-receipt-form">
                    <input type="hidden" name="loan_detail_id" id="loan_detail_id" value="<?php echo htmlspecialchars($l['LoanDetailId'] ?? ''); ?>">
                    <input type="hidden" name="d_number" id="hidden_d_number" value="<?php echo htmlspecialchars($selDNumber); ?>">
                    <input type="hidden" name="current_balance_loan" id="current_balance_loan" value="<?php echo htmlspecialchars($balanceLoanVal); ?>">
                    <input type="hidden" name="transaction_type" id="transaction_type" value="INTEREST_PAYMENT">
                    <input type="hidden" name="action_type" id="action_type" value="save">
                    <input type="hidden" name="interest_months" id="interest_months" value="<?php echo htmlspecialchars($calc['calculatedMonths'] ?? '1'); ?>">
                    <input type="hidden" name="principal_paid" id="hidden_principal_paid" value="0.00">
                    <input type="hidden" name="period_from" id="hidden_period_from" value="<?php echo htmlspecialchars($calc['lastCalculatedDate'] ?? ($l['LoanDate'] ?? '')); ?>">
                    <input type="hidden" name="period_to" id="hidden_period_to" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>">

                    <!-- ========================================== -->
                    <!-- SECTION 1: Date, D Number Search, Payment Details -->
                    <!-- ========================================== -->
                    <div class="row">
                        <div class="col-md-3 col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Date:</label>
                                <input type="text" name="receipt_date" id="receipt_date" class="form-control form-control-readonly text-center" 
                                       value="<?php echo htmlspecialchars($todayDateDisplay); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-5 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Select D Number: <span class="mandatory-star">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="search_d_number" id="search_d_number" class="form-control" 
                                           list="active_dnumbers_list" placeholder="Enter D Number (e.g. D-0001)" 
                                           value="<?php echo htmlspecialchars($selDNumber); ?>" autocomplete="off" required>
                                    <datalist id="active_dnumbers_list">
                                        <?php foreach ($activeDNumbers as $ad): ?>
                                            <option value="<?php echo htmlspecialchars($ad['DNumber']); ?>">
                                                <?php echo htmlspecialchars($ad['DNumber'] . ' - ' . ($ad['Name'] ?? 'Customer')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" id="btn-clear-dnumber" title="Clear Search">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold hidden-xs">&nbsp;</label>
                                <button type="button" id="btn-search-dnumber" class="btn btn-success btn-block" style="font-weight: bold; height: 34px;">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1 of Loan Summary Fields: D Number, Interest Rate, Name, Tot Days -->
                    <div class="row">
                        <div class="col-md-2 col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="form-label-bold">D Number: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_d_number" class="form-control form-control-readonly text-center" 
                                       value="<?php echo htmlspecialchars($selDNumber); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label class="form-label-bold">Interest Rate: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_interest_rate" class="form-control form-control-readonly text-center" 
                                       value="<?php echo htmlspecialchars($interestRateVal); ?>%" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Customer Name: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_customer_name" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($customerName); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Loan Days & Due Status:</label>
                                <input type="text" id="display_tot_days" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($totDaysDisplay); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 of Loan Summary Fields: Balance Loan Amount, Interest, Default Interest, Total Due -->
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Balance Loan Amount:</label>
                                <input type="text" id="display_balance_loan" class="form-control form-control-readonly highlight-amount-field text-right" 
                                       value="<?php echo htmlspecialchars($balanceLoanVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Interest:</label>
                                <input type="text" name="interest_paid" id="display_interest" class="form-control form-control-readonly highlight-amount-field text-right" 
                                       value="<?php echo htmlspecialchars($accruedInterestVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Default Interest: <span class="mandatory-star">*</span></label>
                                <input type="text" name="default_interest" id="display_default_interest" class="form-control form-control-readonly highlight-amount-field text-right" 
                                       value="<?php echo htmlspecialchars($defaultInterestVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Total (Loan + Int. + Def. Int):</label>
                                <input type="text" id="display_total_due" class="form-control form-control-readonly highlight-amount-field text-right" 
                                       value="<?php echo htmlspecialchars($totalAmountDueVal); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 of Loan Summary Fields: Grace Period Days, Amount, Transaction Type -->
                    <div class="row">
                        <div class="col-md-3 col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Grace Period (Days): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_grace_period" class="form-control form-control-readonly text-center" 
                                       value="<?php echo htmlspecialchars($gracePeriodDaysVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Receiving Amount (₹): <span class="mandatory-star">*</span></label>
                                <?php 
                                $suggestedIntAmt = round((float)$accruedInterestVal + (float)$defaultInterestVal, 2);
                                $initPayAmt = $suggestedIntAmt > 0 ? number_format($suggestedIntAmt, 2, '.', '') : (!empty($totalAmountDueVal) && (float)$totalAmountDueVal > 0 ? htmlspecialchars($totalAmountDueVal) : '');
                                ?>
                                <input type="number" step="0.01" min="1" name="total_paid" id="amount_paying" class="form-control text-right" 
                                       placeholder="Enter amount" 
                                       value="<?php echo htmlspecialchars($initPayAmt); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Transaction Type:</label>
                                <select id="receipt_transaction_type_select" class="form-control" onchange="onTxnTypeChange(this.value)">
                                    <option value="INTEREST_PAYMENT" selected>Interest Payment (Receive Interest & Partial Balance)</option>
                                    <option value="JEWEL_RELEASE">Full Settlement & Jewel Release</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Live Payment Allocation Breakdown Banner -->
                    <div id="payment-allocation-summary" style="margin-top: 5px; margin-bottom: 20px; background: #f4f9fd; border: 1px solid #c8e1f5; border-radius: 5px; padding: 12px 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="row text-center">
                            <div class="col-xs-6 col-sm-3" style="border-right: 1px dashed #d6e5f1;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #555; font-weight: 700; display: block; margin-bottom: 3px;">Interest Covered</span>
                                <span id="alloc_interest_text" style="font-size: 16px; font-weight: bold; color: #2e6da4;">₹0.00</span>
                            </div>
                            <div class="col-xs-6 col-sm-3" style="border-right: 1px dashed #d6e5f1;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #555; font-weight: 700; display: block; margin-bottom: 3px;">Default Int. Covered</span>
                                <span id="alloc_default_int_text" style="font-size: 16px; font-weight: bold; color: #a94442;">₹0.00</span>
                            </div>
                            <div class="col-xs-6 col-sm-3" style="border-right: 1px dashed #d6e5f1;">
                                <span style="font-size: 11px; text-transform: uppercase; color: #555; font-weight: 700; display: block; margin-bottom: 3px;">Principal Reduced</span>
                                <span id="alloc_principal_text" style="font-size: 16px; font-weight: bold; color: #3c763d;">₹0.00</span>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <span style="font-size: 11px; text-transform: uppercase; color: #555; font-weight: 700; display: block; margin-bottom: 3px;">New Remaining Balance</span>
                                <span id="alloc_balance_text" style="font-size: 16px; font-weight: bold; color: #d9534f;">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Radio Group -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="radio-options-group">
                                <label class="radio-inline">
                                    <input type="radio" name="payment_method" value="Cash" checked onclick="toggleReceiptPaymentMethod('Cash')"> Cash
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_method" value="Cheque" onclick="toggleReceiptPaymentMethod('Cheque')"> Cheque
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_method" value="RTGS" onclick="toggleReceiptPaymentMethod('RTGS')"> RTGS
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_method" value="UPI" onclick="toggleReceiptPaymentMethod('UPI')"> UPI
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Payment Method Details (Conditional) -->
                    <div id="receipt-cheque-box" style="display: none; background: #fbfbfb; border: 1px dashed #cccccc; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px;">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">Cheque Number <span class="mandatory-star">*</span></label>
                                    <input type="text" name="cheque_number" id="cheque_number" class="form-control" placeholder="Cheque No.">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">From Bank (Customer)</label>
                                    <input type="text" name="cheque_from_bank" id="cheque_from_bank" class="form-control" placeholder="Customer Bank">
                                </div>
                            </div>
                            <div class="col-md-5 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">Deposited To (Company Bank)</label>
                                    <select name="cheque_to_bank" id="cheque_to_bank" class="form-control">
                                        <option value="">Select Company Bank Account</option>
                                        <?php foreach ($companyBanks as $b): ?>
                                            <option value="<?php echo htmlspecialchars($b['BankName']); ?>"><?php echo htmlspecialchars($b['BankName']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="receipt-rtgs-box" style="display: none; background: #fbfbfb; border: 1px dashed #cccccc; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px;">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">RTGS / UTR Number <span class="mandatory-star">*</span></label>
                                    <input type="text" name="rtgs_number" id="rtgs_number" class="form-control" placeholder="UTR Number">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">From Bank</label>
                                    <input type="text" name="rtgs_from_bank" id="rtgs_from_bank" class="form-control" placeholder="Sender Bank">
                                </div>
                            </div>
                            <div class="col-md-5 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">To Bank (Company Bank)</label>
                                    <select name="rtgs_to_bank" id="rtgs_to_bank" class="form-control">
                                        <option value="">Select Company Bank Account</option>
                                        <?php foreach ($companyBanks as $b): ?>
                                            <option value="<?php echo htmlspecialchars($b['BankName']); ?>"><?php echo htmlspecialchars($b['BankName']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="receipt-upi-box" style="display: none; background: #fbfbfb; border: 1px dashed #cccccc; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px;">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">UPI Type / App <span class="mandatory-star">*</span></label>
                                    <select name="upi_type" id="upi_type" class="form-control">
                                        <option value="">Select UPI App</option>
                                        <?php if (!empty($upiTypes)): ?>
                                            <?php foreach ($upiTypes as $u): ?>
                                                <option value="<?php echo htmlspecialchars($u['UpiName'] ?? $u['UPITypeName']); ?>">
                                                    <?php echo htmlspecialchars($u['UpiName'] ?? $u['UPITypeName']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="GPay">GPay</option>
                                            <option value="PhonePe">PhonePe</option>
                                            <option value="PayTM">PayTM</option>
                                            <option value="BHIM">BHIM</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">UPI Ref / Txn ID <span class="mandatory-star">*</span></label>
                                    <input type="text" name="upi_transaction_number" id="upi_transaction_number" class="form-control" placeholder="Transaction Reference No">
                                </div>
                            </div>
                            <div class="col-md-5 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label class="form-label-bold">Company Receiving Bank</label>
                                    <select name="upi_to_bank" id="upi_to_bank" class="form-control">
                                        <option value="">Select Company Bank Account</option>
                                        <?php foreach ($companyBanks as $b): ?>
                                            <option value="<?php echo htmlspecialchars($b['BankName']); ?>"><?php echo htmlspecialchars($b['BankName']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons: New Receipt & Save -->
                    <div class="action-button-row no-print">
                        <button type="button" class="btn btn-receipt-new" onclick="resetReceiptForm()">
                            <i class="fa fa-refresh"></i> New Receipt
                        </button>
                        <button type="submit" name="submit_receipt" id="btn-save-receipt" class="btn btn-receipt-save">
                            <i class="fa fa-save"></i> Save Receipt
                        </button>
                        <?php if (!empty($l['LoanDetailId'])): ?>
                            <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($l['DNumber']); ?>" class="btn btn-info" style="margin-left: 10px;">
                                <i class="fa fa-eye"></i> View Loan Details
                            </a>
                            <button type="button" class="btn btn-primary" onclick="window.print();" style="margin-left: 5px;">
                                <i class="fa fa-print"></i> Print Screen
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- ========================================== -->
                    <!-- SECTION 2: Loan and Gold Valuation Details -->
                    <!-- ========================================== -->
                    <hr class="section-divider">
                    <div class="section-subheading"><i class="fa fa-info-circle"></i> Loan and Gold Valuation Details</div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Loan Date: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_loan_date" class="form-control form-control-readonly highlight-blue-field text-center" 
                                       value="<?php echo htmlspecialchars($loanDateVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Monthly Due Date: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_monthly_due_date" class="form-control form-control-readonly highlight-blue-field text-center" 
                                       value="<?php echo htmlspecialchars($monthlyDueDateVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Closing Due Date: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_closing_due_date" class="form-control form-control-readonly highlight-blue-field text-center" 
                                       value="<?php echo htmlspecialchars($closingDueDateVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Interest Percentage: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_percentage" class="form-control form-control-readonly text-center" 
                                       value="<?php echo htmlspecialchars($interestRateVal); ?>%" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Value / Gram (₹): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_value_per_gram" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($valuePerGramVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Valuation Amount (₹): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_valuation_amount" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($valuationAmountVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Total Gram(s): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_total_grams" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($totalGrossVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Total Wastage (g): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_total_wastage" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($totalWastageVal); ?>" readonly>
                            </div>
                        </div>
                    </div> <!-- Properly closed row -->

                    <div class="row">
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Permissible/Gram (₹): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_permissible_per_gram" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($permissiblePerGramVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Permissible Amount (₹): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_permissible_amount" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($permissibleAmountVal); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Sanctioned Loan Amount (₹): <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_loan_amount" class="form-control form-control-readonly text-right" 
                                       value="<?php echo htmlspecialchars($originalLoanAmountVal); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- SECTION 3: Pledged Ornaments and Customer Details -->
                    <!-- ========================================== -->
                    <hr class="section-divider">
                    <div class="section-subheading"><i class="fa fa-diamond"></i> Pledged Ornaments</div>

                    <!-- A. Pledged Ornaments Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="ornaments-table" id="receipt-ornaments-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 70px;">SlNo</th>
                                            <th style="width: 140px;">No of Items</th>
                                            <th>Ornaments Name</th>
                                            <th style="width: 180px;">Gross Grams</th>
                                            <th style="width: 180px;">Wastage Grams</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ornaments-table-body">
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $idx => $it): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="text" class="form-control text-center form-control-readonly" value="<?php echo $idx + 1; ?>" readonly>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" class="form-control text-center form-control-readonly" value="<?php echo (int)$it['NoOfItems']; ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-readonly" value="<?php echo htmlspecialchars($it['ItemName']); ?>" readonly>
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="text" class="form-control text-right form-control-readonly" value="<?php echo number_format((float)$it['Gram'], 3); ?>" readonly>
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="text" class="form-control text-right form-control-readonly" value="<?php echo number_format((float)$it['Wastage'], 3); ?>" readonly>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <!-- Blank row when no loan selected -->
                                            <tr>
                                                <td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="1" readonly></td>
                                                <td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="-" readonly></td>
                                                <td><input type="text" class="form-control form-control-readonly" value="No loan selected" readonly></td>
                                                <td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>
                                                <td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- B. Customer Personal Details -->
                    <div class="section-subheading" style="margin-top: 15px;"><i class="fa fa-user"></i> Customer Personal Details</div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Occupation: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_cust_occupation" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custOccupation); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Father / Husband Name: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_cust_fathers_name" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custFathersName); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Home Name/No: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_cust_home_name" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custAddress); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Street: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_cust_street" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custAddress); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Village / Town:</label>
                                <input type="text" id="display_cust_village" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custAddress); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">District:</label>
                                <input type="text" id="display_cust_district" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custDistrict); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Contact Number: <span class="mandatory-star">*</span></label>
                                <input type="text" id="display_cust_mobile" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custContactNumber); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label-bold">Pincode:</label>
                                <input type="text" id="display_cust_pincode" class="form-control form-control-readonly" 
                                       value="<?php echo htmlspecialchars($custPincode); ?>" readonly>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <!-- /.receipt-container-card -->

            <!-- Recent Receipts Table for this Loan (if loan loaded) -->
            <?php if (!empty($l['LoanDetailId'])): ?>
                <?php
                // Fetch previous receipts for this loan
                $recHistory = [];
                $ldId = (int)$l['LoanDetailId'];
                $rRes = $conn->query("SELECT * FROM ast_jewel_loan_receipt WHERE LoanDetailId = {$ldId} ORDER BY ReceiptId DESC");
                if ($rRes) {
                    while ($rh = $rRes->fetch_assoc()) {
                        $recHistory[] = $rh;
                    }
                }
                ?>
                <?php if (!empty($recHistory)): ?>
                    <div class="receipt-container-card no-print">
                        <div class="section-subheading">
                            <i class="fa fa-history"></i> Receipts Issued for Loan <?php echo htmlspecialchars($l['DNumber']); ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background-color: #f5f5f5;">
                                        <th>Receipt No</th>
                                        <th>Receipt Date</th>
                                        <th>Transaction Type</th>
                                        <th>Payment Mode</th>
                                        <th class="text-right">Principal</th>
                                        <th class="text-right">Interest</th>
                                        <th class="text-right">Total Paid</th>
                                        <th class="text-right">Balance</th>
                                        <th class="text-center" style="width: 130px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recHistory as $rh): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($rh['ReceiptNo']); ?></strong></td>
                                            <td><?php echo date('d-m-Y', strtotime($rh['ReceiptDate'])); ?></td>
                                            <td>
                                                <span class="label label-<?php echo ($rh['TransactionType'] === 'JEWEL_RELEASE') ? 'success' : 'primary'; ?>">
                                                    <?php echo htmlspecialchars($rh['TransactionType']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($rh['PaymentMethod']); ?></td>
                                            <td class="text-right">₹<?php echo number_format((float)$rh['PrincipalPaid'], 2); ?></td>
                                            <td class="text-right">₹<?php echo number_format((float)$rh['InterestPaid'], 2); ?></td>
                                            <td class="text-right" style="font-weight: bold; color: #204d74;">
                                                ₹<?php echo number_format((float)$rh['TotalPaid'], 2); ?>
                                            </td>
                                            <td class="text-right">₹<?php echo number_format((float)$rh['BalanceLoanAmount'], 2); ?></td>
                                            <td class="text-center">
                                                <a href="aster-jewel-loan-receipt.php?receipt_id=<?php echo urlencode($rh['ReceiptId']); ?>&view_only=1" 
                                                   class="btn btn-xs btn-info" title="View Receipt Voucher">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <a href="aster-jewel-loan-receipt-pdf.php?receipt_id=<?php echo urlencode($rh['ReceiptId']); ?>" 
                                                   target="_blank" class="btn btn-xs btn-default" title="PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
    <!-- /#page-wrapper -->

    <?php include_once "footer.php"; ?>
</div>

<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>

<script>
// Format date helper: YYYY-MM-DD -> DD/MM/YYYY
function formatDateDisplay(dStr) {
    if (!dStr || dStr === '0000-00-00') return '';
    var parts = dStr.split('-');
    if (parts.length === 3) {
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    return dStr;
}

// Format numbers
function formatCurrency(val) {
    var n = parseFloat(val);
    if (isNaN(n)) return '0.00';
    return n.toFixed(2);
}

// Payment method fields toggler
function toggleReceiptPaymentMethod(method) {
    $('#receipt-cheque-box').hide();
    $('#receipt-rtgs-box').hide();
    $('#receipt-upi-box').hide();

    if (method === 'Cheque') {
        $('#receipt-cheque-box').slideDown(150);
    } else if (method === 'RTGS') {
        $('#receipt-rtgs-box').slideDown(150);
    } else if (method === 'UPI') {
        $('#receipt-upi-box').slideDown(150);
    }
}

// Dynamic Live Payment Allocation Breakdown
function updatePaymentAllocation() {
    var totalPaid = parseFloat($('#amount_paying').val()) || 0;
    var currentBalance = parseFloat($('#current_balance_loan').val()) || 0;
    var interestDue = parseFloat($('#display_interest').val()) || 0;
    var defaultIntDue = parseFloat($('#display_default_interest').val()) || 0;
    var txnType = $('#receipt_transaction_type_select').val() || $('#transaction_type').val() || 'INTEREST_PAYMENT';

    var interestPaid = 0;
    var defaultIntPaid = 0;
    var principalPaid = 0;
    var newBalance = currentBalance;

    if (txnType === 'JEWEL_RELEASE') {
        interestPaid = interestDue;
        defaultIntPaid = defaultIntDue;
        principalPaid = currentBalance;
        newBalance = 0;
    } else {
        // Priority 1: Default Interest
        defaultIntPaid = Math.min(defaultIntDue, totalPaid);
        var rem = Math.max(0, totalPaid - defaultIntPaid);

        // Priority 2: Accrued Interest
        interestPaid = Math.min(interestDue, rem);
        rem = Math.max(0, rem - interestPaid);

        // Priority 3: Principal Reduction
        principalPaid = Math.min(currentBalance, rem);
        newBalance = Math.max(0, currentBalance - principalPaid);
    }

    // Update Live Summary Box
    $('#alloc_interest_text').text('₹' + formatCurrency(interestPaid));
    $('#alloc_default_int_text').text('₹' + formatCurrency(defaultIntPaid));
    $('#alloc_principal_text').text('₹' + formatCurrency(principalPaid));
    $('#alloc_balance_text').text('₹' + formatCurrency(newBalance));

    // Update hidden field for server submission
    $('#hidden_principal_paid').val(principalPaid.toFixed(2));
}

// Transaction type switch (Interest only vs Jewel Release)
function onTxnTypeChange(txnType) {
    $('#transaction_type').val(txnType);
    $('#receipt_transaction_type_select').val(txnType);
    var bal = parseFloat($('#current_balance_loan').val()) || 0;
    var intAmt = parseFloat($('#display_interest').val()) || 0;
    var defInt = parseFloat($('#display_default_interest').val()) || 0;
    
    if (txnType === 'JEWEL_RELEASE') {
        var totalClosure = bal + intAmt + defInt;
        $('#amount_paying').val(totalClosure.toFixed(2)).prop('readonly', true);
    } else {
        // Interest Payment
        var interestOnly = intAmt + defInt;
        $('#amount_paying').prop('readonly', false);
        $('#amount_paying').val(interestOnly > 0 ? interestOnly.toFixed(2) : (bal > 0 ? bal.toFixed(2) : ''));
    }
    updatePaymentAllocation();
}

// Reset form for New Receipt
function resetReceiptForm() {
    $('#hidden_d_number').val('');
    $('#loan_detail_id').val('');
    $('#search_d_number').val('').focus();
    $('#display_d_number').val('');
    $('#display_interest_rate').val('');
    $('#display_customer_name').val('');
    $('#display_tot_days').val('');
    $('#display_balance_loan').val('0.00');
    $('#display_interest').val('0.00');
    $('#display_default_interest').val('0.00');
    $('#display_total_due').val('0.00');
    $('#display_grace_period').val('0');
    $('#amount_paying').val('').prop('readonly', false);
    $('#hidden_principal_paid').val('0.00');
    $('#alloc_interest_text').text('₹0.00');
    $('#alloc_default_int_text').text('₹0.00');
    $('#alloc_principal_text').text('₹0.00');
    $('#alloc_balance_text').text('₹0.00');

    // Reset loan dates
    $('#display_loan_date').val('');
    $('#display_monthly_due_date').val('');
    $('#display_closing_due_date').val('');
    $('#display_percentage').val('');
    $('#display_value_per_gram').val('0.00');
    $('#display_valuation_amount').val('0.00');
    $('#display_total_grams').val('0.000');
    $('#display_total_wastage').val('0.000');
    $('#display_permissible_per_gram').val('0.00');
    $('#display_permissible_amount').val('0.00');
    $('#display_loan_amount').val('0.00');

    // Customer fields
    $('#display_cust_occupation').val('');
    $('#display_cust_fathers_name').val('');
    $('#display_cust_home_name').val('');
    $('#display_cust_street').val('');
    $('#display_cust_village').val('');
    $('#display_cust_district').val('');
    $('#display_cust_mobile').val('');
    $('#display_cust_pincode').val('');

    // Ornaments table reset
    $('#ornaments-table-body').html(
        '<tr>' +
        '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="1" readonly></td>' +
        '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="-" readonly></td>' +
        '<td><input type="text" class="form-control form-control-readonly" value="No loan selected" readonly></td>' +
        '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>' +
        '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>' +
        '</tr>'
    );

    // Reset payment radio
    $('input[name="payment_method"][value="Cash"]').prop('checked', true);
    toggleReceiptPaymentMethod('Cash');

    $('#dnumber-search-alert').slideUp();
}

// Clear button
$('#btn-clear-dnumber').on('click', function() {
    $('#search_d_number').val('').focus();
});

// Perform Search via AJAX or direct lookup
function performDNumberSearch() {
    var dNum = $.trim($('#search_d_number').val());
    if (!dNum) {
        $('#dnumber-search-alert-msg').text('Please enter or select a D Number to search.');
        $('#dnumber-search-alert').slideDown();
        $('#search_d_number').focus();
        return;
    }

    $('#btn-search-dnumber').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Searching...');
    $('#dnumber-search-alert').slideUp();

    $.ajax({
        url: 'aster-jewel-loan-receipt.php',
        type: 'GET',
        data: { ajax_search_dnumber: dNum },
        dataType: 'json',
        success: function(resp) {
            $('#btn-search-dnumber').prop('disabled', false).html('<i class="fa fa-search"></i> Search');
            if (resp.status === 'success' && resp.data && resp.data.loan) {
                populateReceiptData(resp.data);
            } else {
                $('#dnumber-search-alert-msg').text(resp.message || 'No matching active loan record found for D Number ' + dNum);
                $('#dnumber-search-alert').slideDown();
            }
        },
        error: function(xhr, status, error) {
            $('#btn-search-dnumber').prop('disabled', false).html('<i class="fa fa-search"></i> Search');
            // If AJAX returns HTML due to redirect, fallback to standard GET navigation
            window.location.href = 'aster-jewel-loan-receipt.php?dnum=' + encodeURIComponent(dNum);
        }
    });
}

// Populate received loan data into all 3 sections
function populateReceiptData(data) {
    var l = data.loan;
    var c = data.customer || {};
    var calc = data.calculation || {};
    var items = data.items || [];

    // Hidden keys
    $('#loan_detail_id').val(l.LoanDetailId);
    $('#hidden_d_number').val(l.DNumber);
    $('#search_d_number').val(l.DNumber);
    $('#current_balance_loan').val(calc.principalBalance);
    $('#interest_months').val(calc.calculatedMonths || 1);
    $('#hidden_period_from').val(calc.lastCalculatedDate || l.LoanDate);
    var now = new Date();
    var nowStr = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
    $('#hidden_period_to').val(nowStr);

    // Section 1
    $('#display_d_number').val(l.DNumber);
    $('#display_interest_rate').val(formatCurrency(calc.interestRate) + '%');
    $('#display_customer_name').val(c.Name || '');
    $('#display_tot_days').val(calc.daysStatusLabel || calc.totDaysDisplay || '0');
    $('#display_balance_loan').val(formatCurrency(calc.principalBalance));
    $('#display_interest').val(formatCurrency(calc.accruedInterest));
    $('#display_default_interest').val(formatCurrency(calc.defaultInterest));
    $('#display_total_due').val(formatCurrency(calc.totalAmountDue));
    $('#display_grace_period').val(calc.gracePeriodDays || '0');

    // Default to Interest Payment mode and allocate
    $('#receipt_transaction_type_select').val('INTEREST_PAYMENT');
    $('#transaction_type').val('INTEREST_PAYMENT');
    onTxnTypeChange('INTEREST_PAYMENT');

    // Section 2
    $('#display_loan_date').val(formatDateDisplay(l.LoanDate));
    $('#display_monthly_due_date').val(formatDateDisplay(calc.monthlyDueDate || calc.dueDate));
    $('#display_closing_due_date').val(formatDateDisplay(calc.closingDueDate));
    $('#display_percentage').val(formatCurrency(calc.interestRate) + '%');
    $('#display_value_per_gram').val(formatCurrency(l.AmountPerGram));
    $('#display_valuation_amount').val(formatCurrency(l.TotalAmount));
    $('#display_permissible_per_gram').val(formatCurrency(l.MaxAmountPerGram));
    $('#display_permissible_amount').val(formatCurrency(l.AllowedAmount));
    $('#display_loan_amount').val(formatCurrency(l.LoanAmount));

    // Calculate sum gross & wastage
    var sumGross = 0;
    var sumWastage = 0;
    var itemsHtml = '';
    if (items.length > 0) {
        $.each(items, function(idx, it) {
            var g = parseFloat(it.Gram) || 0;
            var w = parseFloat(it.Wastage) || 0;
            sumGross += g;
            sumWastage += w;
            itemsHtml += '<tr>' +
                '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="' + (idx + 1) + '" readonly></td>' +
                '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="' + it.NoOfItems + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-readonly" value="' + (it.ItemName || '') + '" readonly></td>' +
                '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="' + g.toFixed(3) + '" readonly></td>' +
                '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="' + w.toFixed(3) + '" readonly></td>' +
                '</tr>';
        });
    } else {
        itemsHtml = '<tr>' +
            '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="1" readonly></td>' +
            '<td class="text-center"><input type="text" class="form-control text-center form-control-readonly" value="-" readonly></td>' +
            '<td><input type="text" class="form-control form-control-readonly" value="No ornaments listed" readonly></td>' +
            '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>' +
            '<td class="text-right"><input type="text" class="form-control text-right form-control-readonly" value="0.000" readonly></td>' +
            '</tr>';
    }
    $('#ornaments-table-body').html(itemsHtml);
    $('#display_total_grams').val(sumGross > 0 ? sumGross.toFixed(3) : (parseFloat(l.TotalNetWeight) || 0).toFixed(3));
    $('#display_total_wastage').val(sumWastage.toFixed(3));

    // Section 3: Customer Details
    $('#display_cust_occupation').val(c.Job || c.Occupation || '');
    $('#display_cust_fathers_name').val(c.FathersName || c.SDWName || '');
    $('#display_cust_home_name').val(c.Address || c.PermenantAddress || '');
    $('#display_cust_street').val(c.Address || c.PermenantAddress || '');
    $('#display_cust_village').val(c.Address || c.PermenantAddress || '');
    $('#display_cust_district').val(c.DistrictName || c.District || '');
    $('#display_cust_mobile').val(c.MobileNumber || c.MobileNo || '');
    $('#display_cust_pincode').val(c.Pincode || c.PinCode || '');
}

// Bind search click & Enter key
$('#btn-search-dnumber').on('click', performDNumberSearch);
$('#search_d_number').on('keypress', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        performDNumberSearch();
    }
});

// Form submission validation
$('#jewel-loan-receipt-form').on('submit', function(e) {
    var loanId = $('#loan_detail_id').val();
    var dNum = $('#hidden_d_number').val();
    var amt = parseFloat($('#amount_paying').val()) || 0;

    if (!loanId || !dNum) {
        e.preventDefault();
        alert('Please search and select a valid D Number before saving.');
        $('#search_d_number').focus();
        return false;
    }

    if (amt <= 0) {
        e.preventDefault();
        alert('Please enter a valid payment Amount.');
        $('#amount_paying').focus();
        return false;
    }

    // Disable button to prevent duplicate submissions
    $('#btn-save-receipt').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
});

// Bind amount paying live allocation
$('#amount_paying').on('input keyup change', updatePaymentAllocation);

// Run initial allocation on page load
$(document).ready(function() {
    updatePaymentAllocation();
});
</script>
</body>
</html>
