<?php
session_start();

require_once 'controller/SearchController.php';
$controller = new SearchController($conn);
$data = $controller->handleRequest();

$searchmember = $data['searchmember'] ?? [];
$search_field = $data['search_field'] ?? '';
$search_value = $data['search_value'] ?? '';
$search_type = $data['search_type'] ?? ($_GET['tp'] ?? '');
$success = $data['success'];
$errors = $data['errors'];

if(empty($search_type))
    $search_type = $_REQUEST['tp'];

error_log("View rendered with success='$success', errors=" . print_r($errors, true));

$pageRoutes = [
    1 => 'create-current-sd-account.php',
    2 => 'aster-savings-daily.php',
    3 => 'aster-savings-monthly.php',
    4 => 'create-rd-account.php',
    5 => 'create-cd-account.php',
    6 => 'aster-jewel-loan.php',
    'jewel' => 'aster-jewel-loan.php'
];

$formAction = "search-account.php?tp=".$search_type;
$redirectPage = $pageRoutes[$search_type] ?? '#';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER-Search members</title>
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
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once "header.php"; ?>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Search Members</h2>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
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
                <?php
					if(isset($_SESSION['success_msg']))
					{
					?>
						<div class="alert alert-success alert-dismissible" role="alert">
							<span style="color:red;font-weight:bold;font-size:16px;">
								<?php echo $_SESSION['success_msg']; ?>
							</span>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						</div>
					<?php
						unset($_SESSION['success_msg']); // remove after showing once
					}
				?>
				
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
				
				<!--------------------------success message Start----------------------------->
               	<?php if(isset($_SESSION['success_msg'])) { ?>
					<div class="alert alert-success alert-dismissible">
						<?php
						echo $_SESSION['success_msg'];
						unset($_SESSION['success_msg']);
						?>
						<button type="button" class="close" data-dismiss="alert">&times;</button>
					</div>
				<?php } ?>
				<!-----------------------------success message End------------------------------>
                	<form method="post" action="<?php echo $formAction; ?>">
					<fieldset class="feild-set">
                    <div class="row">
          				<div class="col-md-6">
             				<div class="form-group">
              				<!-- form group -->
              					<label for="searchby">Select Search Field Name:</label>
              					<span class="mandatory">*</span>
                  				<select class="form-control dbfield selectpicker" id="selectedfield" name="search-field">
                      				<option selected value="1">Member Code</option>
                      				<option value="2">Name</option>
                      				<option value="3">Aadhaar No</option>
                      				<option value="4">Mobile No</option>
                   				</select>
              				</div>
            				<!-- ./ form group -->
          				</div>
          				<div class="col-md-6">
            				<div class="form-group">
              					<label for="role">Enter Search Value:</label>
              					<span class="mandatory">*</span>
              					<input type="text" class="form-control" id="search-value" name="search-value" 
                        		style="" placeholder="Enter Search Value" maxlength="30" required autofocus />
            				</div>
            				<div class="form-group">
              					<input type="hidden" name="search-type" value="<?php echo $search_type;?>"> <!-- Search type 1-> SD, 2-> FD, 3 -> RD, 4->CD,....-->
              					<button class='btn btn-md btn-info' type="submit" name="search-submit" id="search-button" >Search</button>
            				</div>
          				</div>
         			</div>
					</fieldset>
                </form>
            </div>
        </div>
        <br><br>
		<?php
			$show_value = count($searchmember);
			if ($show_value > 0) {
		?>
        <div class="row">
            <div class="col-md-12">
                <table id="search-details" class="table table-striped table-bordered">
                    <thead>
                        <tr class="header-row">
                            <th>SlNo</th>
                            <th>Member Code</th>
                            <th>Name</th>
                            <th>Aadhaar No</th>
                            <th>Mobile No</th>
                            <th>Create SD Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchmember as $index => $searchmember): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($searchmember['MemberCode']); ?></td>
                                <td><?php echo htmlspecialchars($searchmember['FullName']); ?></td>
                                <td><?php echo htmlspecialchars($searchmember['Aadhaar']); ?></td>
                                <td><?php echo htmlspecialchars($searchmember['MobileNo']); ?></td>
                                <td align="center">
									<a href="<?php echo $redirectPage; ?>?id=<?php echo $searchmember['MemberId']; ?>" 
									   class="btn btn-sm btn-warning">
									   View
									</a>
								</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
		<?php }?>
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
$('form').on('submit', function(e) {
    console.log('Form submitted with data:', $(this).serialize());
});

function disableButton() {
    const saveButton = document.getElementById('save-button');
    saveButton.disabled = true;
    console.log('Button disabled, submitting form');
    setTimeout(() => { 
        saveButton.disabled = false; 
        console.log('Button re-enabled');
    }, 2000);
}

$(document).ready(function() {
    $('#course-type-details').DataTable({
        "paging": true,
        "ordering": true,
        "info": true
    });
});
</script>

