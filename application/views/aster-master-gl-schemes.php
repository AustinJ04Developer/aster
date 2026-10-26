<?php
session_start();
// Secure session handling
if (!isset($_SESSION['UserId']) || empty($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once 'controller/GlSchemeController.php';

$controller = new GlSchemeController($conn);
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
    <title>ASTER - Scheme Master</title>
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
                <h2 class="page-header">Scheme Master</h2>
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
        <form method="post" action="aster-master-gl-schemes.php">
            <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)$_SESSION['UserId']); ?>">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date <span class="mandatory">*</span></label>
                        <input type="date" name="date" id="date" class="form-control" required
                               value="<?php echo htmlspecialchars($date); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Interest Percentage (%) <span class="mandatory">*</span></label>
                        <select name="interest_percentage" id="interest_percentage" class="form-control" required>
                            <option value="">-- Select Interest % --</option>
                            <option value="18" <?php echo ((string)$interestPercentage === '18' || (string)$interestPercentage === '18.00') ? 'selected' : ''; ?>>18%</option>
                            <option value="24" <?php echo ((string)$interestPercentage === '24' || (string)$interestPercentage === '24.00') ? 'selected' : ''; ?>>24%</option>
                            <option value="30" <?php echo ((string)$interestPercentage === '30' || (string)$interestPercentage === '30.00') ? 'selected' : ''; ?>>30%</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Amount From <span class="mandatory">*</span></label>
                        <input type="number" step="1" min="0" name="amount_from" id="amount_from" class="form-control" required
                               placeholder="e.g. 1000"
                               value="<?php echo htmlspecialchars((string)$amountFrom); ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Amount To <span class="mandatory">*</span></label>
                        <input type="number" step="1" min="0" name="amount_to" id="amount_to" class="form-control" required
                               placeholder="e.g. 50000"
                               value="<?php echo htmlspecialchars((string)$amountTo); ?>">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-3" style="margin-bottom: 20px;">
                <div class="col-md-12">
                    <a href="aster-master-gl-schemes.php" class="btn btn-primary">New</a>

                    <button type="submit" name="scheme-submit" class="btn btn-success">
                        <?php echo $editId > 0 ? "Update" : "Add"; ?> Scheme
                    </button>

                    <?php if ($editId > 0): ?>
                        <a href="aster-master-gl-schemes.php" class="btn btn-danger">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <br>

        <!-- TABLE -->
        <table class="table table-bordered table-striped" id="schemes-table">
            <thead>
            <tr class="header-row">
                <th>SlNo</th>
                <th>Date</th>
                <th>Interest %</th>
                <th>Amount From</th>
                <th>Amount To</th>
                <th>User ID</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($schemeList)): ?>
                <?php foreach ($schemeList as $i => $row): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['Date']); ?></td>
                        <td align="right"><?php echo htmlspecialchars(number_format((float)$row['InterestPercentage'], 0)); ?> %</td>
                        <td align="right"><?php echo htmlspecialchars(number_format((float)$row['AmountFrom'], 0)); ?></td>
                        <td align="right"><?php echo htmlspecialchars(number_format((float)$row['AmountTo'], 0)); ?></td>
                        <td align="center"><?php echo htmlspecialchars((string)$row['UserId']); ?></td>
                        <td align="center">
                            <a href="?edit_id=<?php echo (int)$row['LSID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo (int)$row['LSID']; ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure to delete this scheme record?')">Delete</a>
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
        $('#schemes-table').DataTable({
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
