<?php 
ob_start(); 
session_start();
$success_confirm = '';
unset($_SESSION['LoginMultipleClick']);
if(!empty($_REQUEST['s']))
	$success_confirm = $_REQUEST['s'];

//$_SESSION['UserId'] = 0;
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
   <!-- Bootstrap Core CSS -->
	<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="css/mpdn.css" rel="stylesheet">
	<!-- Custom Fonts -->
	<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link href="css/datepicker.css" rel="stylesheet" type="text/css">
	<link href="css/data-table.css" rel="stylesheet" type="text/css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<?php
if($_SESSION['UserId'] != ''){
?>
<body>
    <div class="container bg-img centered-form login-outer-block ">
    	
        
    	<div class="row bg-logo">
        	<div class="col-md-12">
            	<img src="images/logo.png" class="img-responsive" style="float:left;"><h2 style="color:#1972b3;text-align:center;">Marthandam People Development Nidhi</h2>
            </div>
        </div>
        <div class="row vertical-align ">
            <div class="col-md-5  center-block">
                  <?php if($success_confirm == 1)
				  {?>
					 <div class="success-message-block">
					  <label>	Incorrect Password.  Please check the mail</label>
					 </div>
				  <?php } ?>
                <div class="login-panel panel panel-primary login-background">

                    <div class="panel-heading bg-login-panel">
                        <h3 class="panel-title">Security Password</h3>
                        
                    </div>
                    <div class="panel-body login-block-background">
                        <form role="form" name="login" action="../php/user-process.php" method="post">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Enter Email Password " name="password" type="password" value="" autofocus>
                                </div>
                                <div class="checkbox">
                                   <!-- <label>
                                        <input name="remember" type="checkbox" value="Remember Me">Remember Me
                                    </label>-->
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <div class="span7 text-center">
                                <button type="submit" class="btn btn-lg btn-info btn-block login-button" name="loginconfirm" style="width:55%">Submit</button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- jQuery -->
<?php } else {?>
<div class="row not-authorised">
	<div class="col-sm-12">
		<h2>You are not authorised to access this page</h2>
	</div>
</div>
<?php } ?>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<!-- Bootstrap Core JavaScript -->
<script src="bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>
<!-- Custom Theme JavaScript -->
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<!--<script src="js/jquery.dataTables.js"></script>
<script src="js/designation.js"></script>-->
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>

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
