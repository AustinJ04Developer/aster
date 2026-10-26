<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
require_once '../settings/config.php';
require_once 'controller/SavingsMonthlyController.php';

$controller = new SavingsMonthlyController($conn);
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
    <title>ASTER - Monthly Savings</title>
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
                <h2 class="page-header">Monthly Savings</h2>
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
       <form method="post" action="aster-master-savings-monthly.php">
    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['UserId']; ?>">

    <!-- LINE 1 – Installment Amount (full width) -->
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Installment Amount <span class="mandatory">*</span></label>
                <input type="number" name="installment_amount" class="form-control" required
                       value="<?php echo htmlspecialchars($installmentAmount); ?>">
            </div>
        </div>
    </div>

    <!-- LINE 2 – 4 Boxes side-by-side -->
    <div class="row">

        <div class="col-md-3">
            <div class="form-group">
                <label>2 Years <span class="mandatory">*</span></label>
                <input type="number" name="years2" class="form-control" required
                       value="<?php echo htmlspecialchars($years2); ?>">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>3 Years <span class="mandatory">*</span></label>
                <input type="number" name="years3" class="form-control" required
                       value="<?php echo htmlspecialchars($years3); ?>">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>4 Years <span class="mandatory">*</span></label>
                <input type="number" name="years4" class="form-control" required
                       value="<?php echo htmlspecialchars($years4); ?>">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>5 Years <span class="mandatory">*</span></label>
                <input type="number" name="years5" class="form-control" required
                       value="<?php echo htmlspecialchars($years5); ?>">
            </div>
        </div>

    </div>

    <!-- LINE 3 – Action Buttons -->
    <div class="row mt-3">
        <div class="col-md-12">
            <a href="aster-master-savings-monthly.php" class="btn btn-primary">New</a>

            <button type="submit" name="savings-submit" class="btn btn-success">
                <?php echo $editId > 0 ? "Update" : "Add"; ?> Savings
            </button>

            <?php if ($editId > 0): ?>
                <a href="aster-master-savings-monthly.php" class="btn btn-danger">Cancel Edit</a>
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
                <th>2 Years</th>
                <th>3 Years</th>
                <th>4 Years</th>
                <th>5 Years</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>

            <?php if (!empty($savingsList)): ?>
                <?php foreach ($savingsList as $i => $row): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td align="center"><?php echo $row['InstallmentAmount']; ?></td>
                        <td align="right"><?php echo $row['Years2']; ?></td>
                        <td align="right"><?php echo $row['Years3']; ?></td>
                        <td align="right"><?php echo $row['Years4']; ?></td>
                        <td align="right"><?php echo $row['Years5']; ?></td>

                        <td align="center">
                            <a href="?edit_id=<?php echo $row['SMID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $row['SMID']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure to delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>

                <!-- 15 Dummy Rows -->
                <?php
                $dummy = [
                    [500, 12000, 18000, 24000, 30000],
                    [750, 15000, 21000, 27000, 33000],
                    [1000, 20000, 28000, 36000, 44000],
                    [1200, 24000, 32000, 40000, 48000],
                    [1500, 30000, 38000, 46000, 54000],
                    [2000, 38000, 47000, 56000, 65000],
                    [2500, 45000, 55000, 65000, 75000],
                    [3000, 52000, 62000, 72000, 82000],
                    [3500, 60000, 70000, 80000, 90000],
                    [4000, 68000, 78000, 88000, 98000],
                    [4500, 76000, 86000, 96000, 106000],
                    [5000, 84000, 94000, 104000, 114000],
                    [6000, 92000, 102000, 112000, 122000],
                    [7000, 100000, 110000, 120000, 130000],
                    [8000, 108000, 118000, 128000, 138000]
                ];
                ?>

                <?php foreach ($dummy as $i => $d): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo $d[0]; ?></td>
                        <td><?php echo $d[1]; ?></td>
                        <td><?php echo $d[2]; ?></td>
                        <td><?php echo $d[3]; ?></td>
                        <td><?php echo $d[4]; ?></td>
                        <td><span class="text-muted">No actions</span></td>
                    </tr>
                <?php endforeach; ?>

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
