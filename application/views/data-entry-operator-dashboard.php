<?php
session_start();
if(!isset($_SESSION['UserId']))
	header('Location: login.php');
include_once "../php/general-functions.php";
include_once "../settings/config.php";
InsertLoginIdInLogged($_SESSION['UserId']);///php/general-functions.php
$row_count_receipt = 0;
$row_count_payment = 0;
$nearby_medicine_expiry_count = 0;
$expired_count = 0;
$daily_op_count = 0;
$daily_new_op_count = 0;
$total_op = 0;
/*$total_op = GetOpCount();//php/general-functions.php
$expired_count =GetExpiredCount(); //php/general-functions.php
$nearby_medicine_expiry_count = GetNearbyExpiryCount(); //php/general-functions.php
$daily_new_op_count = GetCountForDashboards("hos_op_details","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0"); //php/general-functions.php
$daily_op_count = GetCountForDashboards("hos_op_daily_entry","COUNT(*) AS TOT","DATE(Date) = '".date('Y-m-d')."' AND IsDeleted = 0"); //php/general-functions.php*/
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
<style>
#clock{
    background-color:#0000b3;
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
	background-color:#6666ff;
    letter-spacing:3px;
    font-size:14px;
    font-family:arial,sans-serif;
    color:#fff;
	text-align:center;
}
  </style>
</head>
<body onbeforeunload="logout()"  onload="startTime()">
<div id="wrapper">
  <?php include_once "header.php"; ?>
  <!-- Page Code Start From Here -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <h2 class="page-header">Accountant Dashboard</h2>
                    <hr class="hr-background">
                </div>
                <div class="col-md-4">
                    <div id="clockdate">
    						<div id="clock"></div>
                            <div id="date"></div>	
					</div>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <!--Graph code start--> 
            <!--<div class="row">
                <div class="col-md-6">
                    <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="myChart1" style="width:100%;max-width:600px"></canvas>
                </div>
            </div>-->
            <!--Graph code End-->
            <!--<div class="row"> 
         	<div class="col-md-6"> 
                   <a href="expired-report.php"> 
                   <?php if($expired_count > 0)
					{?>
                    <div class="panel panel-red shadow">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-ban-circle dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $expired_count; ?></div>
                                    <div class="dashboard-content-title">Expired Medicine Report</div>
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
                <div class="col-md-6">
                    <a href="readyto-expiry-report.php">
                    <?php if($nearby_medicine_expiry_count > 0)
					{?>
                    <div class="panel panel-custom-warning">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3" style="color:#FFFFFF">
                                    <i class="glyphicon glyphicon-warning-sign dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right" style="color:#FFFFFF">
                                    <div class="huge"><?php echo $nearby_medicine_expiry_count; ?></div>
                                    <div class="dashboard-content-title">Ready to Expiry Report</div>
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
         </div>-->
          <!--   <div class="row">
                <div class="col-md-6">
                   <a href="view-out-patient-daily-entry.php">
                    <?php if($daily_op_count > 0)
					{?>
                    <div class="panel panel-custom-pink">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3" style="color:#FFFFFF">
                                    <i class="glyphicon glyphicon-user dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right" style="color:#FFFFFF">
                                    <div class="huge"><?php echo $daily_op_count; ?></div>
                                    <div class="dashboard-content-title">Daily OP details</div>
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
                <div class="col-md-6">
                    <a href="view-out-patient-daily-new-entry.php">
                    <?php if($daily_new_op_count > 0)
					{?>
                    <div class="panel panel-custom-pink">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3" style="color:#FFFFFF">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right" style="color:#FFFFFF">
                                    <div class="huge"><?php print $daily_new_op_count; ?></div>
                                    <div class="dashboard-content-title">Daily New OP details</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>

                    </div>
                </div>
                </a>
            </div>
            <div class="row">
            	<div class="col-md-6">
                   <a href="view-op-details.php">
                    <?php if($total_op > 0)
					{?>
                    <div class="panel panel-primary shadow">
                    <?php } else {?>
                    <div class="panel panel-green shadow">
                    <?php } ?>
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-user dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $total_op; ?></div>
                                    <div class="dashboard-content-title">Total OP Details</div>
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
            	
            </div>-->
            
<?php 
//print $_SESSION['dashboard'];
?>
</div><?php include_once "footer.php"; ?>
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/chart.min.js"></script>
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/alertify.min.js"></script>
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
function manager_approve(id)
{
		alertify.confirm('Confirm Message','Are you sure to Approve this Transaction',
		function()
		{
			var transid = id; 
			$.ajax({
				   type:"POST",
				   url:"transaction-approveby-manager.php",
				   data:"transid="+transid,
				   
				   success : function(data)
							 {
									$('#'+id).closest("tr").remove();
									alertify.success('Transaction Approved')
							 }
					});
		},
		function()
		{
			alertify.error('Cancelled')
		
		}
		
		);
				
}
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
