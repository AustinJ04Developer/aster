<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once 'controller/GlProcessingFeeController.php';

$controller = new GlProcessingFeeController($conn);
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
    <title>ASTER - Processing Fee Master</title>
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
                <h2 class="page-header">Processing Fee Master</h2>
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
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php foreach ($errors as $err): ?>
                    <p style="margin: 0;"><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <form method="post" action="aster-master-gl-processing-fee.php">
            <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)$_SESSION['UserId']); ?>">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Date <span class="mandatory">*</span></label>
                        <input type="date" name="date" id="date" class="form-control" required
                               value="<?php echo htmlspecialchars($date); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Processing Fee Percentage (%) <span class="mandatory">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="processing_fee_percentage" id="processing_fee_percentage" class="form-control" required
                               placeholder="e.g. 1.00"
                               value="<?php echo htmlspecialchars((string)$processingFeePercentage); ?>">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-3" style="margin-bottom: 20px;">
                <div class="col-md-12">
                    <a href="aster-master-gl-processing-fee.php" class="btn btn-primary">New</a>

                    <button type="submit" name="fee-submit" class="btn btn-success">
                        <?php echo $editId > 0 ? "Update" : "Add"; ?> Processing Fee
                    </button>

                    <?php if ($editId > 0): ?>
                        <a href="aster-master-gl-processing-fee.php" class="btn btn-danger">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <br>

        <!-- TABLE -->
        <table class="table table-bordered table-striped" id="fee-table">
            <thead>
            <tr class="header-row">
                <th>SlNo</th>
                <th>Date</th>
                <th>Processing Fee Percentage (%)</th>
                <th>User ID</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($feeList)): ?>
                <?php foreach ($feeList as $i => $row): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['Date']); ?></td>
                        <td align="right"><?php echo htmlspecialchars(number_format((float)$row['ProcessingFeePercentage'], 2)); ?></td>
                        <td align="center"><?php echo htmlspecialchars((string)$row['UserId']); ?></td>
                        <td align="center">
                            <a href="?edit_id=<?php echo (int)$row['PFID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo (int)$row['PFID']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure to delete this processing fee record?')">Delete</a>
                        </td>
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
        $('#fee-table').DataTable({
            responsive: true,
            ordering: true,
            searching: true,
            pageLength: 10
        });
    });
</script>

</body>
</html>
<?php if (isset($conn) && $conn instanceof mysqli) { $conn->close(); } ?>
