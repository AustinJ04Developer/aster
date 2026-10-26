<?php
session_start();
require_once 'controller/AccountYearController.php';
$controller = new AccountYearController($conn);
$data = $controller->handleRequest();
$accountyears = $data['accountyears'];
$editId = $data['editId'];
$accountyear = $data['accountyear'];
$is_default = $data['isdefault'];
$success = $data['success'];
$errors = $data['errors'];
error_log("View rendered with success='$success', errors=" . print_r($errors, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Manage Account year</title>
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
                <h2 class="page-header">Manage Account year</h2>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">?</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">?</span>
                        </button>
                    </div>
                <?php endif; ?>
                <form method="post" action="aster-account-year.php" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
                <div class="row">
    	

        <div class="col-md-3">
            <div class="form-group">
                <label>Account year <span class="mandatory">*</span></label>
                <select class="form-control" id="accountyear" name="accountyear" required >
                                    <option value="AYID">Select year</option>
                                    <?php foreach ($accountyears  as $year): ?>
                                        <option value="<?php echo $year['AYID']; ?>" 
                                        <?php echo ($editId == $year['AYID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($year['AccountYear']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
            </div>
        </div>
		<div class="col-md-3">
            <div class="form-group">
                <label>Is default <span class="mandatory">*</span></label>
                <div class="radio">
					<label>
   					 <input type="radio" name="is_default" value="1"
        			<?php echo ($is_default == 1) ? 'checked' : ''; ?>>Yes</label>
					<label style="margin-left:10px;">
    				<input type="radio" name="is_default" value="0"
        			<?php echo ($is_default == 0) ? 'checked' : ''; ?>>No</label>
                    </div>
                    
            </div>
        </div>


    </div>
                    <button type="submit" id="save-button" name="year-submit" class="btn btn-success"
        			<?php echo ($editId <= 0) ? 'disabled' : ''; ?>>Update</button>
                    <?php if ($editId > 0): ?>
                        <a href="aster-account-year.php" class="btn btn-danger">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <table id="course-type-details" class="table table-striped table-bordered" cellspacing="0">
                    <thead>
                        <tr class="header-row">
                           <th>SlNo</th>
         			       <th>Account Year</th>
          			       <th>Is Default</th>
						   <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-coloum-style">
					<?php if (empty($accountyears)): ?>
						<tr>
    						<td colspan="4" class="text-center">Account Year not Found</td>
						</tr>
					<?php endif; ?>
                        <?php foreach($accountyears as $index => $accountyear): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                
								<td><?php echo htmlspecialchars($accountyear['AccountYear']); ?></td>
								<td><?php echo htmlspecialchars($accountyear['IsDefault']); ?></td>
                                
                                <td align="center">
                                    <a href="aster-account-year.php?edit_id=<?php echo $accountyear['AYID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="aster-account-year.php?delete_id=<?php echo $accountyear['AYID']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this Year account?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
</body>
</html>
<?php $conn->close(); ?>
