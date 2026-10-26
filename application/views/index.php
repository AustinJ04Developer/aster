<?php 
ob_start(); 
session_start();
include_once("../settings/config.php");
include_once "../php/general-functions.php";
global $dbh;
$success_registration = '';
$login = '';
$roleid = '';
$force_logout = 0;
if(!empty($_REQUEST['s']))
	$success_registration = $_REQUEST['s'];
if(!empty($_REQUEST['r']))
	$login = $_REQUEST['r'];
if(!empty($_REQUEST['f']))
	$force_logout =1;
if(!empty($_REQUEST['RoleId']))
$roleid = $_SESSION['RoleId'];
$sel_branch = 0;
if($roleid == 1)
{
	header('Location: ../views/admin-dashboard.php');	
}
/*$sql_force_to_logout = "DELETE FROM logged WHERE loginid = ".$_SESSION['UserId'];
$success = $dbh->exec($sql_force_to_logout);
*/	
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>CoE</title>
	<!-- Bootstrap Core CSS -->
	<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="css/mpdn.css" rel="stylesheet">
	<!-- Custom Fonts -->
	<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link href="css/datepicker.css" rel="stylesheet" type="text/css">
	<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
	<link href="css/data-table.css" rel="stylesheet" type="text/css">
	<style>
	.number{text-align:right;}
	.active{
	font-weight:bold;
	}
	.nav-tabs>li.active>a
	{
		background-color:#66FF00;
	}
	.nav-tabs {
    border-bottom: 1px solid #2d88b9;
	}
	.nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
		color: #555;
		cursor: default;
		background-color: #fff;
		border: 3px solid #2d88b9;
		border-bottom-color: transparent;
	}
	</style>
</head>
<?php
//$_SESSION['UserId'] = GetUserId();
?>
<body>
    <div class="container bg-img centered-form login-outer-block ">
    	<div class="row bg-logo">
        	<div class="col-md-12">
            	<img src="images/logo.png" class="img-responsive" style="float:left;text-align:center;"><h2 style="color:#1972b3;text-align:center;"><span style="color:#ad0400;font-family: arial black;">Aster - </span><span style="color:#1ba0d8;font-weight:bold;">Public Development Foundation</span></h2>
            </div>
            <div class="col-md-10" style="text-align:center;font-size:25px;" class="bank-title">
			
            </div>
        </div>
        
        <div class="row" style="text-align:center;min-height:35em;display:flex;algin-items:center;background:url(images/logo_bg.png)">
        	
            <div class="col-md-6  center-block">
				<?php if($login == 1)
				{?>
                 <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                   <strong>User does not exist.</strong>
				</div>
				<?php } else if($login == 2) {?>
                <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				   Your approval is still pending.<br>
                   <strong>Please contact your Administrator</strong>
				</div>
				<?php } else if($login == 3) {?>
                <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				   User is blocked.<br>
                   <strong>Please contact your Administrator</strong>
				</div>
				<?php } else if($login == 4) {?>
                <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                   <strong>Incorrect User name or Password.</strong>
				</div>
				<?php } else if($login == 5) {?>  
				<div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				   Another user already exists in the system with the same login name.<br>
                   <strong>Please contact your Administrator</strong>
				</div>
				<?php } ?>
                <?php if($force_logout == 1) { ?>
                <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				   Administrator Closed Your Session.<br>
                   <strong>Please contact your Administrator</strong>
				</div>
                <?php } ?>
				
				<?php if($success_registration == 1)
				{?>
                <div class="alert alert-danger" style="border-radius:15px;text-align:center;">
				  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                   <strong>Thank you for applying for an account. Your account is currently pending approval by the site administrator.
In the meantime, a welcome message with further instructions has been sent to your e-mail address.</strong>
				</div>
				<?php } ?>
                <div class="login-panel panel panel-primary login-background">
                    <div class="panel-heading bg-login-panel">
                        <h3 class="panel-title">User Login</h3>
                    </div>
                    <div class="panel-body login-block-background">
                        <form role="form" id="loginForm" name="login" action="../php/user-process.php" method="post">
                            <fieldset>
                                <div class="form-group">
                                     <input type="text" placeholder="User Name" class="form-control" name="username" id="username" style="display:none;"  /> 
                                     <input type="text" placeholder="User Name" class="form-control" name="username" id="username" maxlength="50" autocomplete="off" title="Username must not be blank and contain only letters, numbers and underscores." required pattern="\w+" autofocus="autofocus"  /><span id="status"></span> 
                                </div>
                                <div class="form-group">
                                    <input class="form-control" id="password" placeholder="Password" name="password" type="password" value="" autocomplete="off" required>
                                </div>
								
                                <div class="checkbox">
                                   <!-- <label>
                                        <input name="remember" type="checkbox" value="Remember Me">Remember Me
                                    </label>-->
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <div class="span7 text-center">
								<div  id="loading" style="display:none;"><img src="images/loader.gif" />&nbsp;Please Wait....</div>
                                <button type="submit" class="btn btn-lg btn-info btn-block login-button" name="login" style="width:55%" onClick="loadimage();" id="my_button">Login</button>
                                </div>
                               <div style="margin-top:15px; text-align:center;">
                                <a href="forget-password.php" class="login-page-llinks">Forget Password</a> <span class="login-page-divider">|</span> <a href="user-registration.php" class="login-page-llinks"> New Registration </a>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<!-- Bootstrap Core JavaScript -->
<script type="text/javascript">
/* Browser Close Event START */
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
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script src="js/mpdn.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript" src="js/page.js"></script>
<script>
function createItemCode(str) {
  if (str == "") {
    document.getElementById("item-code").innerHTML = "";
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
	  var itemtypeindex = document.getElementById('item-type');
	  var itemtype = itemtypeindex.options[itemtypeindex.selectedIndex].innerHTML;
	  itemtype_chars = itemtype.substr(0,2).toUpperCase();
	  var itembrandindex = document.getElementById('brand');
	  var itembrand = itembrandindex.options[itembrandindex.selectedIndex].innerHTML;
	  itembrand_chars = itembrand.substr(0,2).toUpperCase();
      item_code_start = document.getElementById("item-name").value.substr(0, 3).toUpperCase();
        document.getElementById("item-code").value = item_code_start+itemtype_chars+itembrand_chars+this.responseText;
      }
    };
    xmlhttp.open("GET","invt_ajax_generate_item_code.php?q="+str,true);
    xmlhttp.send();
  }
}
function validateDecimalNumber(event) {
  const keyCode = event.keyCode || event.which;
  const keyValue = String.fromCharCode(keyCode);
  const regex = /^[\d.]*$/; // only allow digits and decimal point

  // check if the key pressed is a valid decimal number character
  if (!regex.test(keyValue)) {
    event.preventDefault(); // prevent the character from being entered
  }

  // make sure there's only one decimal point
  const input = event.target.value;
  const decimalCount = (input.match(/\./g) || []).length;
  if (decimalCount > 1) {
    event.preventDefault();
  }
}
function validateWholeNumber(event) {
  const charCode = event.keyCode || event.which;
  // charCode 8 is for backspace, charCode 0 is for arrow keys and function keys
  if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 8 && charCode !== 0) {
    event.preventDefault();
  }
}
</script>
<script>
$('#submitButton').click(function () {
    $('input:invalid').each(function () {
        // Find the tab-pane that this element is inside, and get the id
        var $closest = $(this).closest('.tab-pane');
        var id = $closest.attr('id');

        // Find the link that corresponds to the pane and have it show
        $('.nav a[href="#' + id + '"]').tab('show');

        // Only want to do it once
        return false;
    });
});
</script>
</body>
</html>
