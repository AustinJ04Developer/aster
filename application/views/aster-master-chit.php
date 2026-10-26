<?php
session_start();
require_once 'controller/ChitController.php';
$controller = new ChitController($conn);
$data = $controller->handleRequest();
$chits = $data['chits'];
$editId = $data['editId'];
$userid = $data['userid'];
$monthno = $data['monthno'];
$monthlyamount = $data['monthlyamount'];
$chitamount = $data['chitamount'];
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
    <title>ASTER - Manage Chits</title>
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
                <h2 class="page-header">Manage Chit</h2>
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
                            <span aria-hidden="true">�</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">�</span>
                        </button>
                    </div>
                <?php endif; ?>
                <form method="post" action="aster-master-chit.php" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
                <div class="row">
    	

        <div class="col-md-3">
            <div class="form-group">
                <label>Month No <span class="mandatory">*</span></label>
                <input type="number" name="month_num" class="form-control" required
                       value="<?php echo htmlspecialchars($monthno); ?>">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Monthly Amount <span class="mandatory">*</span></label>
                <input type="number" name="month_amount" class="form-control" required
                       value="<?php echo htmlspecialchars($monthlyamount); ?>">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Chit Amount <span class="mandatory">*</span></label>
                <input type="number" name="chit_amount" class="form-control" required
                       value="<?php echo htmlspecialchars($chitamount); ?>">
            </div>
        </div>

    </div>
                    <a href="aster-master-chit.php" class="btn btn-primary">New</a>
                    <button type="submit" id="save-button" name="chit-submit" class="btn btn-success">
					<?php echo $editId > 0 ? 'Update' : 'Add'; ?> Chit</button>
                    <?php if ($editId > 0): ?>
                        <a href="aster-master-chit.php" class="btn btn-danger">Cancel Edit</a>
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
         			       <th>Month No</th>
           			       <th>Monthly Amount</th>
          			       <th>Chit Amount</th>
          			       <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-coloum-style">
                        <?php foreach($chits as $index => $chit): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                
								<td align="center"><?php echo htmlspecialchars($chit['MonthNo']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($chit['MonthlyAmount']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($chit['ChitAmount']); ?></td>
                                <td align="center" align="center">
                                    <a href="aster-master-chit.php?edit_id=<?php echo $chit['CHID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="aster-master-chit.php?delete_id=<?php echo $chit['CHID']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this course type?');">Delete</a>
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
