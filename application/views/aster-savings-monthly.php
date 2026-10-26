<?php
session_start();
require_once '../php/general-functions.php';
require_once 'controller/SavingsMonthlyController.php';
$controller = new SavingsMonthlyController($conn);
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
$installmentamount = $data['InstallmentAmount'] ?? '';
$totalinterest = $data['totalinterest'] ?? '';
$maturedamount = $data['maturedamount'] ?? '';
//$bank = $data['bank'] ?? '';
//$upi = $data['upi'] ?? '';
$sdtype = $data['sdtype'] ?? '';
$member 	  = $data['member'] ?? null;
$memberid = $_REQUEST['id'] ?? '';

$Install_amount = FillAllInstallmentAmountMonthly($installmentamount); //call Installment amount function from general function
//$memberid = 1;
list($memberid,$fullname,$createddate,$dateofbirth,$occupation,$district,$state,$pincode,$nominee_name,$nominee_age,$nominee_relation) = getMemberFromMembersTable($memberid);
if(!empty($success))
	header('Location: search-account.php?tp=3&s='.$success);
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
    <title>ASTER - Monthly Account</title>
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
                <h2 class="page-header">Create New Account</h2>
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
                            <span aria-hidden="true"></span>
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
               <form method="post" action="aster-savings-monthly.php" enctype="multipart/form-data">
               <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars((string)$editId); ?>">
					<fieldset class="feild-set">
                		<div class="row">
                			<div class="col-md-3">
            					<div class="form-group">
                					<label>Member Id <span class="mandatory">*</span></label>
                 					<input type="text" class="form-control" name="member-id" value="<?php echo $memberid; ?>" readonly>
           		   				</div>
       			 			</div>
							<div class="col-md-3">
								<div class="form-group">
									<label> Member Name <span class="mandatory">*</span></label>
									<input type="text" name="member-name" class="form-control" value="<?php echo $fullname; ?>"
									readonly>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>Created Date </label>
									<input type="text" class="form-control date-picker" id="current-date" name="account-created-date" 
									style=" color:#3333CC; font-weight:bold; font-size:18px" 
									value="<?php echo date('d/m/Y'); ?>" readonly/>
								</div>
							</div>       
							<div class="col-md-3">
								<div class="form-group">
									<label> Savings Scheme Type <span class="mandatory">*</span></label>
									<select name="saving-type" id="saving-type" class="form-control" required>
										<option value="">Select SD Type</option>
										<option value="1">2 Year</option>
										<option value="2">3 Year</option>
										<option value="3">4 Year</option>
										<option value="4">5 Year</option>
											
									</select>
								</div>
							</div>
        				</div>
        				<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label> Interest Percentage <span class="mandatory">*</span></label>
									<input type="text" id="interest-percentage" name="interest-percentage" class="form-control">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label> InstallMent Amount:<span class="mandatory">*</span></label>
									<select name="installment-amount" id="installment-amount" class="form-control" required>
											<option value="">Select Installment Amount</option>
											<?php echo $Install_amount;?>
										</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label> Total Interest <span class="mandatory">*</span></label>
									<input type="text" id="total-interest" name="total-interest" class="form-control" readonly="" 
									style="font-size:20px; color:#FF0000; font-weight:bold">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label> Matured Amount <span class="mandatory">*</span></label>
									<input type="text" id="matured-amount" name="matured-amount" class="form-control" readonly=""
									style="font-size:20px; color:#FF0000; font-weight:bold">
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
							<div class="col-md-3" id="cashfield">
								<div class="form-group">
									<label for="role">Amount to Pay:</label>
									<span class="mandatory">*</span>
									<div id="cashFields" class="form-section">
										<input type="text" class="form-control allow_decimal higlight controlkey" id="cash-amount" name="cash-amount" 
										style="" placeholder=""  onKeyPress="return validateNumber(event)" maxlength="15" required value="<?php //print 						                            	$phone;?>"  onBlur="Check_balance($(this),$(mibalance));" autofocus required/>
									</div>
								</div>
							</div>
						</div>
            			<!-- if Cash is Selected -->
						<!-- if Cheque is selected -->
						<fieldset id="chequefield" class="feild-set">
						<div class="row"> 
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">Cheque Date:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control date-picker" id="current-date2" name="cheque-entered-date" style="" placeholder=
									"dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">Cheque Number:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control" id="cheque-number" name="cheque-number" 
									style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" required  />
								</div>
							</div>
						</div>
						<div class="row">	
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">From Bank:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control" id="cheque-from-bank" name="cheque-from-bank" 
									style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" required/>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">To Bank:<span class="mandatory">*</span></label></label>
									<select class="form-control dbfield selectpicker" id="cheque-to-bank" name="cheque-to-bank" required>
										<option value="">Select Bank</option>
										<?php 
											$bank_list = FillAllBank($bank);
											echo $bank_list;
										?>
									
									</select>
									<small id="cheque-bank-balance"style="color:red;font-weight:bold"></small>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Amount to Pay:<span class="mandatory">*</span></label>
									<input type="decimal" id="cheque-amount" name="cheque-amount" class="form-control" required
									autofocus>
								</div>
							</div>
						</div>
						</fieldset>
						<!--if Cheque is selected -->
						<!-- if rtgs is selected -->
						 <fieldset id="rtgsfield" class="feild-set">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">RTGS Date:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control date-picker" id="current-date3" name="rtgs-entered-date" style="" placeholder=
									"dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">RTGS/NEFT Details </label>
									<span class="mandatory">*</span>
									<select class="form-control dbfield selectpicker" id="rtgs-to-bank" name="rtgs-to-bank">
										<option value="">Select Bank</option>
										<?php 
											$bank_list = FillAllBank($bank);
											echo $bank_list;
										?>
									
									</select>
									<small id="rtgs-bank-balance"style="color:red;font-weight:bold"></small>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">Reference No: <span class="mandatory">*</span></label></label>
									<input type="text" class="form-control" id= "reference-number" name="reference-number" placeholder=" Reference No" required>
								</div>
							</div>
							<!--<div class="col-md-6">
								<div class="form-group">
									<label for="role">From Bank:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control" id="bank" name="bank" 
									style="" placeholder="Enter Bank Name" maxlength="50" value="<?php //print $phone;?>" required />
								</div>
							</div>-->
							<div class="col-md-6">
								<div class="form-group">
									<label>Amount to Pay:<span class="mandatory">*</span></label>
									<input type="decimal" id="rtgs-amount" name="rtgs-amount" class="form-control" required
									autofocus>
								</div>
							</div>
						</div>
						</fieldset>
						<!--  if UPI is selected -->
						<fieldset id="upifield" class="feild-set">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">UPI Date:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control date-picker" id="current-date4" name="upi-entered-date" style="" placeholder=
									"dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>"/>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">UPI Details</label><span class="mandatory">*</span>
									<select class="form-control" required id="upi-type" name="upi-type">
									  <option value="">Select UPI Type</option>
										<?php 
											$upi_list = FillAllUpi($upi);
											echo $upi_list 
										?>
									</select>  
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">UPI ID </label>
									<input type="text" class="form-control" id="upi-id" name="upi-id" placeholder="UPI ID" required>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">To Bank:<span class="mandatory">*</span></label></label>
									<select class="form-control dbfield selectpicker" id="upi-to-bank" name="upi-to-bank" required>
										<option value="">Select Bank</option>
										<?php 
											$bank_list = FillAllBank($bank);
											echo $bank_list; 
										?>
									
									</select>
									<small id="upi-bank-balance"style="color:red;font-weight:bold"></small>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="role">Amount to pay:</label>
									<span class="mandatory">*</span>
									<input type="text" class="form-control" id="upi-amount" name="upi-amount" 
									style="" placeholder="" maxlength="50" value="<?php //print $amount;?>" />
								 </div>
							</div>
						</div><!--  if UPI is selected -->
						</fieldset>
 						<div class="form-group" style="text-align:center">
  							<button type="submit" id="save-button" name="account-monthly-submit" class="btn btn-success"> Save</button>
                 		</div>
				 </fieldset>
            </form>
            </div>

        <br><br>
		<?php
			$show_value = 0;
			if ($show_value > 0) {
		?>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
               <table id="course-type-details" class="table table-striped table-bordered" cellspacing="0">
                    <thead>
                        <tr class="header-row">
                           <th>SlNo</th>
         			       <th>MemberId</th>
           			       <th>Member Name</th>
                           <th>Created Fate</th>
          			       <th>Installment Amount </th>
          			       <th>Total Interest </th>
                           <th>Matured Amount </th>
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
		<?php }?>
       </div> 
    </div>
    <?php include_once "footer.php"; ?>
