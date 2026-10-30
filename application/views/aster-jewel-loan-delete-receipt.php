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
require_once 'controller/JewelLoanDeleteReceiptController.php';

$controller = new JewelLoanDeleteReceiptController($conn);
$data = $controller->handleRequest();

$success          = $data['success'];
$error            = $data['error'];
$selectedReceipt  = $data['selectedReceipt'];
$searchTerm       = $data['searchTerm'];
$receipts         = $data['receipts'];
$todayDateDisplay = $data['todayDateDisplay'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Delete Jewel Loan Receipt</title>

    <!-- Standard Aster Platform CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <link href="css/datatablelatest.css" rel="stylesheet">

    <style>
        .form-control-readonly {
            background-color: #eee !important;
            cursor: not-allowed;
            font-weight: bold;
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
        .badge-type-principal {
            background-color: #5bc0de;
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
        .card-preview {
            background: #fdfdfd;
            border: 1px solid #ddd;
            border-left: 4px solid #d9534f;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .preview-title {
            font-size: 16px;
            font-weight: bold;
            color: #d9534f;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .preview-label {
            font-weight: 600;
            color: #555;
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
                    <i class="fa fa-trash-o text-danger"></i> Delete Receipt
                </h2>
                <hr class="hr-background">
            </div>
        </div>

        <!-- Flash Notifications -->
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <i class="fa fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <i class="fa fa-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row">
            <div class="col-md-12">
                <form method="get" action="aster-jewel-loan-delete-receipt.php">
                    <fieldset class="feild-set">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="text" name="receipt_date" id="receipt_date" class="form-control form-control-readonly text-center" 
                                           value="<?= htmlspecialchars($todayDateDisplay); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search_term">Receipt Number or D-Number</label>
                                    <input type="text" name="search_term" id="search_term" class="form-control" 
                                           placeholder="Enter Receipt No or D-Number (Optional)" 
                                           value="<?= htmlspecialchars($searchTerm) ?>" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3" style="margin-top: 24px;">
                                <button class="btn btn-md btn-info" type="submit" id="search-button">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <a href="aster-jewel-loan-delete-receipt.php" class="btn btn-md btn-default" style="margin-left: 5px;">
                                    <i class="fa fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <br>

        <!-- Selected Receipt Inspection / Confirmation Box -->
        <?php if (!empty($selectedReceipt)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card-preview">
                    <h3 class="preview-title">
                        <i class="fa fa-exclamation-circle"></i> Receipt Deletion Confirmation & Impact Analysis
                    </h3>
                    <div class="row">
                        <div class="col-md-3">
                            <p><span class="preview-label">Receipt No:</span> <strong class="text-primary"><?= htmlspecialchars($selectedReceipt['ReceiptNo']) ?></strong></p>
                            <p><span class="preview-label">Receipt Date:</span> <?= date('d/m/Y', strtotime($selectedReceipt['ReceiptDate'])) ?></p>
                            <p><span class="preview-label">Transaction Type:</span> 
                                <?php if ($selectedReceipt['TransactionType'] === 'INTEREST_PAYMENT'): ?>
                                    <span class="badge-type-payment">INTEREST PAYMENT</span>
                                <?php elseif ($selectedReceipt['TransactionType'] === 'PRINCIPAL_PAYMENT'): ?>
                                    <span class="badge-type-principal">PRINCIPAL PAYMENT</span>
                                <?php elseif ($selectedReceipt['TransactionType'] === 'JEWEL_RELEASE'): ?>
                                    <span class="badge-type-release">JEWEL RELEASE</span>
                                <?php else: ?>
                                    <span class="badge-type-creation">LOAN CREATION</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="preview-label">D-Number:</span> <strong><?= htmlspecialchars($selectedReceipt['DNumber']) ?></strong></p>
                            <p><span class="preview-label">Customer Name:</span> <?= htmlspecialchars($selectedReceipt['CustomerName']) ?></p>
                            <p><span class="preview-label">Mobile Number:</span> <?= htmlspecialchars($selectedReceipt['MobileNumber']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="preview-label">Balance Before Receipt:</span> <strong class="text-info">₹<?= number_format($selectedReceipt['BalanceBefore'] ?? (($selectedReceipt['TransactionType'] === 'LOAN_CREATION') ? (float)$selectedReceipt['BalanceLoanAmount'] : ((float)$selectedReceipt['BalanceLoanAmount'] + (float)$selectedReceipt['PrincipalPaid'])), 2) ?></strong></p>
                            <p><span class="preview-label">Total Amount Paid:</span> <strong class="text-danger">₹<?= number_format($selectedReceipt['TotalPaid'], 2) ?></strong></p>
                            <p><span class="preview-label">Principal Paid:</span> ₹<?= number_format($selectedReceipt['PrincipalPaid'], 2) ?></p>
                            <p><span class="preview-label">Interest Paid:</span> ₹<?= number_format($selectedReceipt['InterestPaid'], 2) ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><span class="preview-label">Current Loan Balance:</span> ₹<?= number_format($selectedReceipt['CurrentLoanBalance'], 2) ?></p>
                            <p><span class="preview-label">Projected Restored Balance:</span> <strong class="text-success">₹<?= number_format($selectedReceipt['ProjectedBalance'], 2) ?></strong></p>
                            <p><span class="preview-label">Payment Method:</span> <?= htmlspecialchars($selectedReceipt['PaymentMethod']) ?></p>
                        </div>
                    </div>

                    <div class="alert alert-warning" style="margin-top: 10px; margin-bottom: 15px;">
                        <i class="fa fa-info-circle"></i>
                        <?php if ($selectedReceipt['TransactionType'] === 'JEWEL_RELEASE'): ?>
                            Deleting this Jewel Release receipt will <strong>re-open the loan account</strong> and restore its outstanding principal balance back to <strong>₹<?= number_format($selectedReceipt['ProjectedBalance'], 2) ?></strong>.
                        <?php elseif ($selectedReceipt['TransactionType'] === 'PRINCIPAL_PAYMENT'): ?>
                            Deleting this Principal Payment receipt will reverse the principal payment of <strong>₹<?= number_format($selectedReceipt['PrincipalPaid'], 2) ?></strong> and restore the outstanding loan balance back to <strong>₹<?= number_format($selectedReceipt['ProjectedBalance'], 2) ?></strong>.
                        <?php elseif ($selectedReceipt['TransactionType'] === 'INTEREST_PAYMENT' && $selectedReceipt['PrincipalPaid'] > 0): ?>
                            Deleting this receipt will reverse the principal payment of <strong>₹<?= number_format($selectedReceipt['PrincipalPaid'], 2) ?></strong>, restoring the loan balance from <strong>₹<?= number_format($selectedReceipt['CurrentLoanBalance'], 2) ?></strong> to <strong>₹<?= number_format($selectedReceipt['ProjectedBalance'], 2) ?></strong>.
                        <?php elseif ($selectedReceipt['TransactionType'] === 'LOAN_CREATION'): ?>
                            <strong>Notice:</strong> This is the loan's initial creation record. Deleting loan creation receipts is restricted to preserve loan inception integrity.
                        <?php else: ?>
                            Deleting this interest receipt will remove the payment transaction from the ledger.
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if ($selectedReceipt['TransactionType'] === 'LOAN_CREATION'): ?>
                            <button type="button" class="btn btn-danger" disabled title="Initial disbursement receipt cannot be deleted">
                                <i class="fa fa-ban"></i> Cannot Delete Loan Creation Receipt
                            </button>
                            <span class="text-muted" style="margin-left: 10px;">To remove this loan, please delete the entire loan account.</span>
                        <?php else: ?>
                            <form method="post" action="aster-jewel-loan-delete-receipt.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to permanently delete receipt <?= htmlspecialchars($selectedReceipt['ReceiptNo']) ?>?\n\nLoan balance will be restored to ₹<?= number_format($selectedReceipt['ProjectedBalance'], 2) ?>.\nThis action cannot be undone.');">
                                <input type="hidden" name="receipt_id" value="<?= $selectedReceipt['ReceiptId'] ?>">
                                <button type="submit" name="confirm_delete_receipt" class="btn btn-danger btn-md">
                                    <i class="fa fa-trash"></i> Delete Receipt
                                </button>
                            </form>
                            <a href="aster-jewel-loan-delete-receipt.php" class="btn btn-default btn-md" style="margin-left: 10px;">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Results Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-list"></i> <strong>Today's Receipts (<?= htmlspecialchars($todayDateDisplay) ?>)</strong>
                        <span class="badge pull-right"><?= count($receipts) ?> record(s)</span>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="receipts-table" class="table table-striped table-bordered table-hover table-receipts">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Receipt No</th>
                                        <th>Date</th>
                                        <th>D-Number</th>
                                        <th>Customer Name</th>
                                        <th>Type</th>
                                        <th>Method</th>
                                        <th class="text-right">Balance Amount (₹)</th>
                                        <th class="text-right">Total Paid (₹)</th>
                                        <th class="text-right">Principal (₹)</th>
                                        <th class="text-right">Interest (₹)</th>
                                        <th class="text-right">Current Bal (₹)</th>
                                        <th class="text-center" style="width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($receipts)): ?>
                                        <?php foreach ($receipts as $idx => $r): ?>
                                            <tr>
                                                <td><?= $idx + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($r['ReceiptNo']) ?></strong>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($r['ReceiptDate'])) ?></td>
                                                <td><?= htmlspecialchars($r['DNumber']) ?></td>
                                                <td><?= htmlspecialchars($r['CustomerName']) ?></td>
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
                                                    <a href="aster-jewel-loan-delete-receipt.php?inspect_id=<?= $r['ReceiptId'] ?>" class="btn btn-info btn-xs" title="Inspect & Delete">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <?php if ($r['TransactionType'] !== 'LOAN_CREATION'): ?>
                                                        <a href="aster-jewel-loan-delete-receipt.php?action=delete&receipt_id=<?= $r['ReceiptId'] ?>" 
                                                           class="btn btn-danger btn-xs" 
                                                           onclick="return confirm('Are you sure you want to delete receipt <?= htmlspecialchars($r['ReceiptNo']) ?> for D-Number <?= htmlspecialchars($r['DNumber']) ?>?\n\nThis will restore the loan balance.');"
                                                           title="Delete">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-default btn-xs" disabled title="Initial loan creation cannot be deleted here">
                                                            <i class="fa fa-lock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="13" class="text-center text-muted" style="padding: 20px;">
                                                <i class="fa fa-info-circle"></i> No receipts found for today (<?= htmlspecialchars($todayDateDisplay) ?>). Only receipts generated on the current day can be deleted.
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
    $('#receipts-table').DataTable({
        "paging": true,
        "ordering": true,
        "info": true,
        "pageLength": 25,
        "order": [[0, "asc"]]
    });
});
</script>
</body>
</html>
