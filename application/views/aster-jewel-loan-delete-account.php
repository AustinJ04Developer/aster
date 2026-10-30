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
require_once 'controller/JewelLoanDeleteAccountController.php';

$controller = new JewelLoanDeleteAccountController($conn);
$data = $controller->handleRequest();

$allAccounts     = $data['allAccounts'] ?? [];
$searchedAccount = $data['searchedAccount'] ?? null;
$selectedDNumber = $data['selectedDNumber'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Delete Accounts</title>

    <!-- Standard Aster Platform CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <link href="css/datatablelatest.css" rel="stylesheet">
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Delete Accounts</h2>
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

        <!-- Session Flash Alerts -->
        <div class="row">
            <div class="col-lg-12">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Form matching Image 1 -->
        <div class="row">
            <div class="col-lg-12">
                <form method="post" action="aster-jewel-loan-delete-account.php">
                    <fieldset class="feild-set">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="searchby">Select D Number:</label>
                                    <span class="mandatory">*</span>
                                    <select class="form-control dbfield selectpicker" id="selectedfields" name="dnumber-search-field" required>
                                        <option value="">Select D Number</option>
                                        <?php if (!empty($allAccounts)): ?>
                                            <?php foreach ($allAccounts as $row): ?>
                                                <option value="<?= htmlspecialchars($row['DNumber']) ?>" <?= ($selectedDNumber === $row['DNumber']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['DNumber']) ?> - <?= htmlspecialchars($row['CustomerName']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" style="margin-top: 25px;">
                                <div class="form-group">
                                    <button class="btn btn-md btn-info" type="submit" name="search-submit-jl-delete-account" id="search-button">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>

        <br><br>

        <!-- Searched Account Result Table -->
        <?php if (!empty($searchedAccount)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="search-details" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="header-row">
                                    <th style="width: 50px;">SlNo</th>
                                    <th>Account Created Date</th>
                                    <th>D Number</th>
                                    <th>Name</th>
                                    <th class="text-center">Interest %</th>
                                    <th class="text-right">Loan Amount (₹)</th>
                                    <th class="text-center" style="width: 90px;">Status</th>
                                    <th class="text-center" style="width: 120px;">Delete Account</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td class="text-center"><?= htmlspecialchars($searchedAccount['LoanDateFormatted']) ?></td>
                                    <td class="text-center" style="font-weight: bold; color: #337ab7;">
                                        <?= htmlspecialchars($searchedAccount['DNumber']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($searchedAccount['CustomerName']) ?></td>
                                    <td class="text-center"><?= number_format((float)$searchedAccount['InterestRate'], 0) ?>%</td>
                                    <td class="text-right"><?= number_format((float)$searchedAccount['LoanAmount'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($searchedAccount['Status'] === 'Active'): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-default">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="aster-jewel-loan-delete-account.php?delete_account=1&dnum=<?= urlencode($searchedAccount['DNumber']) ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to permanently delete this entire jewel loan account (D Number: <?= htmlspecialchars($searchedAccount['DNumber']) ?>)?\n\nAll customer, loan details, items, receipts, and rate history records will be completely removed.\nThis action cannot be undone.')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php include_once "footer.php"; ?>
</div>

<!-- Standard Aster Platform Scripts -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
</body>
</html>
