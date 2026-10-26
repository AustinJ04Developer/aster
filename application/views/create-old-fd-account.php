<?php
   	ob_start();
	session_start();
	if(!isset($_SESSION['UserId']))
		header('Location: index.php');
    //include_once "../php/general-functions.php";
	include_once "edit-alert.php";
	include_once "../php/global-settings.php";
	include_once "../php/global-functions.php";
	include "../settings/config.php";
	global $dbh;
	

function FindMaxFDNOCreate() // 1->sd, 2->fd, ...
{
	global $dbh;
	try
	{
			$sql = "SELECT MAX(SDFNO) AS SDFNO
				FROM ast_savings_fixed
				WHERE 1";
		$sql_query = $dbh->query($sql);
		$sql_query->setFetchMode(PDO::FETCH_ASSOC);
	    $result = $sql_query->fetch();
		$fdno= $result["SDFNO"];
		$fdno += 1;
		/*print_r($arr_date);
		exit;*/
		return $fdno;
		}
	catch(PDOException $e)
	{
     ErrorTracking('create-old-fd-account','FindMaxFDNOCreate',$e->getMessage());
	}

}

$fdno = FindMaxFDNOCreate();

$selected_val = '';
function FillMemberId($selected_val)
{
global $dbh;
	try
	{
		$sql = "SELECT MemberCode,MemberId    
				FROM  ast_members WHERE 1";
		$query_course = $dbh->query($sql);
		$query_course->setFetchMode(PDO::FETCH_ASSOC);
		$option = '';
	    while ($result = $query_course->fetch())
		{
			  if($selected_val == $result['MemberId'])
			  {
			  	$option .= '<option value="'.$result['MemberId'].'" selected>'.$result['MemberCode'].'</option>';
			  }
			  else
			  {
			  	$option .= '<option value="'.$result['MemberId'].'">'.$result['MemberCode'].'</option>';
			  }
		}
		return $option;
	}
	catch (PDOException $e)
	{
		$e->getMessage();
		//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
	}
}

$member_list = FillMemberId($selected_val);



if(isset($_POST['save-old-fd']))
{

	$member_id = addslashes($_POST['member-id']);
	$member_name = addslashes($_POST['member-name']);
	$create_date = checkIsEmpty($_POST['created-date']);
	$saving_type = $_POST['saving-type'];
	$interest_percentage = addslashes($_POST['interest-percentage']);
	$fd_amount = addslashes($_POST['fd-amount']);
	$payment_mode = $_POST['cash-cheque-rtgs-upi'];
	$cheque_date = $_POST['cheque-date'];
	$cheque_amount = addslashes($_POST['cheque-amount']);
	$cheque_num = addslashes($_POST['cheque-number']);
	$cheque_from_bank = addslashes($_POST['cheque-from-bank']);
	$cheque_to_bank = addslashes($_POST['cheque-to-bank']);
	$rtgs_date = $_POST['rtgs-date'];
	$rtgs_amount = addslashes($_POST['rtgs-amount']);
	$rtgs_num = addslashes($_POST['rtgs-number']);
	$rtgs_from_bank = addslashes($_POST['rtgs-from-bank']);
	$rtgs_to_bank = addslashes($_POST['rtgs-to-bank']);
	$upi_date = $_POST['upi-date'];
	$upi_type = $_POST['upi-type'];
	$upi_amount = addslashes($_POST['upi-amount']);
	$upi_trans_num = addslashes($_POST['upi-transaction-number']);
	$total_interest = addslashes($_POST['total-interest']);
	$matured_amount = addslashes($_POST['matured-amount']);
	$remarks = addslashes($_POST['remarks']);
	$is_old_data_entry = 1;
	$current_date = checkIsEmpty(date('d/m/Y'));
	
	$sql = "SELECT AYID,AccountYear FROM ast_account_year WHERE IsDefault = 1";
	$query_course = $dbh->query($sql);
	$query_course->setFetchMode(PDO::FETCH_ASSOC);
	$result = $query_course->fetch();
	
	$ayid = $result['AYID'];
	$atmid = 4;
	$transaction_type = 1;
	$acode = 1;
	$asubcode = 2;
	$interest_type = 1;
	$user_id = $_SESSION['UserId'];

	
	$success = InsertFDAccount($member_id,$member_name,$create_date,$saving_type,$interest_percentage,$fd_amount,$payment_mode,$cheque_date,$cheque_amount,
				$cheque_num,$cheque_from_bank,$cheque_to_bank,$rtgs_date,$rtgs_amount,$rtgs_num,$rtgs_from_bank,$rtgs_to_bank,$upi_date,$upi_type,
				$upi_amount,$upi_trans_num,$total_interest,$matured_amount,$remarks,$fdno,$is_old_data_entry,$current_date,$ayid,$atmid,$transaction_type,
				$acode,$asubcode,$interest_type,$user_id);
	
	if($success)
    	header('Location: search-fd-account-old.php?s=1'); 
	else 
    	header('Location: search-fd-account-old.php?s=2');
}

