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
        .txn-history-heading {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            font-size: 22px;
            margin-top: 5px;
            margin-bottom: 22px;
            color: #222;
        }
        .popup-meta-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            background-color: #ffffff;
        }
        .popup-meta-table td {
            padding: 12px 18px;
            border: 1px solid #ddd;
            font-size: 15px;
        }
        .popup-meta-label {
            font-weight: bold;
            color: #222;
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
                                        data-dnum="<?php echo htmlspecialchars($loan['DNumber']); ?>"
                                        onclick="openTransactionHistoryModal('<?php echo htmlspecialchars($loan['DNumber']); ?>')">
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
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalTxnTitle">Transaction History</h4>
            </div>
            <div class="modal-body" style="padding: 25px 20px;">
                <!-- Loading Spinner -->
                <div id="modalLoading" class="text-center" style="padding: 30px 0; display: none;">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="text-muted" style="margin-top: 8px;">Loading transactions...</p>
                </div>

                <!-- Content Container -->
                <div id="modalContentContainer">
                    <!-- Heading matching Image 1 -->
                    <div class="txn-history-heading">Transaction History</div>

                    <!-- 3-Column Header Table matching Image 1: D Number | Name | Status -->
                    <table class="popup-meta-table">
                        <tbody>
                            <tr>
                                <td style="width: 33%;">
                                    <span class="popup-meta-label">D Number:</span> <span id="modalDNumber" style="font-weight: normal; color: #337ab7;">-</span>
                                </td>
                                <td style="width: 34%;">
                                    <span class="popup-meta-label">Name:</span> <span id="modalCustomerName" style="font-weight: normal;">-</span>
                                </td>
                                <td style="width: 33%;">
                                    <span class="popup-meta-label">Status:</span> <span id="modalStatus" style="font-weight: normal;">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Heading matching Image 2 from aster-jewel-loan-view.php -->
                    <div style="font-size: 16px; font-weight: bold; color: #337ab7; margin-bottom: 12px;">
                        <i class="fa fa-ticket"></i> Transaction & Receipts History
                    </div>

                    <!-- Transaction & Receipts History Table matching aster-jewel-loan-view.php -->
                    <div class="table-responsive" id="modalTxnTableContainer">
                        <table class="table table-bordered table-striped table-hover" id="modalTxnTable" style="margin-bottom: 0;">
                            <thead>
                                <tr style="background-color: #f5f5f5;">
                                    <th>Receipt No</th>
                                    <th>Receipt Date</th>
                                    <th>Transaction Type</th>
                                    <th>Payment Method</th>
                                    <th class="text-right">Balance Amount (₹)</th>
                                    <th class="text-right">Total Paid (₹)</th>
                                    <th class="text-right">Principal (₹)</th>
                                    <th class="text-right">Interest (₹)</th>
                                    <th class="text-right">Current Bal (₹)</th>
                                    <th class="text-center no-print" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modalTxnBody">
                                <!-- Populated dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info text-center" id="modalNoTxnMsg" style="display: none; margin-top: 15px; margin-bottom: 0;">
                        No receipt transactions recorded for this jewel loan yet.
                    </div>

                    <!-- Centered Red Close Button matching Image 1 -->
                    <div class="text-center" style="margin-top: 25px; margin-bottom: 5px;">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" style="background-color: #d9534f; border-color: #d43f3a; padding: 6px 30px; font-size: 14px; font-weight: bold; border-radius: 4px;">
                            Close
                        </button>
                    </div>
                </div>
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
// Format money helper
function formatMoney(num) {
    var n = parseFloat(num);
    if (isNaN(n)) return '0.00';
    return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Global modal opener function
window.openTransactionHistoryModal = function(targetParam) {
    if (!targetParam) return;

    $('#modalLoading').show();
    $('#modalContentContainer').hide();
    $('#modalTransactionHistory').modal('show');

    $.ajax({
        url: 'aster-jewel-loan-account-list.php',
        type: 'GET',
        dataType: 'json',
        data: {
            ajax_txn_history: targetParam
        },
        success: function(resp) {
            $('#modalLoading').hide();
            if (resp && resp.status === 'success') {
                var l = resp.loan;
                var txns = resp.transactions || [];

                // 3-column table
                $('#modalDNumber').text(l.DNumber || '-');
                $('#modalCustomerName').text(l.CustomerName || '-');

                // Status
                if (l.Status === 'Active') {
                    $('#modalStatus').html('<span class="label label-success" style="font-size: 12px; font-weight: bold; padding: 3px 8px;">Active</span>');
                } else {
                    $('#modalStatus').html('<span class="label label-default" style="font-size: 12px; font-weight: bold; padding: 3px 8px;">Inactive</span>');
                }

                // Build Transaction History Table Body matching aster-jewel-loan-view.php
                var tbodyHtml = '';
                if (txns.length > 0) {
                    $('#modalTxnTableContainer').show();
                    $('#modalNoTxnMsg').hide();

                    $.each(txns, function(i, t) {
                        var isRelease = (t.TransactionType === 'JEWEL_RELEASE');
                        var isLoanCreation = (t.TransactionType === 'LOAN_CREATION');
                        var typeClass = isRelease ? 'success' : (isLoanCreation ? 'info' : 'primary');
                        var typeBadge = '<span class="label label-' + typeClass + '" style="font-size: 11px; padding: 3px 6px;">' + (t.TransactionType || '-') + '</span>';

                        var principalPaid = parseFloat(t.PrincipalPaid || 0);
                        var interestPaid = parseFloat(t.InterestPaid || 0);
                        var totalPaid = parseFloat(t.TotalPaid || 0);
                        var balAmount = parseFloat(t.BalanceLoanAmount || 0);
                        var prevBal = isLoanCreation ? balAmount : (balAmount + principalPaid);

                        var actionBtns = '<a href="aster-jewel-loan-receipt.php?receipt_id=' + encodeURIComponent(t.ReceiptId) + '&view_only=1" class="btn btn-xs btn-info" title="View Receipt Voucher" style="margin-right: 4px;">' +
                                         '<i class="fa fa-eye"></i> View</a>' +
                                         '<a href="aster-jewel-loan-receipt-pdf.php?receipt_id=' + encodeURIComponent(t.ReceiptId) + '" target="_blank" class="btn btn-xs btn-default" title="PDF Receipt">' +
                                         '<i class="fa fa-file-text-o"></i></a>';

                        tbodyHtml += '<tr>';
                        tbodyHtml += '<td><strong>' + (t.ReceiptNo || '-') + '</strong></td>';
                        tbodyHtml += '<td>' + (t.FormattedReceiptDate || t.ReceiptDate || '-') + '</td>';
                        tbodyHtml += '<td>' + typeBadge + '</td>';
                        tbodyHtml += '<td>' + (t.PaymentMethod || 'Cash') + '</td>';
                        tbodyHtml += '<td class="text-right">₹' + formatMoney(prevBal) + '</td>';
                        tbodyHtml += '<td class="text-right" style="font-weight: bold; color: #337ab7;">₹' + formatMoney(totalPaid) + '</td>';
                        tbodyHtml += '<td class="text-right">₹' + formatMoney(principalPaid) + '</td>';
                        tbodyHtml += '<td class="text-right">₹' + formatMoney(interestPaid) + '</td>';
                        tbodyHtml += '<td class="text-right">₹' + formatMoney(balAmount) + '</td>';
                        tbodyHtml += '<td class="text-center no-print">' + actionBtns + '</td>';
                        tbodyHtml += '</tr>';
                    });
                    $('#modalTxnBody').html(tbodyHtml);
                } else {
                    $('#modalTxnTableContainer').hide();
                    $('#modalNoTxnMsg').show();
                    $('#modalTxnBody').html('');
                }

                $('#modalContentContainer').show();
            } else {
                $('#modalTxnTableContainer').hide();
                $('#modalNoTxnMsg').html('<span class="text-danger">' + ((resp && resp.message) ? resp.message : 'Error loading details.') + '</span>').show();
                $('#modalContentContainer').show();
            }
        },
        error: function() {
            $('#modalLoading').hide();
            $('#modalContentContainer').show();
            $('#modalTxnTableContainer').hide();
            $('#modalNoTxnMsg').html('<span class="text-danger">Failed to communicate with server.</span>').show();
        }
    });
};

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

    // Delegated click handler on row
    $(document).on('click', '#designation-details tbody tr, .clickable-loan-row', function(e) {
        if ($(e.target).closest('a, button, input').length) {
            return;
        }

        var dNumber = $(this).attr('data-dnum') || $(this).data('dnum');
        var loanDetailId = $(this).attr('data-id') || $(this).data('id');

        if (!dNumber) {
            var dCell = $(this).find('td:nth-child(3)');
            if (dCell.length) {
                dNumber = $.trim(dCell.text());
            }
        }

        var targetParam = dNumber || loanDetailId;
        if (targetParam) {
            openTransactionHistoryModal(targetParam);
        }
    });
});
</script>
</body>
</html>
