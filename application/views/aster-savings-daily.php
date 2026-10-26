<?php
session_start();
require_once '../php/general-functions.php';
require_once 'controller/CurrentSDController.php';
/* ---- Run Controller Properly ---- */
$controller = new CurrentSDController($conn);
$data = $controller->handleRequest();
/* ---- Safe Data Extraction ---- */
$success      = $data['success'] ?? '';
$errors       = $data['errors'] ?? [];
$editId       = $data['editId'] ?? 0;
$savings_daily = $data['savings_daily'] ?? [];
$membername   = $data['membername'] ?? '';
$createddate  = $data['createddate'] ?? '';
$savingstype  = $data['savingstype'] ?? '';
$interestpercentage = $data['interestpercentage'] ?? '';
$installamount = $data['installamount'] ?? '';
$totalinterest = $data['totalinterest'] ?? '';
$maturedamount = $data['maturedamount'] ?? '';
$sdtype = $data['sdtype'] ?? '';
$member 	  = $data['member'] ?? null;
$memberid = $_REQUEST['id'] ?? '';
$getAllSDTypes = FillSDType($sdtype);
$getAllInstallmentAmount = FillInstallmentAmount($installamount);
list($memberid,$fullname,$createddate,$dateofbirth,$occupation,$district,$state,$pincode,$nominee_name,$nominee_age,$nominee_relation) = getMemberFromMembersTable($memberid);
if(!empty($success))
	header('Location: search-account.php?tp=1&s='.$success);
//print $memberid;
//print $fullname;
//print $createddate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Daily Account</title>
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
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Manage Account</h2>
                <hr class="hr-background">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
            </div>
        </div>
        <div class="row">
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
               <form method="post" action="aster-savings-daily.php" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
                <div class="row">
                <div class="col-md-3">
            		<div class="form-group">
                		<label>Member Id <span class="mandatory">*</span></label>
                 		<input type="text" class="form-control" name="member_id" value="<?php echo $memberid; ?>" readonly>
           		   </div>
       			 </div>
        <div class="col-md-3">
            <div class="form-group">
                <label> Member Name <span class="mandatory">*</span></label>
                <input type="text" name="member_name" class="form-control" value="<?php echo $fullname; ?>"
       			readonly>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Created Date </label>
                <input type="text" class="form-control date-picker" id="current-date" name="current-date" style="" placeholder=
								"dd/mm/yyyy" value="<?php echo date('d/m/Y'); ?>" readonly/>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="form-group">
                <label> Savings Type <span class="mandatory">*</span></label>
                <select name="savings-type" class="form-control" required>
       				 	<option value="">Select SD Type</option>
        				<?php echo $getAllSDTypes;?>
     			    </select>
            </div>
        </div>
        </div>
        <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label> Interest Percentage <span class="mandatory">*</span></label>
                <input type="text" name="interest-percentage" class="form-control" value="<?php echo $interestpercentage ; ?>">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label> InstallMent Amount <span class="mandatory">*</span></label>
                <select name="install-amount" class="form-control" required>
       				 	<option value="">Select InstallmentAmount</option>
        				<?php echo $getAllInstallmentAmount;?>
     			    </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label> Total Interest <span class="mandatory">*</span></label>
                <input type="text" name="total-interest" class="form-control" value="<?php echo $totalinterest ; ?>">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label> Matured Amount <span class="mandatory">*</span></label>
                <input type="text" name="matured-amount" class="form-control" value="<?php echo $maturedamount ; ?>">
            </div>
        </div>
        </div>
 <div class="form-group" style="text-align:center">
  <button type="submit" id="save-button" name="account-daily-submit" class="btn btn-success"> Save</button>
                    <?php if ($editId > 0): ?>
                        <a href="controller/model/search-account.php?tp=1" class="btn btn-danger">Cancel </a>
                    <?php endif; ?>
                 </div>
                 </div>
            </form>
            </div>

        <br><br>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
               <table id="course-type-details" class="table table-striped table-bordered" cellspacing="0">
                    <thead>
                        <tr class="header-row">
                           <th>SlNo</th>
         			       <th>MemberId</th>
           			       <th>MemberName</th>
                           <th>Createddate</th>
          			       <th>InstallMentAmount </th>
          			       <th>TotalInterest </th>
                           <th>MaturedAmount </th>
                           <th>Actions </th>
                        </tr>
                    </thead>
                    <tbody class="table-coloum-style">
                        <?php foreach($savings_daily as $index => $savings): ?>
                            <tr>
                                <td align="center"><?php echo $index + 1; ?></td>
                                
								<td align="center"><?php echo htmlspecialchars($savings['MemberId']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($savings['MemberName']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($savings['CreatedDate']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($savings['InstallMentAmount']); ?></td>
                                <td align="right"><?php echo htmlspecialchars($savings['TotalInterest']); ?></td>
                                 <td align="right"><?php echo htmlspecialchars($savings['MaturedAmount']); ?></td>
                                <td align="center">
                                    <a href="aster-savings-daily.php?delete_id=<?php echo $savings['DSID']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
<style>
.hidden { display:none; }
</style>

<script>
const paymentModeRadios = document.querySelectorAll('input[name="cash-cheque-rtgs-upi"]');

const cashSection   = document.getElementById('cashfield');
const chequeSection = document.getElementById('chequefield');
const rtgsSection   = document.getElementById('rtgsfield');
const upiSection    = document.getElementById('upifield');

function resetSections() {
  [cashSection, chequeSection, rtgsSection, upiSection].forEach(section => {
    section.classList.add('hidden');
    section.querySelectorAll('input, select').forEach(el => {
      el.removeAttribute('required');
    });
  });
}

function activateSection(section) {
  section.classList.remove('hidden');
  section.querySelectorAll('input, select').forEach(el => {
    el.setAttribute('required', 'required');
  });
}
function showHideCashChequeSD() {

  document.getElementById("chequefield").style.borderStyle = "groove";
}
function showHideCashrtgsSD() {

  document.getElementById("rtgsfield").style.borderStyle = "groove";
}
function showHideCashupiSD() {

  document.getElementById("upifield").style.borderStyle = "groove";
}
function updateFields() {
  const selected = document.querySelector(
    'input[name="cash-cheque-rtgs-upi"]:checked'
  ).value;

  resetSections();

  if (selected === '1') activateSection(cashSection);
  if (selected === '2') activateSection(chequeSection);
  if (selected === '3') activateSection(rtgsSection);
  if (selected === '4') activateSection(upiSection);
}

updateFields();
paymentModeRadios.forEach(r => r.addEventListener('change', updateFields));
</script>

  
</body>
</html>
<?php $conn->close(); ?>
