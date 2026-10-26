<?php
session_start();

if (!defined('DB_NAME')) define('DB_NAME', 'aster');
require_once '../settings/config.php';
require_once 'controller/SearchController.php';
$controller = new SearchController($conn);
$data = $controller->handleRequest();

$searchmember = $data['searchmember'] ?? [];
$search_field = $data['search_field'] ?? '';
$search_value = $data['search_value'] ?? '';
$search_type = $data['search_type'] ?? ($_GET['tp'] ?? 'jewel');
$success = $data['success'];
$errors = $data['errors'];

if(empty($search_type) && isset($_REQUEST['tp'])) {
    $search_type = $_REQUEST['tp'];
}

error_log("Jewel Loan Search View rendered with success='$success', errors=" . print_r($errors, true));

$formAction = "aster-jewel-loan-search.php" . (!empty($search_type) ? "?tp=" . urlencode($search_type) : "");
$redirectPage = "aster-jewel-loan.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Search Members (Jewel Loan)</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mpdn.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
    <link href="css/datatablelatest.css" rel="stylesheet">
    <style>
        .dataTables_filter {
            display: block !important;
            margin-bottom: 3px;
        }
        .header-row th {
            background-color: #0088cc !important;
            color: #ffffff !important;
            text-align: center;
            vertical-align: middle;
        }
        .loan-subtitle {
            color: #0000ff;
            font-size: 16px;
            font-weight: bold;
            margin-top: -10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .feild-set {
            border: 1px solid #ddd !important;
            padding: 15px !important;
            border-radius: 6px;
            background-color: #fcfcfc;
        }
        .mandatory-star {
            color: red;
            font-weight: bold;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once "header.php"; ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header" style="text-align: center; color: #0f5c38; font-weight: bold; border-bottom: none; margin-bottom: 5px;">Search Members</h2>
                <div class="loan-subtitle">Jewel Loan</div>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-right">
                <h6 class="madatory-caption" style="margin-top: 0; margin-bottom: 10px;"><span class="mandatory-star">*</span> Fields are mandatory</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php if (!empty($_GET['s'])) { ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <?php echo htmlspecialchars($_GET['s']); ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php } ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo $formAction; ?>">
                    <fieldset class="feild-set">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selectedfield">Select Search Field Name: <span class="mandatory-star">*</span></label>
                                    <select class="form-control dbfield" id="selectedfield" name="search-field" required>
                                        <option value="1" <?php echo ($search_field == '1') ? 'selected' : ''; ?>>Member Code / D Number</option>
                                        <option value="2" <?php echo ($search_field == '2') ? 'selected' : ''; ?>>Name</option>
                                        <option value="3" <?php echo ($search_field == '3') ? 'selected' : ''; ?>>Aadhaar No</option>
                                        <option value="4" <?php echo ($search_field == '4') ? 'selected' : ''; ?>>Mobile No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search-value">Enter Search Value: <span class="mandatory-star">*</span></label>
                                    <input type="text" class="form-control" id="search-value" name="search-value" 
                                           placeholder="Enter Search Value" maxlength="50" required autofocus 
                                           value="<?php echo htmlspecialchars($search_value); ?>" />
                                </div>
                                <div class="form-group" style="margin-top: 10px;">
                                    <input type="hidden" name="search-type" value="<?php echo htmlspecialchars($search_type); ?>">
                                    <button class="btn btn-md btn-info" type="submit" name="search-submit" id="search-button" style="background-color: #5bc0de; border-color: #46b8da; color: #fff; padding: 6px 18px;">Search</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <br>
        <?php
            $show_value = count($searchmember);
            if ($show_value > 0) {
        ?>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="search-details" class="table table-striped table-bordered">
                        <thead>
                            <tr class="header-row">
                                <th style="width: 60px;">SlNo</th>
                                <th>Member Code</th>
                                <th>Name</th>
                                <th>Aadhaar No</th>
                                <th>Mobile No</th>
                                <th style="width: 210px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchmember as $index => $row): ?>
                                <tr>
                                    <td class="text-center" style="vertical-align: middle;"><?php echo $index + 1; ?></td>
                                    <td style="vertical-align: middle;"><?php echo htmlspecialchars($row['MemberCode']); ?></td>
                                    <td style="vertical-align: middle;"><?php echo htmlspecialchars($row['FullName']); ?></td>
                                    <td style="vertical-align: middle;"><?php echo htmlspecialchars($row['Aadhaar'] ?? ''); ?></td>
                                    <td style="vertical-align: middle;"><?php echo htmlspecialchars($row['MobileNo'] ?? ''); ?></td>
                                    <td class="text-center" style="vertical-align: middle; white-space: nowrap;">
                                        <a href="aster-jewel-loan-view.php?id=<?php echo urlencode($row['MemberId']); ?>" 
                                           class="btn btn-sm btn-info" 
                                           style="padding: 4px 10px; font-weight: 500; margin-right: 4px;">
                                           <i class="fa fa-eye"></i> View Details
                                        </a>
                                        <a href="aster-jewel-loan.php?id=<?php echo urlencode($row['MemberId']); ?>" 
                                           class="btn btn-sm btn-success" 
                                           style="padding: 4px 10px; font-weight: 500;">
                                           <i class="fa fa-plus-circle"></i> New Loan
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-warning text-center" style="margin-top: 15px;">
                        No members found matching your search criteria.
                    </div>
                </div>
            </div>
        <?php } ?>
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
$(document).ready(function() {
    if ($('#search-details').length) {
        $('#search-details').DataTable({
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