</div>
<script src="js/jquery-1.9.1.min.js"></script>
<script>
if (typeof $.fn.combobox !== 'function')
{
    $.fn.combobox = function()
    {
        return this;
    };
}
</script>
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

<!--Ajax for auto value for total interest and matured amount code start-->
<script>
$(document).ready(function () {

    function calculateAmounts() {
        let savingType =
            $('#saving-type').val();
        let interestPercentage =
            $('#interest-percentage').val();
        let installmentAmount =
    		$('#installment-amount option:selected').data('amount');
		let dsid =
    		$('#installment-amount').val();

        console.log("VALUES:");
        console.log(savingType);
        console.log(interestPercentage);
        console.log(installmentAmount);
        $.ajax({
            url: 'controller/ajax/ajax-calculate-interest-monthly.php',
            type: 'POST',
            data: {
                saving_type: savingType,
                interest_percentage:interestPercentage,
                installment_amount:installmentAmount,
				dsid: dsid
            },
            dataType: 'json',
            success: function(response) {
                console.log("SUCCESS");
                console.log(response);
                $('#total-interest').val(
                    response.total_interest
                );
                $('#matured-amount').val(
                    response.matured_amount
                );
            },
            error: function(xhr, status, error) {
                console.log("ERROR");
                console.log(xhr.responseText);
                console.log(status);
                console.log(error);
            }
        });
    }
    $('#saving-type').change(calculateAmounts);
    $('#interest-percentage').keyup(calculateAmounts);
    $('#installment-amount').change(calculateAmounts);
});
</script>
<!--Ajax for auto value for total interest and matured amount code end-->


