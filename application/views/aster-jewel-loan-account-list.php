<?php
session_start();
// Secure authentication guard
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once '../settings/db.php';
require_once 'controller/JewelLoanAccountListController.php';

$controller = new JewelLoanAccountListController($conn);
$data = $controller->handleRequest();

$selectedDate      = $data['selectedDate'];
$dbDate            = $data['dbDate'];
$isSearchTriggered = $data['isSearchTriggered'];
$loans             = $data['loans'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Account List</title>

    <!-- Standard Aster Platform CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">

    <style>
        .account-table tbody tr {
            cursor: pointer;
        }
        .account-table tbody tr:hover {
            background-color: #f5f5f5 !important;
        }
        .badge-active {
            background-color: #5cb85c;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .badge-inactive {
            background-color: #777777;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 3px;
        }
        /* Modal Styles */
        .modal-header-custom {
            background-color: #337ab7;
            color: #ffffff;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            padding: 12px 15px;
        }
        .modal-header-custom .modal-title {
            color: #ffffff;
            font-size: 17px;
            font-weight: bold;
        }
        .modal-header-custom .close {
            color: #ffffff;
            opacity: 0.9;
        }
        .modal-meta-box {
            background-color: #fcfcfc;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 15px;
        }
        .modal-meta-label {
            font-size: 11px;
            font-weight: bold;
            color: #777;
            text-transform: uppercase;
        }
        .modal-meta-val {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .txn-type-label {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Account List</h2>
                <hr class="hr-background">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption">
                    <span class="mandatory">*</span> Fields are mandatory
                </h6>
            </div>
        </div>

        <!-- Filter Form following Scheme Master / Appraiser Master Style -->
        <form method="GET" action="aster-jewel-loan-account-list.php" id="accountListFilterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date <span class="mandatory">*</span></label>
                        <input type="text" 
                               name="date" 
                               id="current-date" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($selectedDate); ?>" 
                               placeholder="DD/MM/YYYY" 
                               readonly
                               required 
                               style="cursor: pointer; background-color: #ffffff;">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group" style="margin-top: 25px;">
                        <button type="submit" name="show_all" value="1" class="btn btn-primary" style="padding: 6px 18px;">
                            Show All
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <hr>

        <!-- Loan Accounts Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="designation-details" class="table table-striped table-bordered table-hover account-table">
                        <thead>
                            <tr class="header-row">
                                <th style="width: 50px;">SlNo</th>
                                <th>Date</th>
                                <th>D Number</th>
                                <th>Name</th>
                                <th>Default %</th>
                                <th>Loan Amount</th>
                                <th>Balance Amount</th>
                                <th>Due Date</th>
                                <th style="width: 80px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($loans)): ?>
                                <?php foreach ($loans as $idx => $loan): ?>
                                    <tr class="clickable-loan-row" 
                                        data-id="<?php echo (int)$loan['LoanDetailId']; ?>" 
                                        data-dnum="<?php echo htmlspecialchars($loan['DNumber']); ?>">
                                        <td class="text-center"><?php echo $idx + 1; ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($loan['LoanDateFormatted']); ?></td>
                                        <td class="text-center" style="font-weight: bold; color: #337ab7;">
                                            <?php echo htmlspecialchars($loan['DNumber']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($loan['CustomerName']); ?></td>
                                        <td class="text-center"><?php echo number_format((float)$loan['InterestRate'], 0); ?>%</td>
                                        <td class="text-right"><?php echo number_format((float)$loan['LoanAmount'], 2); ?></td>
                                        <td class="text-right"><?php echo number_format((float)$loan['BalanceLoanAmount'], 2); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($loan['DueDateFormatted']); ?></td>
                                        <td class="text-center"><?php echo $loan['StatusBadge']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center" style="padding: 20px; color: #777;">
                                        No records found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "footer.php"; ?>
</div>

<!-- ======================================================== -->
<!-- TRANSACTION HISTORY POP-UP MODAL                          -->
<!-- ======================================================== -->
<div class="modal fade" id="modalTransactionHistory" tabindex="-1" role="dialog" aria-labelledby="modalTxnTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalTxnTitle">Transaction History</h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <!-- Loading Spinner -->
                <div id="modalLoading" class="text-center" style="padding: 30px 0; display: none;">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="text-muted" style="margin-top: 8px;">Loading...</p>
                </div>

                <!-- Content Container -->
                <div id="modalContentContainer">
                    <!-- Loan Account Overview Details from table -->
                    <div class="modal-meta-box">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Date</div>
                                <div class="modal-meta-val" id="modalLoanDate">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">D Number</div>
                                <div class="modal-meta-val" id="modalDNumber" style="color: #337ab7;">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Name</div>
                                <div class="modal-meta-val" id="modalCustomerName">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Default %</div>
                                <div class="modal-meta-val" id="modalInterestRate">-</div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Loan Amount</div>
                                <div class="modal-meta-val" id="modalLoanAmount">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Balance Amount</div>
                                <div class="modal-meta-val" id="modalBalanceAmount">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Due Date</div>
                                <div class="modal-meta-val" id="modalDueDate">-</div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="modal-meta-label">Status</div>
                                <div class="modal-meta-val" id="modalStatus">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction History Records Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="modalTxnTable" style="margin-bottom: 0;">
                            <thead>
                                <tr class="header-row">
                                    <th style="width: 45px;">SlNo</th>
                                    <th>Receipt No</th>
                                    <th>Date</th>
                                    <th>Transaction Type</th>
                                    <th>Payment Mode</th>
                                    <th>Principal Paid</th>
                                    <th>Interest Paid</th>
                                    <th>Total Paid</th>
                                    <th>Balance Loan Amount</th>
                                    <th style="width: 60px;">Print</th>
                                </tr>
                            </thead>
                            <tbody id="modalTxnBody">
                                <!-- Populated dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 15px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Standard Aster Platform Scripts -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize standard Aster DataTable
    if ($('#designation-details').length && <?php echo (!empty($loans) ? 'true' : 'false'); ?>) {
        $('#designation-details').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "pageLength": 10
        });
    }

    // Format money helper
    function formatMoney(num) {
        var n = parseFloat(num);
        if (isNaN(n)) return '0.00';
        return n.toFixed(2);
    }

    // Row Click handler to show Transaction History Modal
    $(document).on('click', '.clickable-loan-row', function(e) {
        if ($(e.target).closest('a, button').length) {
            return;
        }

        var loanDetailId = $(this).data('id');
        if (!loanDetailId) return;

        $('#modalLoading').show();
        $('#modalContentContainer').hide();
        $('#modalTransactionHistory').modal('show');

        $.ajax({
            url: 'aster-jewel-loan-account-list.php',
            type: 'GET',
            dataType: 'json',
            data: {
                ajax_txn_history: loanDetailId
            },
            success: function(resp) {
                $('#modalLoading').hide();
                if (resp && resp.status === 'success') {
                    var l = resp.loan;
                    var txns = resp.transactions || [];

                    // Populate Overview fields
                    $('#modalLoanDate').text(l.LoanDateFormatted || '-');
                    $('#modalDNumber').text(l.DNumber || '-');
                    $('#modalCustomerName').text(l.CustomerName || '-');
                    $('#modalInterestRate').text(l.InterestRate || '-');
                    $('#modalLoanAmount').text(l.LoanAmount);
                    $('#modalBalanceAmount').text(l.BalanceLoanAmount);
                    $('#modalDueDate').text(l.DueDateFormatted || '-');

                    // Status
                    if (l.Status === 'Active') {
                        $('#modalStatus').html('<span class="badge-active">Active</span>');
                    } else {
                        $('#modalStatus').html('<span class="badge-inactive">Inactive</span>');
                    }

                    // Build Transaction History Table Body
                    var tbodyHtml = '';
                    if (txns.length > 0) {
                        $.each(txns, function(i, t) {
                            var typeBadge = '';
                            if (t.TransactionType === 'LOAN_CREATION') {
                                typeBadge = '<span class="label label-info">LOAN_CREATION</span>';
                            } else if (t.TransactionType === 'INTEREST_PAYMENT') {
                                typeBadge = '<span class="label label-warning">INTEREST_PAYMENT</span>';
                            } else if (t.TransactionType === 'PRINCIPAL_PAYMENT') {
                                typeBadge = '<span class="label label-primary">PRINCIPAL_PAYMENT</span>';
                            } else if (t.TransactionType === 'JEWEL_RELEASE') {
                                typeBadge = '<span class="label label-success">JEWEL_RELEASE</span>';
                            } else {
                                typeBadge = '<span class="label label-default">' + (t.TransactionType || '-') + '</span>';
                            }

                            var printPdfBtn = '<a href="aster-jewel-loan-receipt-pdf.php?receipt_id=' + t.ReceiptId + '" target="_blank" class="btn btn-xs btn-default" title="Print Receipt"><i class="fa fa-print"></i></a>';

                            tbodyHtml += '<tr>';
                            tbodyHtml += '<td class="text-center">' + (i + 1) + '</td>';
                            tbodyHtml += '<td class="text-center">' + (t.ReceiptNo || '-') + '</td>';
                            tbodyHtml += '<td class="text-center">' + (t.FormattedReceiptDate || t.ReceiptDate || '-') + '</td>';
                            tbodyHtml += '<td class="text-center">' + typeBadge + '</td>';
                            tbodyHtml += '<td class="text-center">' + (t.PaymentMethod || 'Cash') + '</td>';
                            tbodyHtml += '<td class="text-right">' + formatMoney(t.PrincipalPaid) + '</td>';
                            tbodyHtml += '<td class="text-right">' + formatMoney(t.InterestPaid) + '</td>';
                            tbodyHtml += '<td class="text-right" style="font-weight:bold;">' + formatMoney(t.TotalPaid) + '</td>';
                            tbodyHtml += '<td class="text-right" style="font-weight:bold;">' + formatMoney(t.BalanceLoanAmount) + '</td>';
                            tbodyHtml += '<td class="text-center">' + printPdfBtn + '</td>';
                            tbodyHtml += '</tr>';
                        });
                    } else {
                        tbodyHtml = '<tr><td colspan="10" class="text-center" style="padding: 15px; color:#777;">No transactions found.</td></tr>';
                    }
                    $('#modalTxnBody').html(tbodyHtml);

                    $('#modalContentContainer').show();
                } else {
                    $('#modalTxnBody').html('<tr><td colspan="10" class="text-center text-danger" style="padding:15px;">' + ((resp && resp.message) ? resp.message : 'Error loading details.') + '</td></tr>');
                    $('#modalContentContainer').show();
                }
            },
            error: function() {
                $('#modalLoading').hide();
                $('#modalContentContainer').show();
                $('#modalTxnBody').html('<tr><td colspan="10" class="text-center text-danger" style="padding:15px;">Failed to connect to server.</td></tr>');
            }
        });
    });
});
</script>
</body>
</html>
