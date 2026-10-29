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
                                                    <a href="aster-jewel-loan-receipt.php?receipt_id=<?= $r['ReceiptId'] ?>&view_only=1" class="btn btn-default btn-xs" title="View / Print Voucher" target="_blank">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
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
});
</script>
</body>
</html>

