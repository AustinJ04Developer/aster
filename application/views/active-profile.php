<?php
session_start();
if(!isset($_SESSION['uid']))
	header('Location: login.php');
include_once "../php/general-functions.php";
InsertLoginIdInLogged($_SESSION['UserId']);
//ForceToLogoutUser($_SESSION['UserId']);
$userid = '';
if(!empty($_REQUEST['eid']))
	$userid = $_REQUEST['eid'];
//$_SESSION['UserId'] = 2;
//$userid = $_SESSION['UserId'];
///php/general-functions.php
list($name,$username,$password,$role,$staffcodenumber,$designationid,$divisionid,$entityid,$emailid,$mobilenumber,$phonenumber) = FillAllFieldsInUserProfile($userid);
//$success = $_REQUEST['s'];
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
<link href="css/mpdn.css" rel="stylesheet">
<!-- Custom Fonts -->
<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/datepicker.css" rel="stylesheet" type="text/css">
<link href="css/data-table.css" rel="stylesheet" type="text/css">
<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="wrapper">
  <?php include_once "header.php"; ?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">Active User</h2>
        <hr class="hr-background">
      </div>
	  <?php if($success == 1) { ?>
	  <div class="col-lg-12">
        <h6 class="success-message">Data updated successfully !!!</h6>
        <hr class="hr-background">
      </div>
	  <?php $success = 0;}?>
      <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <!-- Page Code Start From Here -->
    <div class="row">
	  <div class="col-sm-12">
      <form name="user-approval" action="../php/user-process.php" method="post">
        <div class="col-sm-12">
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Date</label>
              <input type="text" class="form-control date-picker" id="current-date" name="current-date" style="" placeholder=
                        "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>" readonly>
            </div>
            <div class="col-sm-6 form-group">
              <label>Name</label>
              <input type="text" placeholder="Enter Name" class="form-control" name="name" id="name" value="<?php echo $name; ?>" maxlength="50" readonly="">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>User Name</label>
              <input type="text" placeholder="" class="form-control" name="username" id="user-name" maxlength="50" value="<?php echo $username; ?>" autocomplete="off" readonly="readonly">
            </div>
            <div class="col-sm-6 form-group">
              <label>Password</label>
              <input type="Password" placeholder="Enter Password" class="form-control" name="password" id="password" maxlength="10" autocomplete="off" readonly="readonly" value="<?php echo $password; ?>">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label for="role">Role</label>
              <select class="form-control combobox" id="role" name="role" disabled>
			  <option disabled selected></option>
				<?php
					$option_details = EditUserProfileFields('RoleId','RoleName',$role,'role'); //General Functions order by field list, selected id and table name
			 		echo $option_details;
				?>
              </select>
            </div>
            <div class="col-sm-6 form-group">
              <label>Staff Code Number</label>
              <input type="text" placeholder="Enter Staff Code Number" class="form-control" name="staffcodenumber" id="staffcode-number" value="<?php echo $staffcodenumber; ?>" maxlength="25" autocomplete="off" readonly="readonly">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label for="designation">Designation</label>
              <select class="form-control combobox" id="designation" name="designation" disabled>
			  <option disabled selected></option>
                <?php
					$option_details = EditUserProfileFields('DesignationId','Designation',$designationid,'designation'); ///php/general-functions.php
			 		echo $option_details;
				?>
              </select>
            </div>
            <div class="col-sm-6 form-group">
              <label>Email ID</label>
              <input type="text" placeholder="Enter Email ID" class="form-control" name="emailid" id="email-id" maxlength="50" readonly="readonly" value="<?php echo emailid;?>" readonly>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Mobile Number</label>
              <input type="text" placeholder="Enter Mobile Number" class="form-control" name="mobilenumber" id="mobile-number" maxlength="15" value="<?php echo $mobilenumber; ?>" readonly="readonly">
            </div>
            <div class="col-sm-6 form-group">
              <label>Phone Number</label>
              <input type="text" placeholder="Enter Phone Number" class="form-control" name="phonenumber" id="phone-number" maxlength="15" value="<?php echo $phonenumber; ?>" readonly>
			  <input type="hidden" value="<?php print $userid;?>" name="userid" />
            </div>
          </div>
		  <div  id="loading" style="display:none;"><img src="images/loader.gif" />Mail Sending.....</div>
          <button type="submit" class="btn btn-md btn-info" name="activeuser" onClick="loadimage();">Active</button>
		  <button type="submit" class="btn btn-md btn-danger" name="canceluser">Cancel</button>
        </div>
      </form>
	 </div>
    </div>
  </div>
  <!-- /.row -->
  <!-- Page Code End -->
  <!-- Footer -->
 
  <!-- Footer End -->
</div> <?php include_once "footer.php"; ?>
<!-- /#Page wrapper -->
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="js/mpdn.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<!--<script src="js/jquery.dataTables.js"></script>
<script src="js/designation.js"></script>-->
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">

  $(document).ready(function(){
    $('.combobox').combobox();
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
  function loadimage()
  {
    document.getElementById("loading").style.display = "block";
  }
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
/* Browser Close Event END */</script>
</body>
</html>