function InsertFDAccount($member_id,$member_name,$create_date,$saving_type,$interest_percentage,$fd_amount,$payment_mode,$cheque_date,$cheque_amount,
				$cheque_num,$cheque_from_bank,$cheque_to_bank,$rtgs_date,$rtgs_amount,$rtgs_num,$rtgs_from_bank,$rtgs_to_bank,$upi_date,$upi_type,
				$upi_amount,$upi_trans_num,$total_interest,$matured_amount,$remarks,$fdno,$is_old_data_entry,$current_date,$ayid,$atmid,$transaction_type,
				$acode,$asubcode,$interest_type,$user_id)
{
	global $dbh;
	
	$sql1 = "INSERT INTO ast_savings_fixed(SDFNO,MemberId,MemberName,CreatedDate,SavingsType,InterestPercentage,Amount,TotalInterest,MaturedAmount,
												IsOldDataEntry,OldDataEntryDate,Remarks)
												 VALUES (
															".$fdno.",
															".$member_id.",
															'".$member_name."',
															'".$create_date."',
															".$saving_type.",
															".$interest_percentage.",
															".$fd_amount.",
															".$total_interest.",
															".$matured_amount.",
															".$is_old_data_entry.",
															'".$current_date."',
															'".$remarks."'
															)";
			/*print $sql1;
			exit;*/
	$success =  $dbh->exec($sql1);
	
	$sql2 = "INSERT INTO ast_transaction(Date,AccountNo,Amount,AYID,ATMID,TransactionType,TransactionMethod,ACode,ASubCode,InterestType,
											Remarks,UserId)
										VALUES(
												'".$create_date."',
												".$fdno.",
												".$fd_amount.",
												".$ayid.",
												".$atmid.",
												".$transaction_type.",
												".$payment_mode.",
												".$acode.",
												".$asubcode.",
												".$interest_type.",
												'".$remarks."',
												".$user_id."
												)";
												
			/*print $sql2;
			exit;*/
	$success =  $dbh->exec($sql2);
	$last_tid_fixed = $dbh->lastInsertId();
	
	if($payment_mode == 1)
	{
		$sql3 = "INSERT INTO ast_transaction_method(TID,MethodType,Amount)
											VALUES(
													".$last_tid_fixed.",
													".$payment_mode.",
													".$fd_amount."
													)";
	}
	else if($payment_mode == 2)
	{
		$sql3 = "INSERT INTO ast_transaction_method(TID,MethodType,Amount,ChequeDate,FromBank,ToBank,ChequeNumber)
											VALUES(
													".$last_tid_fixed.",
													".$payment_mode.",
													".$fd_amount.",
													'".$cheque_date."',
													'".$cheque_from_bank."',
													'".$cheque_to_bank."',
													'".$cheque_num."'
													)";
	}
	else if($payment_mode == 3)
	{
		$sql3 = "INSERT INTO ast_transaction_method(TID,MethodType,Amount,ChequeDate,FromBank,ToBank,ChequeNumber)
											VALUES(
													".$last_tid_fixed.",
													".$payment_mode.",
													".$fd_amount.",
													'".$rtgs_date."',
													'".$rtgs_from_bank."',
													'".$rtgs_to_bank."',
													'".$rtgs_num."'
													)";
	}
	else
	{
		$sql3 = "INSERT INTO ast_transaction_method(TID,MethodType,Amount,UPIType,UPITransactionId)
											VALUES(
													".$last_tid_fixed.",
													".$payment_mode.",
													".$fd_amount.",
													".$upi_type.",
													'".$upi_trans_num."'
													)";
	}
	$success =  $dbh->exec($sql3);
	
	
	$month = date('m');
	$year = date('Y');
	
	
	$sql4 = "INSERT INTO ast_savings_paid_details(SavingsAccountNo,ATMID,MemberId,CurrentDate,Amount,Month,Year,TID,EnteredBy)
											VALUES(
													".$fdno.",
													".$atmid.",
													".$member_id.",
													'".$current_date."',
													".$fd_amount.",
													".$month.",
													".$year.",
													".$last_tid_fixed.",
													".$user_id."
											)";
	$success =  $dbh->exec($sql4);
	return $success;

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<title>MEDS-ASTER</title>
<!-- Bootstrap Core CSS -->
<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/mpdn.css" rel="stylesheet">
<!-- Custom Fonts -->
<link href="css/datepicker.css" rel="stylesheet" type="text/css">
<link href="css/data-table.css" rel="stylesheet" type="text/css">
<style>
#rtgs{display:none;}
#upi{display:none;}


