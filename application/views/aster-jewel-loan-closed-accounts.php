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
require_once 'controller/JewelLoanClosedAccountsController.php';

$controller = new JewelLoanClosedAccountsController($conn);
$data = $controller->handleRequest();

$closedLoans = $data['closedLoans'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Closed Accounts List</title>

    <!-- Standard Aster Platform CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">

    <style>
        .badge-inactive {
            background-color: #777777;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 8px;
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
                <h2 class="page-header">Closed Accounts List</h2>
                <hr class="hr-background">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="closed-accounts-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="header-row">
                                <th style="width: 50px;">SlNo</th>
                                <th>Account Creation Date</th>
                                <th>D Number</th>
                                <th>Name</th>
                                <th class="text-center">Interest %</th>
                                <th class="text-right">Loan Amount (₹)</th>
                                <th class="text-center" style="width: 90px;">Status</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($closedLoans)): ?>
                                <?php foreach ($closedLoans as $idx => $loan): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $idx + 1; ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($loan['LoanDateFormatted']); ?></td>
                                        <td class="text-center" style="font-weight: bold; color: #337ab7;">
                                            <?php echo htmlspecialchars($loan['DNumber']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($loan['CustomerName']); ?></td>
                                        <td class="text-center"><?php echo number_format((float)$loan['InterestRate'], 0); ?>%</td>
                                        <td class="text-right"><?php echo number_format((float)$loan['LoanAmount'], 2); ?></td>
                                        <td class="text-center">
                                            <span class="badge-inactive">Inactive</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="aster-jewel-loan-view.php?dnum=<?php echo urlencode($loan['DNumber']); ?>" 
                                               class="btn btn-xs btn-info" title="View Account Details">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center" style="padding: 20px; color: #777;">
                                        No closed or inactive accounts found.
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

<!-- Standard Aster Platform Scripts -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    if ($('#closed-accounts-table').length && <?php echo (!empty($closedLoans) ? 'true' : 'false'); ?>) {
        $('#closed-accounts-table').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "pageLength": 10
        });
    }
});
</script>
</body>
</html>
