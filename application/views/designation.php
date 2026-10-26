<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_secure', 1); // Requires HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();
session_regenerate_id(true);
if (!isset($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

define('ROOT_PATH', __DIR__ . '/..');

define('PROCESS_PATH','../php/designation-process.php');
define('PAGE_NAME', 'designation.php');
include_once ROOT_PATH . '/php/general-functions.php';
include_once ROOT_PATH . '/settings/config.php';
include_once 'edit-alert.php';

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32)); // Fallback for older PHP

// Sanitize inputs
$status = isset($_REQUEST['s']) && is_numeric($_REQUEST['s']) ? (int)$_REQUEST['s'] : '';
$edit_id = isset($_REQUEST['eid']) && is_numeric($_REQUEST['eid']) ? (int)$_REQUEST['eid'] : '';
$delete_id = isset($_REQUEST['did']) && is_numeric($_REQUEST['did']) ? (int)$_REQUEST['did'] : '';
$designation = $description = '';

if ($edit_id) {
    try {
        [$designation, $description] = GetDesignationDetails($edit_id);
    } catch (Exception $e) {
        error_log('Error fetching designation: ' . $e->getMessage());
    }
}

if ($delete_id) {
    try {
        DeleteDesignation($delete_id);
    } catch (Exception $e) {
        error_log('Error deleting designation: ' . $e->getMessage());
        $status = 30; // Indicate deletion failure
    }
}
InsertLoginIdInLogged($_SESSION['UserId']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory | Designation</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/datepicker.css" rel="stylesheet">
    <link href="css/data-table.css" rel="stylesheet">
</head>
<body>
<div id="wrapper">
    <?php include_once "header.php"; ?>
    <?php
    list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage();
    if ($page_access_permission == 0) {
        echo PageAuthorizationMessage();
    } else {
    ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Designation</h2>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
            </div>
        </div>
        <div class="row data-notification">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <?php
                $messages = [
                    1 => 'Data Saved Successfully',
                    2 => 'Data Updated Successfully',
                    3 => 'Data Deleted Successfully',
                    30 => 'Can\'t Delete. Designation Used in User Registration'
                ];
                if (isset($messages[$status])) {
                    echo "<div class='success-message'>{$messages[$status]}</div>";
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <fieldset id="major-feild-set" style="margin-top:15px;">
                    <?php
                    function renderDesignationForm($designation, $description, $edit_id, $user_id) {
                        ob_start();
                        ?>
                        <?php  // echo PROCESS_PATH; ?>
                        <form name="designation" method="post" action="../php/designation-process.php" id="form_id">
                            <div class="form-group">
                                <label>Designation<span class="mandatory">*</span></label>
                                <input type="text" placeholder="Enter Designation" class="form-control" name="designation" id="designation" maxlength="50" 
                                value="<?php echo htmlspecialchars($designation); ?>" autofocus required>
                                <span id="status-designation"></span>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" rows="3" placeholder="Enter Description" name="description" id="description" maxlength="100">
								<?php echo htmlspecialchars($description); ?></textarea>
                                <input type="hidden" name="olddesg" value="<?php echo htmlspecialchars($designation); ?>">
                                <input type="hidden" name="eid" value="<?php echo (int)$edit_id; ?>">
                                <input type="hidden" name="userid" value="<?php echo (int)$user_id; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            </div>
                            <div class="form-group" style="text-align:center;">
                                <a href="<?php echo PAGE_NAME; ?>"><button type="button" class="btn btn-md btn-info" name="new">New</button></a>
                                <?php if ($edit_id): ?>
                                    <button type="submit" class="btn btn-md btn-info" id="editAlert" name="submit" style="display:none;"></button>
                                    <button class="btn btn-md btn-info" type="submit" name="submit" id="editClick">Update</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-md btn-info" name="submit" id="submit">Save</button>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php
                        return ob_get_clean();
                    }
                    echo renderDesignationForm($designation, $description, $edit_id, $_SESSION['UserId']);
                    ?>
                </fieldset>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <table id="designation-details" class="table table-striped table-bordered" cellspacing="0">
                    <thead>
                        <tr class="header-row">
                            <th>SlNo</th>
                            <th>Designation</th>
                            <th>Description</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody class="table-coloum-style">
                        <?php echo UserRelatedMaster('DesignationId', 'Designation', 'Description', PAGE_NAME, 'designation'); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
    <?php include_once "footer.php"; ?>
</div>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/metisMenu.min.js"></script>
<script src="js/mpdn.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    const idleMax = <?php echo json_encode($_SESSION['TimeIdleMax'] ?? 600); ?>;
    let idleTime = 0;
    const idleInterval = setInterval(timerIncrement, 60000);
    $(document).on('mousemove keypress', () => idleTime = 0);

    function timerIncrement() {
        idleTime++;
        if (idleTime > idleMax) {
            alert('Session expired due to inactivity. You will be logged out.');
            window.location.href = 'logout.php';
        }
    }

    $('#designation-details').DataTable();
});
</script>
</body>
</html>