<?php
   	session_start();
	if(!isset($_SESSION['UserId']))
		header('Location: login.php');
    include_once "../php/general-functions.php";
	include_once "../php/general-class.php";
	//ForceToLogoutUser($_SESSION['UserId']);
	$page_id = 1; // Page Id 1-> Branch Settings
	$branch_name = '';
	$table_name = 'pdn_branchmaster';
	$key_field_name = 'BMID';
	if(!empty($_REQUEST['eid'])) // Edit Record
	{
		$arr_field_list = 'Date,BranchName,BuildingNo,StreetName,City,Pincode,State,Phone,SDReceiptAbbr,SDPaymentAbbr,FDReceiptAbbr,FDPaymentAbbr,RDReceiptAbbr,RDPaymentAbbr,ShareReceiptAbbr,ShareReceiptNo,SDReceiptNo,SDPaymentNo,FDReceiptNo,FDPaymentNo,RDReceiptNo,RDPaymentNo,OtherReceiptAbbr,OtherReceiptNo,ReceiptChallanNo,PaymentChallanNo,ReceiptChallanAbbr,PaymentChallanAbbr';
		$key_field_name = 'BMID'; 
		$arr_field_value = ArrayFieldValues($_REQUEST['eid'],$arr_field_list,$table_name,$key_field_name,$page_id,$table_name); // pdn-settings-functions.php
	}
	elseif(!empty($_REQUEST['did'])) // Delete Record
	{
    	$branch = new PDNDataInsertUpdateDelete;
	    $success = $branch->MISDeleteData($_REQUEST['did'],$table_name,$key_field_name);	
		// $delete_field_value = DeleteValues($_REQUEST['did']);
	}
	// Find whether it is edit mode or not if edit mode select the particular state
	if(isset($arr_field_value[6]))
		$selected_state_id = $arr_field_value[6];
	else
		$selected_state_id = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Inventory</title>
    <!-- Bootstrap Core CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/mpdn.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link href="css/datepicker.css" rel="stylesheet" type="text/css">
    <link href="css/data-table.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div id="wrapper">
      	<?php include_once "header.php"; ?>
        <?php
		    include_once "header.php";
		    list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage(); //php/general-functions.php
			if($page_access_permission == 0)
			{
				print PageAuthorizationMessage();
				exit;
			}
		?>
        <!-- Page Code Start From Here -->
        <div id="page-wrapper"> 
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Create / Update Branch</h2>
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
           		<div class="col-md-12">
                	<?php if($_REQUEST['s'] == 1) {?>
                    	<h4> Data Updated Successfully </h4>
                    <?php } elseif($_REQUEST['s'] == 2) {?>
	                    <h4> Error....Data Not Updated </h4>
                    <?php } ?>
                </div>
           </div>
           <!-- /.row -->
		   <!-- Page Code Start From Here -->
            <!-- .row -->
            <div class="row"> <!-- First Row -->
            	<div class="col-md-12 col-sm-12 col-xs-12">
                 <fieldset id="major-feild-set" style="margin-top:15px;">
                    <form role="frm-role" method="post" action="../php/bank-settings/general-settings.php">
                      <div class="col-md-6">
                          <div class="form-group"><!-- form group -->
                            <label for="date">Date:</label>
                            <input type="text" class="form-control date-picker" id="current-date" name="current-date" style="" placeholder=
                            "dd/mm/yyyyy" value="<?php if(isset($arr_field_value[0])) { echo date("d/m/Y", strtotime($arr_field_value[0]));} else{ echo date("d/m/Y");}?>" readonly />                          </div><!-- ./ form group -->
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Branch Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="branch-name" name="branch-name" 
                        style="" placeholder="Enter branch name" maxlength="300" value="<?php print $arr_field_value[1]?>" required autofocus /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group"><!-- form group -->
                            <label for="date">Building No/Name:</label><span class="mandatory">*</span>
                            <input type="text" class="form-control" id="building-no" name="building-no" style="" placeholder=
                            "Enter Building No" value="<?php print $arr_field_value[2]; ?>" required  />
                          </div><!-- ./ form group -->
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Street No/Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="street-no" name="street-no" 
                        style="" placeholder="Enter street no/name" maxlength="100" value="<?php print $arr_field_value[3];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group"><!-- form group -->
                            <label for="date">City/Town:</label><span class="mandatory">*</span>
                            <input type="text" class="form-control" id="city" name="city" style="" placeholder=
                            "Enter City / Town" value="<?php print $arr_field_value[4]; ?>" required />
                          </div><!-- ./ form group -->
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Pincode:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="pincode" name="pincode" 
                        style="" placeholder="Enter Pincode" maxlength="8" value="<?php print $arr_field_value[5];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group"><!-- form group -->
                            <label for="date">Select State:</label><span class="mandatory">*</span>
                            <select class="form-control state" id="state" name="state" title="Role Name must not be empty" required autocomplete="off" required  >
                              <option disabled selected></option>
                                <?php
                                    $option_details = FillStateValues($selected_state_id); //General Functions
                                    echo $option_details;
                                ?>
                              </select>
                          </div><!-- ./ form group -->
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Phone:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="phone" name="phone" 
                        style="" placeholder="Enter Phone No" maxlength="15" value="<?php print $arr_field_value[7];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">SD Receipt Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="sd-receipt-abbr" name="sd-receipt-abbr" 
                        style="" placeholder="Enter SD Receipt Short Name" maxlength="3" value="<?php print $arr_field_value[8];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">SD Payment Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="sd-payment-abbr" name="sd-payment-abbr" 
                        style="" placeholder="Enter SD Payment Short Name" maxlength="3" value="<?php print $arr_field_value[9];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">FD Receipt Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="fd-receipt-abbr" name="fd-receipt-abbr" 
                        style="" placeholder="Enter FD Receipt Short Name" maxlength="3" value="<?php print $arr_field_value[10];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">FD Payment Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="fd-payment-abbr" name="fd-payment-abbr" 
                        style="" placeholder="Enter FD Payment Short Name" maxlength="3" value="<?php print $arr_field_value[11];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">RD Receipt Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="rd-receipt-abbr" name="rd-receipt-abbr" 
                        style="" placeholder="Enter RD Receipt Short Name" maxlength="3" value="<?php print $arr_field_value[12];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">RD Payment Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="rd-payment-abbr" name="rd-payment-abbr" 
                        style="" placeholder="Enter RD Payment Short Name" maxlength="3" value="<?php print $arr_field_value[13];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Share Receipt Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="share-receipt-abbr" name="share-receipt-abbr" 
                        style="" placeholder="Enter Share Receipt Short Name" maxlength="3" value="<?php print $arr_field_value[14];?>" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Share Receipt Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="share-receipt-no" name="share-receipt-no" 
                        style="" placeholder="Enter Share Receipt Number" maxlength="10" value="<?php print $arr_field_value[15];?>" 
                        onKeyPress="return validateNumber(event)"required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">SD Receipt Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="sd-receipt-no" name="sd-receipt-no" 
                        style="" placeholder="Enter SD Receipt Number" maxlength="10" value="<?php print $arr_field_value[16];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">SD Payment Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="sd-payment-no" name="sd-payment-no" 
                        style="" placeholder="Enter SD Payment Number" maxlength="10" value="<?php print $arr_field_value[17];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">FD Receipt Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="fd-receipt-no" name="fd-receipt-no" 
                        style="" placeholder="Enter FD Receipt Number" maxlength="10" value="<?php print $arr_field_value[18];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">FD Payment Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="fd-payment-no" name="fd-payment-no" 
                        style="" placeholder="Enter FD Payment Number" maxlength="10" value="<?php print $arr_field_value[19];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">RD Receipt Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="rd-receipt-no" name="rd-receipt-no" 
                        style="" placeholder="Enter RD Receipt Number" maxlength="10" value="<?php print $arr_field_value[20];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">RD Payment Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="rd-payment-number" name="rd-payment-number" 
                        style="" placeholder="Enter RD Payment Number" maxlength="10" value="<?php print $arr_field_value[21];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Other Receipt Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="other-receipt-abbr" name="other-receipt-abbr" 
                        style="" placeholder="Enter Other Receipt Short Name" maxlength="3" value="<?php print $arr_field_value[22];?>" 
                        required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Other Receipt Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="other-receipt-no" name="other-receipt-no" 
                        style="" placeholder="Enter Other Receipt Number" maxlength="10" value="<?php print $arr_field_value[23];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Receipt Challan Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="receipt-challan-no" name="receipt-challan-no" 
                        style="" placeholder="Enter Receipt Challan Number" maxlength="10" value="<?php print $arr_field_value[24];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Payment Challan Number:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="payment-challan-number" name="payment-challan-number" 
                        style="" placeholder="Enter Payment Challan Number" maxlength="10" value="<?php print $arr_field_value[25];?>" 
                        onKeyPress="return validateNumber(event)" required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Receipt Challan Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="receipt-challan-abbr" name="receipt-challan-abbr" 
                        style="" placeholder="Enter Receipt Challan Short Name" maxlength="3" value="<?php print $arr_field_value[26];?>" 
                        required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-6">
                      <div class="form-group">
                        <label for="role">Payment Challan Short Name:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="payment-challan-abbr" name="payment-challan-abbr" 
                        style="" placeholder="Enter Payment Challan Short Name" maxlength="3" value="<?php print $arr_field_value[27];?>" 
                        required /> <span id="status-role"></span> 
                      </div>
                      </div>
                      <div class="col-md-12">
                      <div class="form-group" style="text-align:center">
                      	<input type="hidden" value="<?php echo $_REQUEST['eid'];?>" name="eid">
            			 <button class='btn btn-md btn-info' type="submit" name="save-branch" id="save-branch" >Save</button>
                      </div>
                      </div>
                   </form>
                   </fieldset>
                 </div> <!-- ./ Col -->
              </div> <!-- ./ First Row -->
              <div class="row"> <!-- Second Row -->  
                <div class="col-md-12">
                    <table id="designation-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                      <thead>
                        <tr class="header-row">
                          <th>SlNo</th>
                          <th>Branch Name</th>
                          <th>Address</th>
                          <th>Edit</th>
                          <th>Delete</th>
                        </tr>
                      </thead>
                      <tbody class="table-coloum-style">
                        <?php 
                        //Pass argument as field names seperated by comma and table name
						 $arr_field_list = array();
						 $link = 'branch-settings.php';
						 $table = 'pdn_branchmaster';
						 $arr_field_list = 'BranchName,City,Phone';
						 $key_field = 'BMID';
						 $grid_result = PDNResultGrid($arr_field_list,$link,$table,$key_field);
                         echo $grid_result;
                        ?>
                      </tbody>
                    </table>
      			</div><!-- ./col -->
            </div><!-- /. Second Row -->
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
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript">
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
</script>
</body>
</html>
