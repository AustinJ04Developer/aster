<?php
session_start();
include_once "../php/general-functions.php";
$success_registration = $_REQUEST['s'];
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
  <?php include_once "header-registration.php"; 
  ?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">Forget Password</h2>
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
	    <?php if($success_registration == 1)
		{?>
	  	<div class="success-message-block" style="height:40px;margin:25px 0px 25px 0px;">
		Further instructions have been sent to your e-mail address.
		</div>
		<?php } ?>
         <?php if($success_registration == 2)
		{?>
	  	<div class="success-message-block" style="height:40px;margin:25px 0px 25px 0px;">
		 Still you are not approved. You can change the password only after approved by admin.
		</div>
		<?php } ?>
          <?php if($success_registration == 3)
		{?>
	  	<div class="success-message-block" style="height:40px;margin:25px 0px 25px 0px;">
		 You account is blocked. Please contact your administrator.
		</div>
		<?php } ?>
        <form name="forget-password" action="../php/user-process.php" method="post" autocomplete="off" id="forget-password" >
          <div class="col-sm-12">
            <div class="row">
              <div class="col-sm-6 form-group">
                <input style="display:none" type="text" name="fakeusernameremembered"/>
                <input style="display:none" type="password" name="fakepasswordremembered"/>
                <label>Email ID</label>
                <span class="mandatory">*</span>
                <input type="email" placeholder="Enter Email ID" class="form-control" name="emailid" id="emailid" maxlength="50" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" title="Invalid Email Id" required autofocus>
                <span id="status-email"></span>
			  </div>
            </div>
            <div  id="loading" style="display:none;"><img src="images/loader.gif" />Mail Sending.....</div>
            <button type="submit" class="btn btn-md btn-info" name="forgetpassword" id="forget-password" onClick="loadimage();">Submit</button>
          </div>
        </form>
      </div>
    </div>
    <br />
   
  </div> <?php include_once "footer.php"; ?>
  <!-- /.row -->
  <!-- Page Code End -->
  <!-- Footer -->
  <!-- Footer End -->
</div>
<!-- /#Page wrapper -->
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/forget-password.js" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function(){
	$('#forget-password').validate();
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
<script type="text/javascript" src="js/login.js"></script>
<script>
function loadimage()
{ 
//var v1 = document.getElementById("username").val
	if(document.getElementById("emailid").value == "")
	{
		return false;
	}
	else
	{
	document.getElementById("loading").style.display = "block";
	}
}
/* Browser Close Event START */
 
/* Browser Close Event END */

</script>

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
