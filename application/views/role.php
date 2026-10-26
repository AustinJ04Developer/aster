<?php
session_start();
// Redirect unauthenticated users
if (!isset($_SESSION['UserId'])) {
    header('Location: login.php');
    exit;
}

// Include configuration and database connection
require_once '../settings/config.php';
// Initialize variables
$is_inserted_in_db = $_GET['s'] ?? '';
$is_add_role = $_GET['p'] ?? '';
$is_edit_mode = $_GET['eid'] ?? '';
$is_delete_mode = $_GET['did'] ?? '';
$role = '';
$description = '';
$date = date('d/m/Y');

// Handle delete operation
if ($is_delete_mode && is_numeric($is_delete_mode)) {
    try {
        $stmt = $dbh->prepare('DELETE FROM role WHERE RoleId = ?');
        $stmt->execute([$is_delete_mode]);
        header('Location: index.php?s=3');
        exit;
    } catch (PDOException $e) {
        $is_inserted_in_db = ($e->getCode() == 23000) ? 30 : 'error'; // Handle foreign key constraint
    }
}

// Fetch role details for edit
if ($is_edit_mode && is_numeric($is_edit_mode)) {
    try {
        $stmt = $dbh->prepare('SELECT RoleName, Description, Date FROM role WHERE RoleId = ?');
        $stmt->execute([$is_edit_mode]);
        $role_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($role_data) {
            $role = $role_data['RoleName'];
            $description = $role_data['Description'];
            $date = date('d/m/Y', strtotime($role_data['Date']));
        }
    } catch (PDOException $e) {
        $is_inserted_in_db = 'error';
    }
}

// Check page access permissions
require_once '../php/general-functions.php';
[$page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission] = UserAuthorizedToViewThisPage();
if ($page_access_permission == 0) {
    die(PageAuthorizationMessage());
}
// Log user activity
InsertLoginIdInLogged($_SESSION['UserId']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Role Management</title>
    <!-- Bootstrap Core CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/mpdn.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="wrapper">
        <?php include_once 'header.php'; ?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Create Role</h2>
                    <hr class="hr-background">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <fieldset id="major-feild-set" style="margin-top:15px;">
                        <?php
                        $messages = [
                            1 => 'Data Saved Successfully',
                            2 => 'Data Updated Successfully',
                            3 => 'Data Deleted Successfully',
                            30 => 'Can\'t Delete. Role Used in User Registration',
                            'error' => 'Operation Failed',
                            1 => 'You do not have permission to add role'
                        ];
                        if ($is_inserted_in_db && isset($messages[$is_inserted_in_db])) {
                            echo '<div class="success-message">' . htmlspecialchars($messages[$is_inserted_in_db]) . '</div>';
                        }
                        if ($is_add_role == 1) {
                            echo '<div class="success-message">' . htmlspecialchars($messages[1]) . '</div>';
                        }
                        ?>
                        <form role="frm-role" method="post" action="../php/role-process.php">
                            <div class="form-group">
                                <label for="current-date">Date:</label>
                                <input type="text" class="form-control date-picker" id="current-date" name="current-date"
                                       value="<?php echo htmlspecialchars($date); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="role">Role:<span class="mandatory">*</span></label>
                                <input type="text" class="form-control" id="role" name="role"
                                       placeholder="Enter role name" maxlength="50"
                                       value="<?php echo htmlspecialchars($role); ?>" required autofocus>
                                <span id="status-role"></span>
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea name="description" id="description" class="form-control"
                                          placeholder="Enter description about the role"
                                          maxlength="500"><?php echo htmlspecialchars($description); ?></textarea>
                                <input type="hidden" name="oldrole" value="<?php echo htmlspecialchars($role); ?>">
                                <input type="hidden" name="eid" value="<?php echo htmlspecialchars($is_edit_mode); ?>">
                                <input type="hidden" name="userid" value="<?php echo htmlspecialchars($_SESSION['UserId']); ?>">
                            </div>
                            <div class="form-group" style="text-align:center;">
                                <button type="submit" class="btn btn-md btn-info"
                                        name="submit"><?php echo $is_edit_mode ? 'Update' : 'Create'; ?></button>
                            </div>
                        </form>
                    </fieldset>
                </div>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    <table id="role-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr class="header-row">
                                <th>SlNo</th>
                                <th>Role</th>
                                <th>Description</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody class="table-coloum-style">
                            <?php
                            $stmt = $dbh->query('SELECT RoleId, RoleName, Description FROM role ORDER BY RoleId');
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($roles as $index => $role) {
                                echo '<tr>';
                                echo '<td>' . ($index + 1) . '</td>';
                                echo '<td>' . htmlspecialchars($role['RoleName']) . '</td>';
                                echo '<td>' . htmlspecialchars($role['Description']) . '</td>';
                                echo '<td align="center"><a href="?eid=' . $role['RoleId'] . '" class="btn btn-xs btn-warning">Edit</a></td>';
                                echo '<td align="center"><a href="?did=' . $role['RoleId'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'Are you sure?\')">Delete
								</a></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php include_once 'footer.php'; ?>
    </div>
 <script src="js/jquery-1.9.1.min.js"></script>
	<script src="js/user-registration.js" type="text/javascript"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/show-date-picker.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/metisMenu.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="js/mpdn.js"></script>
    <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
    
    
    <!-- BY RB ON 20/2/16 -->
    <script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/addmore.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#role-details').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true
            });

            // Initialize datepicker
            $('.date-picker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true
            });

            // Session timeout
            let idleTime = 0;
            const idleMax = <?php echo (int)$_SESSION['TimeIdleMax']; ?>;
            const idleInterval = setInterval(timerIncrement, 40000);
            $(document).on('mousemove keypress', () => idleTime = 0);

            function timerIncrement() {
                idleTime++;
                if (idleTime > idleMax) {
                    window.location.href = 'logout.php';
                }
            }

            // Handle browser unload for user logout
            let unloaded = false;
            $(window).on('beforeunload unload', function() {
                if (!unloaded) {
                    $.ajax({
                        type: 'GET',
                        async: false,
                        url: '../php/clear_current_user.php',
                        data: { uid: '<?php echo $_SESSION['UserId']; ?>', amodule: 1 },
                        timeout: 5000
                    });
                    unloaded = true;
                }
            });
        });
    </script>
</body>
</html>