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
$savings_current = $data['savings_current'] ?? [];
$membername   = $data['membername'] ?? '';
$createddate  = $data['createddate'] ?? '';
$amount       = $data['amount'] ?? '';
$remarks       = $data['remarks'] ?? '';
$bank = $data['bank'] ?? '';
$upi = $data['upi'] ?? '';
$member 	  = $data['member'] ?? null;
$memberid = $_REQUEST['id'] ?? '';
$getAllUpi = FillAllUpi($upi);
$getAllBanks = FillAllBank($bank);
list($memberid,$fullname,$createddate,$dateofbirth,$occupation,$district,$state,$pincode,$nominee_name,$nominee_age,$nominee_relation) = getMemberFromMembersTable($memberid);
//if (!empty($success)) {
   // header("Location: search-account.php?tp=1");
  //  exit;
//}
if (!empty($success)) {
    header("Location: search-account.php?tp=1&s=" . urlencode($success));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ASTER - Current Account</title>
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
                <form method="post" action="create-current-sd-account.php" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
                <fieldset class="feild-set">
                <div class="row">
                <div class="col-md-4">
            		<div class="form-group">
                		<label>MemberId <span class="mandatory">*</span></label>
                 		<input type="text" class="form-control" name="member_id" value="<?php echo $memberid; ?>" readonly>
           		   </div>
       			 </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> MemberName <span class="mandatory">*</span></label>
                <input type="text" name="member_name" class="form-control" value="<?php echo $fullname; ?>"
       			readonly>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>CreatedDate </label>
                <input type="text" class="form-control" id="current-date" name="current-date"  placeholder=
                            "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
                
            </div>
        </div>
        
        </div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
				  <label for="role">Payment Mode :</label>
				  <span class="mandatory">*</span>
				  	<div class="radio">
					<label>
					<input type="radio" name="cash-cheque-rtgs-upi" id="cash" value="1" checked="checked"  onClick="showHideCashChequeSD(1);">
					Cash </label>
					<label style="margin-left:10px;">
					<input type="radio" name="cash-cheque-rtgs-upi" id="selected-cheque" value="2" onClick="showHideCashChequeSD(2);">
					Cheque </label>
					<label style="margin-left:10px;">
					<input type="radio" name="cash-cheque-rtgs-upi" id="selected-rtgs" value="3" onClick="showHideCashrtgsSD(3);">
					RTGS </label>
                    <label style="margin-left:10px;">
					<input type="radio" name="cash-cheque-rtgs-upi" id="selected-upi" value="4" onClick="showHideCashupiSD(4);">
					UPI </label>
				  </div>
				</div>
			</div>	
               
            <!-- if Cash is selected -->
            <div class="col-md-4" id="cashfield">
              	<div class="form-group">
                  <label for="role">Amount:</label>
                  <span class="mandatory">*</span>
                 	 <div id="cashFields" class="form-section">
		             	<input type="text" class="form-control allow_decimal higlight controlkey" id="sd-amount" name="sd-amount" 
                            style="" placeholder=""  onKeyPress="return validateNumber(event)" maxlength="15" required value="<?php //print 						                            $phone;?>"  onBlur="Check_balance($(this),$(mibalance));" autofocus/>
                    </div>
           		 </div>
            </div>
            
            </div>
            <!-- if Cash is Selected -->
            <!-- if Cheque is selected -->
            <fieldset id="chequefield" class="feild-set">
          		<div class="row" > 
            <div class="col-md-4">
              <div class="form-group">
                <label for="role">Cheque Date:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control" id="current-date2" name="cheque-entered-date"  placeholder=
                            "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="form-group">
                <label for="role">Cheque Number:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control" id="cheque-number" name="cheque-number" 
                            style="" placeholder="" maxlength="50" value="<?php //print $;?>" required  />
              </div>
            </div>
                <input type="hidden" class="form-control date-picker" id="current-date1" name="cheque-collecton-date" style="" placeholder=
                            "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>" readonly />
           <div class="col-md-4">
              <div class="form-group">
                <label for="role">From Bank:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control" id="from-bank" name="cheque-from-bank" 
                            style="" placeholder="Enter Bank Name" maxlength="50" value="<?php //print $phone;?>" required />
              </div>
            </div>
           
            <div class="col-md-4">
              <div class="form-group">
                <label for="role">To Bank:</label>
                <span class="mandatory">*</span>
                <select name="cheque-to-bank" class="form-control" required>
       				 	<option value="">Select Bank</option>
        				<?php echo $getAllBanks;?>
     			    </select>
              </div>
            </div>
            <div class="col-md-4">
            <div class="form-group">
                <label> Amount <span class="mandatory">*</span></label>
                <input type="decimal" name="cheque-amount" class="form-control" required
                        autofocus>
            </div>
        </div>
          </div>
          </fieldset>
          <!--if Cheque is selected -->
          <!-- if rtgs is selected -->
          <fieldset id="rtgsfield" class="feild-set">
          <div class="row">
          <div class="col-md-3">
              <div class="form-group">
                <label for="role">RTGS Date:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control date-picker" id="current-date3" name="rtgs-entered-date" style="" placeholder=
                            "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
              </div>
            </div>
          
			<div class="col-md-3">
             <div class="form-group">
             <label for="role">Reference No: </label>
     			 <input type="text" class="form-control" name="reference-number" placeholder=" Reference No" required>
             	 </div>
              </div>
             
            <div class="col-md-3">
              <div class="form-group">
                <label for="role">To Bank:</label>
                <span class="mandatory">*</span>
                <select name="rtgs-to-bank" class="form-control" required>
       				 	<option value="">Select Bank</option>
        				<?php echo $getAllBanks;?>
     			    </select>
              </div>
            </div>
              <div class="col-md-3">
              <div class="form-group">
                <label for="role">Amount:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control" id="rtgs-amount" name="rtgs-amount" 
                            style="" placeholder="" maxlength="50" value="<?php //print $amount;?>" />
              </div>
            </div>
            </div>
            </fieldset>

          <!--  if UPI is selected -->
          <fieldset id="upifield" class="feild-set">
        	<div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="role">UPI Entered Date:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control date-picker" id="current-date4" name="upi-entered-date" style="" placeholder=
                            "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
              </div>
            </div>
           		<div class="col-md-4">
              		<div class="form-group">
                            <label for="role">UPI Details</label><span class="mandatory">*</span>
      						<select name="upi-type" class="form-control" required>
       				 	<option value="">Select UPI</option>
        				<?php echo $getAllUpi;?>
     			    </select>
                    </div>
                </div>
                <div class="col-md-4">
              		<div class="form-group">
                    <label for="role">UPI ID </label>
      					<input type="text" class="form-control" name="upi-id" placeholder="UPI ID" required>
             	   </div>
                </div>
            <div class="col-md-4">
              <div class="form-group">
              	<label for="role">To Bank </label>
               	<span class="mandatory">*</span>
                
      				<select name="upi-to-bank" class="form-control" required>
       				 	<option value="">Select Bank</option>
        				<?php echo $getAllBanks;?>
     			    </select>
              </div>
          </div>
           <div class="col-md-4">
              <div class="form-group">
                <label for="role">Amount:</label>
                <span class="mandatory">*</span>
                <input type="text" class="form-control" id="upi-amount" name="upi-amount" 
                            style="" placeholder="" maxlength="50" value="<?php //print $amount;?>" />
              </div>
            </div>
          </div>
          </fieldset>
          <!--  if UPI is selected -->
          <div class="row">
          <div class="col-md-3">
              <div class="form-group">
                <label for="role">Date Of Birth</label>
                <input type="text" class="form-control date-picker" id="current-date2" name="dob" style="" placeholder=
                            "dd/mm/yyyyy" value="<?php echo $dateofbirth; ?>" readonly />
              </div>
            </div>
            <div class="col-md-3">
           		 <div class="form-group">
                	<label> Occupation </label>
                	<input type="text" name="occupation" class="form-control" value="<?php echo $occupation; ?>"
       			readonly>
           	 </div>
       	 </div>
         	 <div class="col-md-3">
           		 <div class="form-group">
                	<label> District </label>
                	<input type="text" name="district" class="form-control" value="<?php echo $district; ?>"
       			readonly>
           	 </div>
       	 </div>
         <div class="col-md-3">
           		 <div class="form-group">
                	<label> State </label>
                	<input type="text" name="state" class="form-control" value="<?php echo $state; ?>"
       			readonly>
           	 </div>
       	 </div>
         <div class="col-md-3">
           		 <div class="form-group">
                	<label> PinCode </label>
                	<input type="text" name="pin_code" class="form-control" value="<?php echo $pincode; ?>"
       			readonly>
           	 </div>
       	 </div>
         <div class="col-md-3">
           		 <div class="form-group">
                	<label> NomineeName </label>
                	<input type="text" name="nominee_name" class="form-control" value="<?php echo $nominee_name; ?>"
       			readonly>
           	 </div>
       	 </div>
          <div class="col-md-3">
           		 <div class="form-group">
                	<label> NomineeAge </label>
                	<input type="text" name="nominee_age" class="form-control" value="<?php echo $nominee_age; ?>"
       			readonly>
           	 </div>
       	 </div>
         <div class="col-md-3">
           		 <div class="form-group">
                	<label> Nominee Relation </label>
                	<input type="text" name="nominee_relation" class="form-control" value="<?php echo $nominee_relation; ?>"
       			readonly>
           	 </div>
       	 </div>
        </div>
 <div class="form-group" style="text-align:center">
  <button type="submit" id="save-button" name="account-submit" class="btn btn-success"> Save</button>
                    <?php if ($editId > 0): ?>
                        <a href="search-account.php?tp=1" class="btn btn-danger">Cancel </a>
                    <?php endif; ?>
                 </div>
               </div>
               </fieldset>
            </form>
            </div>

        <br><br>
        
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
