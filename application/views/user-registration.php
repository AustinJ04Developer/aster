<?php
/*   	global $is_inserted_in_db;
	$is_inserted_in_db = $_REQUEST['s'];
*/
session_start();
include_once "../php/general-functions.php";
include_once "../settings/config.php";
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
  <?php include_once "header-registration.php"; ?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">User Registration</h2>
        <hr class="hr-background">
      </div>
      <!-- /.col-lg-12 -->
    </div>
	 <div class="row">
      <div class="col-lg-12">
        <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <!-- Page Code Start From Here -->
    <div class="row">
	  <div class="col-sm-12">
      <form name="user-registration" action="../php/user-process.php" method="post" autocomplete="off" id="user-registration" enctype = "multipart/form-data">
        <div class="col-sm-12">
          <div class="row">
            <div class="col-sm-6 form-group">
			<input style="display:none" type="text" name="fakeusernameremembered"/>
			<input style="display:none" type="password" name="fakepasswordremembered"/>
              <label>Date</label>
              <input type="text" class="form-control date-picker" id="current-date" name="current-date" style="" placeholder=
                        "dd/mm/yyyyy" value="<?php print date('d/m/Y'); ?>" readonly>
            </div>
            <div class="col-sm-6 form-group">
              <label>Name</label><span class="mandatory">*</span>
              <input type="text" placeholder="Enter Name" class="form-control" name="name" id="name" maxlength="50" title="Name must not be blank." required data-toggle="tooltip" data-placement="top" data-html="true" autofocus>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>User Name</label><span class="mandatory">*</span>
              <input type="text" placeholder="Enter User Name" class="form-control" name="username" id="username" maxlength="50" autocomplete="off" title="Username must not be blank and contain only letters, numbers and underscores." required pattern="\w+" /><span id="status"></span> 
            </div>
            <div class="col-sm-6 form-group">
              <label>Password</label><span class="mandatory">*</span>
              <input type="Password" placeholder="Enter Password" class="form-control" name="password" id="password" maxlength="10" autocomplete="off" pattern=".{5,}" title="Five or more characters" required>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label for="role">Role</label><span class="mandatory">*</span>
              <select class="form-control combobox" id="role" name="role" title="Role Name must not be empty" required>
			  <option disabled selected></option>
				<?php
					$option_details = UserRegistrationMasterFields('RoleId','RoleName','role'); //General Functions
			 		echo $option_details;
				?>
              </select>
            </div>
            <div class="col-sm-6 form-group">
              <label>Address</label><span class="mandatory">*</span>
              <input type="text" placeholder="Enter Address" class="form-control" name="staffcodenumber" id="staffcode-number" maxlength="500" title="Address must not be empty"  required> 
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label for="designation">Designation</label><span class="mandatory">*</span>
              <select class="form-control combobox" id="designation" name="designation" title="Designation must not be empty" required>
			  <option disabled selected></option>
                <?php
					$option_details = UserRegistrationMasterFields('DesignationId','Designation','designation'); //General Functions
			 		echo $option_details;
				?>
              </select>
            </div>
            <div class="col-sm-6 form-group">
              <label>Email ID</label><span class="mandatory">*</span>
              <input type="email" placeholder="Enter Email ID" class="form-control" name="emailid" id="emailid" maxlength="50" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" title="Invalid Email Id" required><span id="status-email"></span> 
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Mobile Number</label>
              <input type="text" placeholder="Enter Mobile Number" class="form-control" name="mobilenumber" id="mobile-number" maxlength="15">
            </div>
            <div class="col-sm-6 form-group">
              <label>Phone Number</label>
              <input type="text" placeholder="Enter Phone Number" class="form-control" name="phonenumber" id="phone-number" maxlength="15">
            </div>
          </div>
          <div class="row">
           
            <div class="col-sm-6 form-group">
            <label>Upload Photo</label><span class="mandatory"></span>
              <input autocomplete="off" class="form-control input upload-textbox pull-left"  name="signature" type="file" />
			</div>
          </div>
          
		  <div id="loading" style="display:none;"><img src="images/loader.gif" />Please wait.....</div>
          <center><button type="submit" class="btn btn-md btn-info" name="submit" onClick="loadimages();" data-loading-text="Please wait...">Save</button></center>
        </div>
      </form>
	 </div>
    </div>
	<br />
	
  </div><?php include_once "footer.php"; ?>
  <!-- /.row -->
  <!-- Page Code End -->
  <!-- Footer -->
  
  <!-- Footer End -->
</div>
<!-- /#Page wrapper -->
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/user-registration.js" type="text/javascript"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('input').attr('autocomplete', 'false');
    $('.combobox').combobox();
	$('#user-registration').validate();
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
  $(window).load(function() { // can also try on document ready
    $('input[autocomplete]').removeAttr('autocomplete');
});
/* Browser Close Event START */
/* Browser Close Event END */

</script>

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
<script type="text/javascript" src="js/login.js"></script>
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
            url: '../php/clear-user-in-search-data-reg-activity.php?uid=<?php echo $_SESSION['UserId'];?>&amodule=1',
            success:function(){ 
                unloaded = true; 
                $('body').css('cursor','default');
            },
            timeout: 5000
        });
    }
}
/* Browser Close Event END */
</script>

</body>
</html>