</style>
</head>
<body>
<div id="wrapper">
  <?php include_once "header.php"; ?>
  <?php
		    include_once "header.php";
		    list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage(); //php/general-functions.php
			if($page_access_permission == 0)
			{
				/*print PageAuthorizationMessage();
				exit;*/
			}
		?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">Create Savings Fixed Account</h2>
        <hr class="hr-background">
      </div>
      <!-- /.col-lg-12 -->
    </div>
	 
    <!-- /.row -->
    <div class="row">
      <div class="col-lg-12">
        <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
      </div>
      <!-- /.col-lg-12 -->
    </div>
	
	
	
    <div class="row">
      <!-- First Row -->
      <div class="col-md-12 col-sm-12 col-xs-12">
        <fieldset id="major-feild-set" style="margin-top:15px;">
        <form role="frm-fd" method="post" id="current-form" action="" enctype="multipart/form-data" autocomplete="off">
       	 	<div class="row">
            	<div class="col-md-4">
              		<div class="form-group">
                  		<label for="role">FD Account Number:</label>
                  		<span class="mandatory">*</span><br>
                  		<label id="account-no">FD-<?php echo $fdno;?></label>
                	</div>
              	</div>
			</div>
			<div class="row">
				<div class="col-md-3">
                	<div class="form-group">
                  		<label for="InputCourse">MemberId:<span class="mandatory">&nbsp;*</span></label>
                  		<select class="form-control combobox memberid" id="member-id" name="member-id" required tabindex="1" onChange="getMemberName(this.value);">
						  <option disabled selected></option>
						 <?php print $member_list; ?>
				  		</select>
					</div>
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="InputCourse">Member Name:<span class="mandatory">&nbsp;*</span></label>
						<input type="text" class="form-control" id="member-name" name="member-name" 
						value="<?php //echo $member_list; ?>" placeholder="" readonly>
					</div>                                      
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label for="implementation_date">Created Date<span class="mandatory">*</span></label>
						<input type="date" class="form-control" id="current-date" name="created-date" 
							   value="<?php //echo htmlspecialchars($implementationDate); ?>" required tabindex="2">
					</div>
				</div>
				<div class="col-md-3">
            		<div class="form-group">
              			<label for="serchby">Select Saving Type:</label>
              			<span class="mandatory">*</span>
                  		<select class="form-control combobox savingtype" id="saving-type" name="saving-type" required tabindex="3">
						<option disabled selected></option>
                      		<option value="1">1 Year</option>
                      		<option value="2">2 Year</option>
                      		<option value="3">3 Year</option>
                   		</select>
              		</div>
          		</div>
			</div>
        	<div class="row">
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">Interest Rate (%):</label><span class="mandatory">*</span>
                		<input type="text" class="form-control higlight" id="interest-percentage" name="interest-percentage" value=""
						maxlength="5" required>
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                  		<label for="role">Fixed Amount:</label>
                  		<span class="mandatory">*</span>
                  		<input type="text" class="form-control" id="fd-amount" name="fd-amount" style="" placeholder="" required maxlength="15" 
						value="<?php //print $renewal_amount;?>" onChange="InterestAndMaturedAmount();"/>
                	</div>
              	</div>
				<div class="col-md-4">
            		<div class="form-group">
              			<label for="role">Cash / Cheque / RTGS / UPI:</label><span class="mandatory">*</span>
              			<div class="radio">
                		<label>
                		<input type="radio" name="cash-cheque-rtgs-upi" id="cash" value="1" onClick="showHideChequeRtgsUpi(1);" checked>
                		Cash </label>
                		<label style="margin-left:20px;">
                		<input type="radio" name="cash-cheque-rtgs-upi" id="selected-cheque" value="2" onClick="showHideChequeRtgsUpi(2);" required="">
                		Cheque </label>
                		<label style="margin-left:20px;">
                		<input type="radio" name="cash-cheque-rtgs-upi" id="selected-rtgs" value="3" onClick="showHideChequeRtgsUpi(3);" required="">
                		Rtgs </label>
						<label style="margin-left:20px;">
                		<input type="radio" name="cash-cheque-rtgs-upi" id="selected-upi" value="4" onClick="showHideChequeRtgsUpi(4);" required="">
                		Upi </label>
              			</div>
            		</div>
				</div>
			</div>
			<!---------------------- if Cheque is selected start code--------------------------------->
			<div class="row" id="cheque">
				<fieldset id="major-feild-set" style="margin-top:20px; margin-bottom:20px; margin-left:20px; margin-right:20px;">
				<div class="col-md-4">
					<div class="form-group">
						<label for="implementation_date">Cheque Date:<span class="mandatory">*</span></label>
						<input type="date" class="form-control" id="current-date1" name="cheque-date" 
							   value="<?php //echo htmlspecialchars($implementationDate); ?>"  tabindex="2">
					</div>
				</div>
				<div class="col-md-4">
              		<div class="form-group">
                 		<label for="role">Amount:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="cheque-amount" name="cheque-amount" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">Cheque Number:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="cheque-number" name="cheque-number" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">From Bank:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="cheque-from-bank" name="cheque-from-bank" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">To Bank:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="cheque-to-bank" name="cheque-to-bank" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				</fieldset>
			</div>
			<!---------------------- if Cheque is selected end code--------------------------------->
			
			<!---------------------- if RTGS is selected Start Code----------------------------->
			<div class="row" id="rtgs">
				<fieldset id="major-feild-set" style="margin-top:20px; margin-bottom:20px; margin-left:20px; margin-right:20px;">
				<div class="col-md-4">
					<div class="form-group">
						<label for="implementation_date">RTGS Date:<span class="mandatory">*</span></label>
						<input type="date" class="form-control" id="current-date2" name="rtgs-date" 
							   value="<?php //echo htmlspecialchars($implementationDate); ?>" tabindex="2">
					</div>
				</div>
				<div class="col-md-4">
              		<div class="form-group">
                 		<label for="role">Amount:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="rtgs-amount" name="rtgs-amount" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">RTGS Number:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="rtgs-number" name="rtgs-number" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">From Bank:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="rtgs-from-bank" name="rtgs-from-bank" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">To Bank:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="rtgs-to-bank" name="rtgs-to-bank" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				</fieldset>
			</div>
			<!---------------------- if RTGS is selected end Code----------------------------->
			
			<!---------------------- if UPI is selected Start Code----------------------------->
			<div class="row" id="upi">
				<fieldset id="major-feild-set" style="margin-top:20px; margin-bottom:20px; margin-left:20px; margin-right:20px;">
				<div class="col-md-4">
					<div class="form-group">
						<label for="implementation_date">UPI Date:<span class="mandatory">*</span></label>
						<input type="date" class="form-control" id="current-date3" name="upi-date" 
							   value="<?php //echo htmlspecialchars($implementationDate); ?>"  tabindex="2">
					</div>
				</div>
				<div class="col-md-4">
            		<div class="form-group">
              			<label for="serchby">Select UPI Type:</label>
              			<span class="mandatory">*</span>
                  		<select class="form-control combobox upitype" id="upi-type" name="upi-type" tabindex="3">
						<option disabled selected></option>
                      		<option value="1">GPay</option>
                      		<option value="2">PhonePay</option>
                      		<option value="3">PayTM</option>
							<option value="4">Bhim</option>
							<option value="5">Razor Pay</option>
                   		</select>
              		</div>
          		</div>
				<div class="col-md-4">
              		<div class="form-group">
                 		<label for="role">Amount:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="upi-amount" name="upi-amount" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">UPI Transaction Number:</label>
                		<span class="mandatory">*</span>
                		<input type="text" class="form-control" id="upi-transaction-number" name="upi-transaction-number" 
                            style="" placeholder="" maxlength="50" value="<?php //print $phone;?>" />
              		</div>
            	</div>
				</fieldset>
			</div>
			<!---------------------- if UPI is selected end Code----------------------------->
			
			<div class="row">
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">Total Interest:</label>
                		<input type="text" class="form-control" id="total-interest" name="total-interest" 
                 		style="font-size:20px; color:#FF0000" placeholder="" maxlength="50" value="<?php //print $phone;?>" readonly  />
              		</div>
            	</div>
				<div class="col-md-4">
              		<div class="form-group">
                		<label for="role">Matured Amount:</label>
                		<input type="text" class="form-control" id="matured-amount" name="matured-amount" 
                 		style="font-size:20px; color:#FF0000" placeholder="" maxlength="50" value="<?php //print $phone;?>" readonly  />
              		</div>
            	</div>
				<div class="col-md-4">
					<div class="form-group">
						<label for="role">Remarks:</label>
						<textarea class="form-control" id="remarks" name="remarks" rows="2" cols="50"></textarea>
					</div>
				</div>
			</div>
			
			<div class="row">
          		<div class="col-md-12">
            		<div class="form-group" style="text-align:center">              
              			<input class="btn btn-md btn-info" type="submit" name="save-old-fd" id="save-old-fd" value="Save" >
              			<input type="hidden" name="save-old-fd">
            		</div>
          		</div>
         	</div>
          
        </form>
        </fieldset>
      </div>
      <!-- ./ Col -->
    </div>
    <!-- Page Code End -->
  </div>
  <!-- Footer -->
  <?php include_once "footer.php"; ?>
  <!-- Footer End -->
