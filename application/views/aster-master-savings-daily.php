<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
require_once '../settings/config.php';
require_once 'controller/SavingsDailyController.php';

$controller = new SavingsDailyController($conn);
$data = $controller->handleRequest();
// Extract all controller data
extract($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Daily Savings</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="wrapper">

    <?php include_once "header.php"; ?>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Daily Savings</h2>
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

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
       <form method="post" action="aster-master-savings-daily.php">
    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['UserId']; ?>">

    <!-- LINE 1 – Installment Amount (full width) -->
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Installment Amount <span class="mandatory">*</span></label>
                <input type="number" name="installment_amount" id="installment_amount" class="form-control" required
                       value="<?php echo htmlspecialchars($installmentAmount); ?>">
            </div>
        </div>
    </div>

    <!-- LINE 2 – 4 Boxes side-by-side -->
    <div class="row">

        <div class="col-md-4">
            <div class="form-group">
                <label>100 Days <span class="mandatory">*</span></label>
                <input type="number" name="D100" class="form-control" required
                       value="<?php echo htmlspecialchars($D100); ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>200 Days <span class="mandatory">*</span></label>
                <input type="number" name="D200" class="form-control" required
                       value="<?php echo htmlspecialchars($D200); ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label>300 Days <span class="mandatory">*</span></label>
                <input type="number" name="D300" class="form-control" required
                       value="<?php echo htmlspecialchars($D300); ?>">
            </div>
        </div>

    </div>

    <!-- LINE 3 – Action Buttons -->
    <div class="row mt-3">
        <div class="col-md-12">
            <a href="aster-master-savings-daily.php" class="btn btn-primary">New</a>

            <button type="submit" name="savings-submit" class="btn btn-success">
                <?php echo $editId > 0 ? "Update" : "Add"; ?> Savings
            </button>

            <?php if ($editId > 0): ?>
                <a href="aster-master-savings-daily.php" class="btn btn-danger">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </div>

</form>


        <br>

        <!-- TABLE -->
        <table class="table table-bordered table-striped" id="savings-table">
            <thead>
            <tr class="header-row">
                <th>SlNo</th>
                <th>Installment Amount</th>
                <th>100 Days</th>
                <th>200 Days</th>
                <th>300 Days</th>
      
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>

            <?php if (!empty($savingsList)): ?>
                <?php foreach ($savingsList as $i => $row): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td align="center"><?php echo $row['InstallmentAmount']; ?></td>
                        <td align="right"><?php echo $row['D100']; ?></td>
                        <td align="right"><?php echo $row['D200']; ?></td>
                        <td align="right"><?php echo $row['D300']; ?></td>
                       

                        <td align="center">
                            <a href="?edit_id=<?php echo $row['DSID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $row['DSID']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure to delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>

                <!-- 15 Dummy Rows -->
             

            <?php endif; ?>

            </tbody>
        </table>

    </div>

    <?php include_once "footer.php"; ?>
</div>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        $('#savings-table').DataTable({
            responsive: true,
            ordering: true,
            searching: true,
            pageLength: 10
        });
    });
</script>

</body>
</html>
<?php $conn->close(); ?>
