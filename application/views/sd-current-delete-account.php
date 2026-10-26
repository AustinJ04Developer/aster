<?php
session_start();
require_once 'controller/CurrentSDController.php';
$controller = new CurrentSDController($conn);
$data = $controller->handleRequest();
$current_account_no = $data['current_account_no'];
//print_r($data);

$searchmemberaccountdelete = $data['searchmemberaccountdelete'] ?? [];
$errors = $data['errors'] ?? [];
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
                <h2 class="page-header">Delete Accounts</h2>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
            </div>
        </div>
		<div class="row">
			<?php if(isset($_SESSION['success'])): ?>
			<div class="alert alert-success">
				<?= $_SESSION['success']; ?>
			</div>
			<?php unset($_SESSION['success']); ?>
			<?php endif; ?>
		
			<?php if(isset($_SESSION['error'])): ?>
				<div class="alert alert-danger">
					<?= $_SESSION['error']; ?>
				</div>
			<?php unset($_SESSION['error']); ?>
			<?php endif; ?>
		</div>
        <div class="row">
          <form method="post" action="sd-current-delete-account.php"> 
		  <fieldset class="feild-set"> 
          <div class="row">
         	 <div class="col-md-6">
             	<div class="form-group">
              <!-- form group -->
              	<label for="searchby">Select Account Number:</label>
              	<span class="mandatory">*</span>
                  <select class="form-control dbfield selectpicker" id="selectedfields" name="account-search-field">
                      <option value="">Select Account Number</option>

					<?php if(!empty($current_account_no)) : ?>
                
                        <?php foreach($current_account_no as $row): ?>
                
                             <option value="<?php echo $row['SCID']; ?>">
           				 					<?php echo $row['SCNO']; ?> - <?php echo $row['MemberName']; ?>
        				</option>
                
                        <?php endforeach; ?>
                
                    <?php endif; ?>		
                 </select>
              </div>
            <!-- ./ form group -->
          </div><br>
          <div class="form-group">
            <button class='btn btn-md btn-info' type="submit" name="search-submit-sd-delete-account" id="search-button" >Search</button>
          </div>
          </div>
		  </fieldset>
                </form>
            </div>
        <br><br>
		
		<?php if(!empty($searchmemberaccountdelete)) { ?>
        <div class="row">
            <div class="col-md-12">
                <table id="search-details" class="table table-striped table-bordered">
                    <thead>
                        <tr class="header-row">
                            <th>SlNo</th>
                            <th>Member Id</th>
                            <th>Member Name</th>
                            <th>SCNO</th>
                            <th>Amount</th>
                            <th>Delete Account</th>
                        </tr>
                    </thead>
                   <tbody>
						<?php if(!empty($searchmemberaccountdelete)) : ?>
						
							<?php foreach ($searchmemberaccountdelete as $key => $member): ?>
						
								<tr>
						
									<td><?= $key + 1 ?></td>
									<td><?= $member['MemberId'] ?></td>
                                    <td><?= $member['MemberName'] ?></td>
									<td><?= $member['SCNO'] ?></td>
									<td><?= number_format($member['Amount'],2) ?></td>
									<td>
										<center>
											<a href="sd-current-delete-account.php?delete_current=1&scid=<?= $member['SCID'] ?>"
											   class="btn btn-danger btn-sm"
											   onclick="return confirm('Are you sure to delete this payment?')">
											   Delete
											</a>
										</center>
									</td>
						
								</tr>
						
							<?php endforeach; ?>
						
						<?php endif; ?>
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
    $('#search-details').DataTable({
        "paging": true,
        "ordering": true,
        "info": true
    });
});
</script>