</div>
<!-- /#Page wrapper -->
</div>
<!-- /#wrapper -->
<!-- jQuery -->

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
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript">

//Combo box placeholder
$('.memberid').combobox(); // Gender Combo
$(".memberid").attr("placeholder", "Select Member ID");

$('.savingtype').combobox(); // Gender Combo
$(".savingtype").attr("placeholder", "Select Saving Type");

$('.upitype').combobox(); // Gender Combo
$(".upitype").attr("placeholder", "Select UPI Type");

/* Browser Close Event START */
var unloaded = false;
$(window).on('beforeunload', unload);
$(window).on('unload', unload);  
function unload(){
    if(!unloaded){
        $('body').css('cursor','wait');
        $.ajax({
            type: 'get',
            async: false,
            url: '../php/clear_current_user.php?uid=<?php echo $_SESSION['UserId'];?>&amodule=1',
            success:function(){ 
                unloaded = true; 
                $('body').css('cursor','default');
            },
            timeout: 5000
        });
    }
}
/* Browser Close Event END */
 $(document).ready(function(){
  $('#designation-details').DataTable();
	idleMax = <?php echo $_SESSION['TimeIdleMax']; ?>;// Logout after 10 minutes of IDLE
		idleTime = 1;
		var idleInterval = setInterval("timerIncrement()", 40000); 
		$(this).mousemove(function (e) {idleTime = 0;});
		$(this).keypress(function (e) {idleTime = 0;});
  });
   function timerIncrement() {
		idleTime = idleTime + 1;
		if (idleTime > idleMax) {
			 document.location.href = "logout.php";
		}
	}
	
 document.getElementById("current-date1").removeAttribute("required");
 document.getElementById("current-date2").removeAttribute("required");
 document.getElementById("bank").removeAttribute("required");
 document.getElementById("cheque-number").removeAttribute("required");
 document.getElementById("transfer-cheque-amount").removeAttribute("required");
 document.getElementById("current-date3").removeAttribute("required");
 document.getElementById("transfer-account-type").removeAttribute("required");
 document.getElementById("transfer-from-account").removeAttribute("required");
 document.getElementById("transfer-to-account").removeAttribute("required");
 document.getElementById("transfer-share-amount").removeAttribute("required");
 document.getElementById("transfer-to-bank-account").removeAttribute("required");