<!-------------------------script for payment mode code start----------------------->
<script>
const paymentModeRadios = document.querySelectorAll(
    'input[name="cash-cheque-rtgs-upi"]'
);
const cashSection   = document.getElementById('cashfield');
const chequeSection = document.getElementById('chequefield');
const rtgsSection   = document.getElementById('rtgsfield');
const upiSection    = document.getElementById('upifield');
const allSections = [
    cashSection,
    chequeSection,
    rtgsSection,
    upiSection
];
function resetSections() {
    allSections.forEach(section => {
        section.classList.add('hidden');
        // Disable full fieldset
        section.disabled = true;
        // Remove required
        section.querySelectorAll('input, select, textarea').forEach(el => {
            el.required = false;
        });
    });
}

function activateSection(section) {
    section.classList.remove('hidden');
    // Enable fieldset
    section.disabled = false;
    // Add required only to editable fields
    section.querySelectorAll('input, select, textarea').forEach(el => {
        if (
            el.type !== 'hidden' &&
            el.type !== 'radio' &&
            !el.readOnly &&
            !el.disabled
        ) {
            el.required = true;
        }
    });
}
function updateFields() {
    resetSections();
    const selected = document.querySelector(
        'input[name="cash-cheque-rtgs-upi"]:checked'
    ).value;
    switch (selected) {
        case '1':
            activateSection(cashSection);
            break;
        case '2':
            activateSection(chequeSection);
            break;
        case '3':
            activateSection(rtgsSection);
            break;
        case '4':
            activateSection(upiSection);
            break;
    }
}

// Initial load
updateFields();

// Change event
paymentModeRadios.forEach(radio => {
    radio.addEventListener('change', updateFields);
});
</script>
<!-------------------------script for payment mode code end----------------------->

<style>
.hidden{
    display:none;
}
</style> 

<!-------------------------script for payment mode amount validation code start----------------------->
<script>
$('#cash-amount,#cheque-amount,#rtgs-amount,#upi-amount').on('blur', function () {

    let amount = parseFloat($(this).val()) || 0;

    if ($(this).val() == '') {
        return;
    }

    // Get selected installment amount text
    let dailySavings = parseFloat(
        $('#installment-amount option:selected').text()
    ) || 0;

    if (dailySavings <= 0) {
        alert('Daily Savings Amount not found');
        return;
    }

    if (amount % dailySavings !== 0) {

        alert(
            'Amount should be a multiple of Daily Savings Amount (' +
            dailySavings + ')'
        );

        $(this).val('');
        $(this).focus();
    }
});
</script> 
<!-------------------------script for payment mode amount validation code end----------------------->
</body>
</html>
<?php $conn->close(); ?>
