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
require_once 'controller/JewelLoanReceiptListController.php';

$controller = new JewelLoanReceiptListController($conn);
$data = $controller->handleRequest();

$filters   = $data['filters'];
$receipts  = $data['receipts'];
$summary   = $data['summary'];
$todayDate = $data['todayDate'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Jewel Loan Receipt List</title>

    <!-- Standard Aster Platform CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <link href="css/datatablelatest.css" rel="stylesheet">

    <style>
        .metric-card {
            background: #fff;
            border-radius: 4px;
            border: 1px solid #e1e4e8;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border-left: 4px solid #337ab7;
        }
        .metric-card.success-card {
            border-left-color: #5cb85c;
        }
        .metric-card.info-card {
            border-left-color: #5bc0de;
        }
        .metric-card.warning-card {
            border-left-color: #f0ad4e;
        }
        .metric-number {
            font-size: 22px;
            font-weight: bold;
            margin-top: 5px;
            color: #2c3e50;
        }
        .metric-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            font-weight: 600;
        }
        .badge-type-payment {
            background-color: #337ab7;
            color: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-type-principal {
            background-color: #5bc0de;
            color: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-type-release {
            background-color: #5cb85c;
            color: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-type-creation {
            background-color: #f0ad4e;
            color: #fff;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .table-receipts th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        /* Voucher Pop-up Modal Styling */
        #receiptViewModal {
            z-index: 100050 !important;
        }
        .modal-backdrop {
            z-index: 100040 !important;
        }
        .voucher-card {
            background: #ffffff;
            border: 2px solid #204d74;
            border-radius: 6px;
            padding: 20px 25px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            max-width: 860px;
            margin-left: auto;
            margin-right: auto;
        }
        .voucher-header {
            text-align: center;
            border-bottom: 2px solid #204d74;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .voucher-org-title {
            font-size: 20px;
            font-weight: 800;
            color: #204d74;
            margin-bottom: 3px;
        }
        .voucher-org-sub {
            font-size: 12px;
            color: #555555;
            margin-bottom: 8px;
        }
        .voucher-badge {
            display: inline-block;
            background: #204d74;
            color: #ffffff;
            font-weight: bold;
            font-size: 13px;
            padding: 3px 15px;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .voucher-table-meta {
            width: 100%;
            margin-bottom: 12px;
        }
        .voucher-table-meta td {
            padding: 4px 6px;
            font-size: 13px;
        }
        .voucher-table-breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .voucher-table-breakdown th {
            background: #f0f4f8;
            color: #204d74;
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 12px;
        }
        .voucher-table-breakdown td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 12px;
        }
        .voucher-signatures {
            margin-top: 30px;
            padding-top: 8px;
        }
        .signature-col {
            text-align: center;
            border-top: 1px dashed #666;
            padding-top: 5px;
            font-weight: 600;
            font-size: 12px;
            color: #444;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once "header.php"; ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header" style="margin-top: 15px;">
                    <i class="fa fa-list-alt text-primary"></i> Receipt List
                    
                </h2>
                <hr class="hr-background">
            </div>
        </div>

        <!-- Summary Metrics Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="metric-card">
                    <div class="metric-label"><i class="fa fa-file-text-o"></i> Total Receipts</div>
                    <div class="metric-number"><?= number_format($summary['totalCount']) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="metric-card success-card">
                    <div class="metric-label"><i class="fa fa-money"></i> Total Collected</div>
                    <div class="metric-number text-success">₹<?= number_format($summary['totalPaid'], 2) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="metric-card info-card">
                    <div class="metric-label"><i class="fa fa-arrow-circle-down"></i> Principal Recovered</div>
                    <div class="metric-number text-info">₹<?= number_format($summary['totalPrincipal'], 2) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="metric-card warning-card">
                    <div class="metric-label"><i class="fa fa-percent"></i> Interest Collected</div>
                    <div class="metric-number text-warning">₹<?= number_format($summary['totalInterest'], 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-filter"></i> <strong>Receipt Filter Settings</strong>
                        <span class="text-muted pull-right" style="font-size: 12px;">Default displays all records</span>
                    </div>
                    <div class="panel-body">
                        <form method="get" action="aster-jewel-loan-receipt-list.php">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="query">Keyword / D-Number / Name:</label>
                                        <input type="text" name="query" id="query" class="form-control" placeholder="Search keyword..." value="<?= htmlspecialchars($filters['query']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="receipt_no">Receipt No:</label>
                                        <input type="text" name="receipt_no" id="receipt_no" class="form-control" placeholder="e.g. JLR-2026-0001" value="<?= htmlspecialchars($filters['receipt_no']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="transaction_type">Type:</label>
                                        <select name="transaction_type" id="transaction_type" class="form-control">
                                            <option value="">-- All Types --</option>
                                            <option value="INTEREST_PAYMENT" <?= $filters['transaction_type'] === 'INTEREST_PAYMENT' ? 'selected' : '' ?>>INTEREST_PAYMENT</option>
                                            <option value="PRINCIPAL_PAYMENT" <?= $filters['transaction_type'] === 'PRINCIPAL_PAYMENT' ? 'selected' : '' ?>>PRINCIPAL_PAYMENT</option>
                                            <option value="JEWEL_RELEASE" <?= $filters['transaction_type'] === 'JEWEL_RELEASE' ? 'selected' : '' ?>>JEWEL_RELEASE</option>
                                            <option value="LOAN_CREATION" <?= $filters['transaction_type'] === 'LOAN_CREATION' ? 'selected' : '' ?>>LOAN_CREATION</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method:</label>
                                        <select name="payment_method" id="payment_method" class="form-control">
                                            <option value="">-- All Methods --</option>
                                            <option value="Cash" <?= $filters['payment_method'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                                            <option value="Cheque" <?= $filters['payment_method'] === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                                            <option value="RTGS" <?= $filters['payment_method'] === 'RTGS' ? 'selected' : '' ?>>RTGS</option>
                                            <option value="UPI" <?= $filters['payment_method'] === 'UPI' ? 'selected' : '' ?>>UPI</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="date_from">Date From:</label>
                                                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="form-group">
                                                <label for="date_to">Date To:</label>
                                                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Apply Filters</button>
                                    <a href="aster-jewel-loan-receipt-list.php" class="btn btn-default" style="margin-left: 5px;"><i class="fa fa-refresh"></i> Reset (Show All)</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipts Data Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-table"></i> <strong>Receipts Table</strong>
                        <span class="badge pull-right"><?= count($receipts) ?> record(s)</span>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="receipt-list-table" class="table table-striped table-bordered table-hover table-receipts">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Receipt No</th>
                                        <th>Receipt Date</th>
                                        <th>D-Number</th>
                                        <th>Customer Name</th>
                                        <th>Mobile</th>
                                        <th>Type</th>
                                        <th>Method</th>
                                        <th class="text-right">Balance Amount (₹)</th>
                                        <th class="text-right">Total Paid (₹)</th>
                                        <th class="text-right">Principal (₹)</th>
                                        <th class="text-right">Interest (₹)</th>
                                        <th class="text-right">Current Bal (₹)</th>
                                        <th class="text-center" style="width: 130px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($receipts)): ?>
                                        <?php foreach ($receipts as $idx => $r): ?>
                                            <tr>
                                                <td><?= $idx + 1 ?></td>
                                                <td><strong><?= htmlspecialchars($r['ReceiptNo']) ?></strong></td>
                                                <td><?= date('d/m/Y', strtotime($r['ReceiptDate'])) ?></td>
                                                <td><?= htmlspecialchars($r['DNumber']) ?></td>
                                                <td><?= htmlspecialchars($r['CustomerName']) ?></td>
                                                <td><?= htmlspecialchars($r['MobileNumber']) ?></td>
                                                <td>
                                                    <?php if ($r['TransactionType'] === 'INTEREST_PAYMENT'): ?>
                                                        <span class="badge-type-payment">INTEREST</span>
                                                    <?php elseif ($r['TransactionType'] === 'PRINCIPAL_PAYMENT'): ?>
                                                        <span class="badge-type-principal">PRINCIPAL</span>
                                                    <?php elseif ($r['TransactionType'] === 'JEWEL_RELEASE'): ?>
                                                        <span class="badge-type-release">RELEASE</span>
                                                    <?php else: ?>
                                                        <span class="badge-type-creation">CREATION</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($r['PaymentMethod']) ?></td>
                                                <td class="text-right">₹<?= number_format($r['BalanceBefore'] ?? (($r['TransactionType'] === 'LOAN_CREATION') ? (float)$r['BalanceLoanAmount'] : ((float)$r['BalanceLoanAmount'] + (float)$r['PrincipalPaid'])), 2) ?></td>
                                                <td class="text-right font-weight-bold">₹<?= number_format($r['TotalPaid'], 2) ?></td>
                                                <td class="text-right">₹<?= number_format($r['PrincipalPaid'], 2) ?></td>
                                                <td class="text-right">₹<?= number_format($r['InterestPaid'], 2) ?></td>
                                                <td class="text-right">₹<?= number_format($r['BalanceLoanAmount'], 2) ?></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-default btn-xs btn-view-receipt-popup" data-receipt-id="<?= $r['ReceiptId'] ?>" data-receipt-no="<?= htmlspecialchars($r['ReceiptNo']) ?>" title="View Receipt Voucher (Popup)">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <a href="aster-jewel-loan-receipt-pdf.php?receipt_id=<?= $r['ReceiptId'] ?>" class="btn btn-danger btn-xs" title="Download PDF" target="_blank">
                                                        <i class="fa fa-file-pdf-o"></i>
                                                    </a>
                                                    <?php if ($r['ReceiptDate'] === $todayDate && $r['TransactionType'] !== 'LOAN_CREATION'): ?>
                                                        <a href="aster-jewel-loan-delete-receipt.php?inspect_id=<?= $r['ReceiptId'] ?>" class="btn btn-warning btn-xs" title="Delete Today's Receipt">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="14" class="text-center text-muted" style="padding: 20px;">
                                                <i class="fa fa-info-circle"></i> No receipts found matching the filter criteria.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /#page-wrapper -->

    <?php include_once "footer.php"; ?>
</div>
<!-- /#wrapper -->

<!-- ============================================================== -->
<!-- Receipt Voucher Modal Popup (Placed outside #wrapper)           -->
<!-- ============================================================== -->
<div class="modal fade" id="receiptViewModal" tabindex="-1" role="dialog" aria-labelledby="receiptViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 900px; margin: 30px auto;">
        <div class="modal-content" style="border-radius: 6px; box-shadow: 0 5px 25px rgba(0,0,0,0.4);">
            <div class="modal-header" style="background-color: #f7f9fa; border-bottom: 1px solid #e1e4e8; padding: 12px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 26px; line-height: 1; margin-top: -3px;">&times;</button>
                <div class="pull-right" style="margin-right: 15px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-print-modal-receipt" style="margin-right: 5px;">
                        <i class="fa fa-print"></i> Print Voucher
                    </button>
                    <a href="#" target="_blank" class="btn btn-danger btn-sm" id="btn-pdf-modal-receipt" style="margin-right: 5px;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </a>
                </div>
                <h4 class="modal-title" id="receiptViewModalLabel" style="font-weight: bold; color: #337ab7; margin: 0;">
                    <i class="fa fa-ticket"></i> Receipt Voucher - <span id="modal-receipt-no-display"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 15px 20px; background-color: #f8f9fa; max-height: calc(85vh - 120px); overflow-y: auto;">
                <div id="receipt-modal-loading" class="text-center" style="padding: 50px 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p style="margin-top: 15px; font-weight: 600; color: #555;">Loading Receipt Voucher Details...</p>
                </div>
                <div id="receipt-modal-error" class="alert alert-danger" style="display: none;"></div>
                <div id="receipt-modal-body-content" style="display: none;"></div>
            </div>
            <div class="modal-footer" style="padding: 10px 20px; background-color: #f7f9fa; border-top: 1px solid #e1e4e8;">
                <span class="text-muted pull-left" style="font-size: 12px; margin-top: 6px;">
                    <i class="fa fa-info-circle"></i> Review the receipt details above. Click Close to return to the receipt list.
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Standard Aster Platform Scripts -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#receipt-list-table').DataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "pageLength": 25,
        "order": [[2, "desc"]]
    });

    // Open receipt voucher in modal popup
    $(document).on('click', '.btn-view-receipt-popup', function(e) {
        e.preventDefault();
        var receiptId = $(this).attr('data-receipt-id') || $(this).data('receipt-id');
        var receiptNo = $(this).attr('data-receipt-no') || $(this).data('receipt-no');

        if (!receiptId) {
            alert('Receipt ID not found.');
            return;
        }

        $('#modal-receipt-no-display').text(receiptNo);
        $('#btn-pdf-modal-receipt').attr('href', 'aster-jewel-loan-receipt-pdf.php?receipt_id=' + encodeURIComponent(receiptId));

        $('#receipt-modal-loading').show();
        $('#receipt-modal-error').hide().html('');
        $('#receipt-modal-body-content').hide().html('');

        // Show modal immediately
        $('#receiptViewModal').modal('show');

        // Fetch voucher HTML directly via AJAX
        $.ajax({
            url: 'aster-jewel-loan-receipt-list.php',
            type: 'GET',
            dataType: 'json',
            data: { ajax_receipt_voucher: receiptId },
            success: function(resp) {
                $('#receipt-modal-loading').hide();
                if (resp && resp.status === 'success' && resp.html) {
                    if (resp.receipt_no) {
                        $('#modal-receipt-no-display').text(resp.receipt_no);
                    }
                    $('#receipt-modal-body-content').html(resp.html).show();
                } else {
                    var errMsg = (resp && resp.message) ? resp.message : 'Failed to load receipt voucher.';
                    $('#receipt-modal-error').html('<i class="fa fa-exclamation-circle"></i> ' + errMsg).show();
                }
            },
            error: function(xhr, status, error) {
                $('#receipt-modal-loading').hide();
                $('#receipt-modal-error').html('<i class="fa fa-exclamation-circle"></i> Server communication error. Please try again.').show();
            }
        });
    });

    // Print voucher directly from modal content
    $('#btn-print-modal-receipt').on('click', function() {
        var printContents = document.getElementById('receipt-modal-body-content').innerHTML;
        if (!printContents) {
            alert('Receipt content is not loaded yet.');
            return;
        }
        var printWindow = window.open('', '_blank', 'height=750,width=950');
        if (!printWindow) {
            window.print();
            return;
        }
        printWindow.document.write('<!DOCTYPE html><html><head><title>Print Receipt Voucher</title>');
        printWindow.document.write('<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; padding: 20px; background: #fff; }');
        printWindow.document.write('.voucher-card { background: #fff; border: 2px solid #204d74; border-radius: 6px; padding: 20px 25px; max-width: 850px; margin: 0 auto; }');
        printWindow.document.write('.voucher-header { text-align: center; border-bottom: 2px solid #204d74; padding-bottom: 12px; margin-bottom: 15px; }');
        printWindow.document.write('.voucher-org-title { font-size: 20px; font-weight: 800; color: #204d74; margin-bottom: 3px; }');
        printWindow.document.write('.voucher-org-sub { font-size: 12px; color: #555; margin-bottom: 8px; }');
        printWindow.document.write('.voucher-badge { display: inline-block; background: #204d74; color: #fff; font-weight: bold; font-size: 13px; padding: 3px 15px; border-radius: 3px; }');
        printWindow.document.write('.voucher-table-meta, .voucher-table-breakdown { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; }');
        printWindow.document.write('.voucher-table-meta td { padding: 5px 8px; }');
        printWindow.document.write('.voucher-table-breakdown th, .voucher-table-breakdown td { border: 1px solid #ccc; padding: 6px 10px; }');
        printWindow.document.write('.voucher-table-breakdown th { background: #f0f4f8; color: #204d74; font-weight: bold; }');
        printWindow.document.write('.voucher-signatures { margin-top: 35px; }');
        printWindow.document.write('.signature-col { text-align: center; border-top: 1px dashed #666; padding-top: 5px; font-weight: 600; font-size: 12px; }');
        printWindow.document.write('@media print { body { padding: 0; } .voucher-card { border: 1px solid #000; box-shadow: none; width: 100%; max-width: 100%; } }');
        printWindow.document.write('</style></head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 300);
    });

    // Reset modal on close
    $('#receiptViewModal').on('hidden.bs.modal', function() {
        $('#receipt-modal-body-content').html('').hide();
        $('#receipt-modal-error').html('').hide();
        $('#receipt-modal-loading').show();
    });
});
</script>
</body>
</html>