$(function()
{
  $('#current-form').submit(function(){
    $("input[type='submit']", this)
      .val("Please Wait...")
      .attr('disabled', 'disabled');
    return true;
  });
});
/* Show / Hide Sd Combo box for Renewal type */


</script>
<script type="text/javascript">
$('#print').on('click',function(){
/* Get from elements values */
 var values = $(this).serialize();
 $.ajax({
	    url: "mpdn-report/receipt-voucher.php?sn=<?php echo "Receipt Voucher";?>&cy=<?php echo "Report";?>",
        type: "post",
        data: values ,
        success: function (response) {
		res = response.slice(-58);
		//alert(res);
		window.open(res)
        // you will get response from your php page (what you echo or print)                 
        },
        error: function(jqXHR, textStatus, errorThrown) {
          // console.log(textStatus, errorThrown);
		  alert(errorThrown);
        },
		 beforeSend: function(){
			   $('.loader').show()
		   },
		  complete: function(){
			   $('.loader').hide();
		  }
    });
 });



//cash,cheque,trannsfer,upi mode

function showHideChequeRtgsUpi(id) /* This function apply required fields according to the selection of the radio button ie cash/cheque/transfer */
{
 var cheq = document.getElementById("cheque");
 var rtgs = document.getElementById("rtgs");
 var sd = document.getElementById("sdcash");
 var upi = document.getElementById("upi");
 
 if(id == 2) // Cheque
 {
//	 alert("aaa");
	// cheq.checked = true;
	 $('#selected-rtgs').attr('checked',false);
	 $('#selected-upi').attr('checked',false);
	 $('#selected-cheque').attr('checked',true);
	 $('#cash').attr('checked',false);
	 cheq.style.display = "block";
	 rtgs.style.display = "none";
	 upi.style.display = "none";
	 document.getElementById("current-date2").value = '';
	 document.getElementById("rtgs-amount").value = '';
	 document.getElementById("rtgs-number").value = '';
	 document.getElementById("rtgs-from-bank").value = '';
	 document.getElementById("rtgs-to-bank").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("upi-type").value = '';
	 document.getElementById("upi-amount").value = '';
	 document.getElementById("upi-transaction-number").value = '';
	
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("rtgs-amount").removeAttribute("required");
	 document.getElementById("rtgs-number").removeAttribute("required");
	 document.getElementById("rtgs-from-bank").removeAttribute("required");
	 document.getElementById("rtgs-to-bank").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("upi-type").removeAttribute("required");
	 document.getElementById("upi-amount").removeAttribute("required");
	 document.getElementById("upi-transaction-number").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	document.getElementById("current-date1").required = true;
	document.getElementById("current-date1").value = today;
	document.getElementById("cheque-amount").required = true;
	document.getElementById("cheque-number").required = true;
	document.getElementById("cheque-from-bank").required = true;
	document.getElementById("cheque-to-bank").required = true;
	
 }
 else if(id == 3) // RTGS
 {
    /*alert(id);*/
	 $('#selected-rtgs').attr('checked',true);
	 $('#selected-cheque').attr('checked',false);
	 $('#selected-upi').attr('checked',false);
	 $('#cash').attr('checked',false);
//	document.getElementById("selected-transfer").checked = true;
	rtgs.style.display = "block";
	cheq.style.display = "none";
	upi.style.display = "none";
	 document.getElementById("current-date1").value = '';
	 document.getElementById("cheque-amount").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("cheque-from-bank").value = '';
	 document.getElementById("cheque-to-bank").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("upi-type").value = '';
	 document.getElementById("upi-amount").value = '';
	 document.getElementById("upi-transaction-number").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("cheque-amount").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("cheque-from-bank").removeAttribute("required");
	 document.getElementById("cheque-to-bank").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("upi-type").removeAttribute("required");
	 document.getElementById("upi-amount").removeAttribute("required");
	 document.getElementById("upi-transaction-number").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	document.getElementById("current-date2").required = true;
	document.getElementById("current-date2").value = today;
	document.getElementById("rtgs-amount").required = true;
	document.getElementById("rtgs-number").required = true;
	document.getElementById("rtgs-from-bank").required = true;
	document.getElementById("rtgs-to-bank").required = true;
 }
 else if(id == 4) // UPI
 {
    /*alert(id);*/
	 $('#selected-rtgs').attr('checked',false);
	 $('#selected-cheque').attr('checked',false);
	 $('#selected-upi').attr('checked',true);
	 $('#cash').attr('checked',false);
//	document.getElementById("selected-transfer").checked = true;
	rtgs.style.display = "none";
	cheq.style.display = "none";
	upi.style.display = "block";
	 document.getElementById("current-date1").value = '';
	 document.getElementById("cheque-amount").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("cheque-from-bank").value = '';
	 document.getElementById("cheque-to-bank").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("rtgs-amount").value = '';
	 document.getElementById("rtgs-number").value = '';
	 document.getElementById("rtgs-from-bank").value = '';
	 document.getElementById("rtgs-to-bank").value = '';
	
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("cheque-amount").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("cheque-from-bank").removeAttribute("required");
	 document.getElementById("cheque-to-bank").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("rtgs-amount").removeAttribute("required");
	 document.getElementById("rtgs-number").removeAttribute("required");
	 document.getElementById("rtgs-from-bank").removeAttribute("required");
	 document.getElementById("rtgs-to-bank").removeAttribute("required");
	 
	 var today = new Date();
		var dd = String(today.getDate()).padStart(2, '0');
		var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
		var yyyy = today.getFullYear();
		today = dd + '/' + mm + '/' + yyyy;
	document.getElementById("current-date3").required = true;
	document.getElementById("current-date3").value = today;
	document.getElementById("upi-type").required = true;
	document.getElementById("upi-amount").required = true;
	document.getElementById("upi-transaction-number").required = true;
 }
 else // cash
 {
	 $('#selected-rtgs').attr('checked',false);
	 $('#selected-cheque').attr('checked',false);
	 $('#selected-upi').attr('checked',false);
	 $('#cash').attr('checked',true);
	 cheq.style.display = "none";
	 rtgs.style.display = "none";
	 upi.style.display = "none";
	 /*sd.style.display = "block";*/
	 document.getElementById("current-date1").value = '';
	 document.getElementById("cheque-amount").value = '';
	 document.getElementById("cheque-number").value = '';
	 document.getElementById("cheque-from-bank").value = '';
	 document.getElementById("cheque-to-bank").value = '';
	 document.getElementById("current-date2").value = '';
	 document.getElementById("rtgs-amount").value = '';
	 document.getElementById("rtgs-number").value = '';
	 document.getElementById("rtgs-from-bank").value = '';
	 document.getElementById("rtgs-to-bank").value = '';
	 document.getElementById("current-date3").value = '';
	 document.getElementById("upi-type").value = '';
	 document.getElementById("upi-amount").value = '';
	 document.getElementById("upi-transaction-number").value = '';
	 
	 
	 document.getElementById("current-date1").removeAttribute("required");
	 document.getElementById("cheque-amount").removeAttribute("required");
	 document.getElementById("cheque-number").removeAttribute("required");
	 document.getElementById("cheque-from-bank").removeAttribute("required");
	 document.getElementById("cheque-to-bank").removeAttribute("required");
	 document.getElementById("current-date2").removeAttribute("required");
	 document.getElementById("rtgs-amount").removeAttribute("required");
	 document.getElementById("rtgs-number").removeAttribute("required");
	 document.getElementById("rtgs-from-bank").removeAttribute("required");
	 document.getElementById("rtgs-to-bank").removeAttribute("required");
	 document.getElementById("current-date3").removeAttribute("required");
	 document.getElementById("upi-type").removeAttribute("required");
	 document.getElementById("upi-amount").removeAttribute("required");
	 document.getElementById("upi-transaction-number").removeAttribute("required");
	  
 }
}


