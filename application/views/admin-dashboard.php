<?php
session_start();
if(!isset($_SESSION['UserId']))
	header('Location: login.php');
include_once "../php/general-functions.php";
include_once "../settings/config.php";
$expired_count = 0;
$nearby_medicine_expiry_count = 0;
$pending_approval = GetPendingApprovalValue();
$blocked_users = GetBlockedUsers();
$role_list = GetRoleCount();
$active_users = GetActiveUsers();
/*$total_op = GetOpCount();  //php/general-functions.php
$expired_count =GetExpiredCount(); //php/general-functions.php
$nearby_medicine_expiry_count = GetNearbyExpiryCount(); //php/general-functions.php
*/
InsertLoginIdInLogged($_SESSION['UserId']);///php/general-functions.php
/*$daily_new_op_count = GetCountForDashboards("hos_op_details","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0"); //php/general-functions.php
$daily_op_count = GetCountForDashboards("hos_op_daily_entry","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0"); //php/general-functions.php
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
<link rel="icon" type="image/ico" href="images/favicon1.png">
<!-- Bootstrap Core CSS -->
<link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="css/mpdn.css" rel="stylesheet">
<!-- Custom Fonts -->
<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/datepicker.css" rel="stylesheet" type="text/css">
<link href="css/data-table.css" rel="stylesheet" type="text/css">
<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
<style>
#clock{
    background-color:#333;
    font-family: sans-serif;
    font-size:20px;
    text-shadow:0px 0px 1px #fff;
    color:#fff;
	text-align:center;
	letter-spacing:3px;
}
#clock span {
    color:#FFFFFF;
    text-shadow:0px 0px 1px #333;
    font-size:15px;
    position:relative;
    top:-7px;
    left:-5px;
}
#date {
	background-color:#333;
    letter-spacing:3px;
    font-size:14px;
    font-family:arial,sans-serif;
    color:#fff;
	text-align:center;
}
  </style>
</head>
<body onbeforeunload="logout()" onLoad="startTime()">
<div id="wrapper">
  <?php include_once "header.php"; ?>
  <!-- Page Code Start From Here -->
        <div id="page-wrapper"> 
            <div class="row">
                <div class="col-md-12">
                    <h2 class="page-header">Admin Dashboard</h2>
                    <hr class="hr-background">
                </div>
            </div>
         
     
            <div class="row">
                <div class="col-md-4">
                	<a href="user-approval-list.php">
                    <?php if($pending_approval > 0)
					{?>
                    <div class="panel panel-yellow shadow">
                    <?php } else {?>
                    <div class="panel panel-yellow shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-file dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $pending_approval; ?></div>
                                    <div class="dashboard-content-title">Pending Approval!</div>
                                </div>
                            </div>
                        </div>
                        
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        
                    </div>
                    </a>
                </div>
				<div class="col-md-4">
                    <a href="role-list.php">
                    <?php if($role_list > 0)
					{?>
                    <div class="panel panel-green shadow">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-edit user-menu-settings dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $role_list; ?></div>
                                    <div class="dashboard-content-title">Role List!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                    
                    </div>
                    </a>
                </div> 
            	<div class="col-md-4">
                   <a href="blocked-users.php">
                   <?php if($blocked_users > 0)
					{?>
                    <div class="panel panel-red shadow">
                    <?php } else {?>
                    <div class="panel panel-red shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ban-circle dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $blocked_users; ?></div>
                                    <div class="dashboard-content-title">Blocked Users!</div>
                                </div>
                            </div>
                        </div>
                    
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                    </div>
                   </a>
                </div>
			</div>
			<div class="row"><!-- row -->
                <div class="col-md-4"><!-- col -->
                    <a href="active-users.php">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-green shadow">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-user dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php print $active_users; ?></div>
                                    <div class="dashboard-content-title">Active Users!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
			 <div class="col-md-4"><!-- col -->
                    <a href="#">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-primary shadow">
                    <?php } else {?>
                    <div class="panel panel-primary shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-time dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php //print $active_users; ?>2025-2026</div>
                                    <div class="dashboard-content-title">Academic Year Setup!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
			<div class="col-md-4"><!-- col -->
                    <a href="#">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-green shadow" >
                    <?php } else {?>
                    <div class="panel panel-black shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-adjust dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php // print $active_users; ?>Even</div>
                                    <div class="dashboard-content-title">Semester Setup!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
          </div><!-- ./ row -->
		  <div class="row"><!-- row -->
                <div class="col-md-4"><!-- col -->
                    <a href="#">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-primary shadow">
                    <?php } else {?>
                    <div class="panel panel-primary shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-user dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php // print $active_users; ?>25</div>
                                    <div class="dashboard-content-title">Faculties List!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
			 <div class="col-md-4"><!-- col -->
                    <a href="#">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-primary shadow">
                    <?php } else {?>
                    <div class="panel panel-primary shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-user dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php // print $active_users; ?>1500</div>
                                    <div class="dashboard-content-title">Students List!</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
			<div class="col-md-4"><!-- col -->
                    <a href="#">
                    <?php if($active_users > 0)
					{?>
                    <div class="panel panel-red shadow">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-asterisk dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php // print $active_users; ?>In Progress</div>
                                    <div class="dashboard-content-title">Assessment Status</div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <span class="pull-left">View Details</span>
                            <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                            <div class="clearfix"></div>
                        </div>
                </div>
                </a>
            </div> <!-- ./ col -->
          </div><!-- ./ row -->
<!-- /#Page wrapper -->
<?php 
//print $_SESSION['dashboard'];
?>
</div><?php include_once "footer.php"; ?>
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/chart.min.js"></script>
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


</script>

<script type="text/javascript">
function startTime() {
    var today = new Date();
    var hr = today.getHours();
    var min = today.getMinutes();
    var sec = today.getSeconds();
    ap = (hr < 12) ? "<span>AM</span>" : "<span>PM</span>";
    hr = (hr == 0) ? 12 : hr;
    hr = (hr > 12) ? hr - 12 : hr;
    //Add a zero in front of numbers<10
    hr = checkTime(hr);
    min = checkTime(min);
    sec = checkTime(sec);
    document.getElementById("clock").innerHTML = hr + ":" + min + ":" + sec + " " + ap;
    
    var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var curWeekDay = days[today.getDay()];
    var curDay = today.getDate();
    var curMonth = months[today.getMonth()];
    var curYear = today.getFullYear();
    var date = curWeekDay+", "+curDay+" "+curMonth+" "+curYear;
    document.getElementById("date").innerHTML = date;
    
    var time = setTimeout(function(){ startTime() }, 500);
}
function checkTime(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}
</script>
</body>
</html>