/*------------------code for find member name when select the member code-------------------------*/
function getMemberName(val) {
    var strURL = "get-membername.php?id=" + val;

    if (!val) {
        $('#member-name').val(''); // just clear the textbox
        return;
    }

    var req = getXMLHTTP();
    if (req) {
        req.onreadystatechange = function () {
            if (req.readyState == 4) {
                if (req.status == 200) {
                    // req.responseText should be the plain member name
                    document.getElementById('member-name').value = req.responseText;
                } else {
                    alert("Problem while using XMLHTTP:\n" + req.statusText);
                }
            }
        };
        req.open("GET", strURL, true);
        req.send(null);
    }
}
/*------------------code for find member name when select the member code-------------------------*/



/*------------------code for finding the total interest and matured amount-------------------------*/
function InterestAndMaturedAmount() {
    const saving_type = parseInt(document.getElementById("saving-type").value, 10) || 0;
    const rate = parseFloat(document.getElementById("interest-percentage").value) || 0;
    const amount = parseFloat(document.getElementById("fd-amount").value) || 0;

    const total_interest_el = document.getElementById("total-interest");
    const matured_amount_el = document.getElementById("matured-amount");

    // years based on saving_type
    let years = 1;
    if (saving_type === 2) {
        years = 2;
    } else if (saving_type !== 1) {
        years = 3;
    }

    const interest = Math.round(amount * rate * years / 100);   // simple interest
    const matured = Math.round(amount + interest);

    total_interest_el.value = interest;
    matured_amount_el.value = matured;
}
/*------------------code for finding the total interest and matured amount-------------------------*/	
</script>
</body>
</html>
<?php
if(!empty($challan_no))
{// HEADER ROW
 // MAIN CONTENET LEFT AND RIGHT
 $atmid = 2;
 $table = PrintReceiptPaymentVoucher($challan_no,$atmid,2); // general-functions.php receipt 1 and payment 2
 //print $table;
 $_SESSION['PRINTRECEIPTPAYMENTREPRINTVOUCHERNEW'] = $table;
}
?>