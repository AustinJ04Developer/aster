<?php
   	session_start();
    include_once "../php/general-functions.php";
	include_once "../php/global-functions.php";
	include_once "edit-alert.php";
	InsertLoginIdInLogged($_SESSION['UserId']);
	//ForceToLogoutUser($_SESSION['UserId']);
	//$selected_date='';

$total_disease = GetDiseaseCount();  //php/general-functions.php
$total_unit = GetUnitCount();  //php/general-functions.php 
$total_medicine_name = GetMedicineNameCount();   //php/general-functions.php 
$total_stockiest = GetStockiestCount();    //php/general-functions.php
$total_medicine_Company = GetMedicineCompanyCount();   //php/general-functions.php
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
<style>
li#vertical-menu{
  margin: 10px 0;
}
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
		print PageAuthorizationMessage();
		exit;
	}
  ?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12 col-md-6">
        <h2 class="page-header">Pharmacy General Report</h2>
        <hr class="hr-background">
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
              <div class="col-lg-12">
                <?php if($_REQUEST['s'] == 1) {?>
                <!--<h4 class="madatory-caption" style="text-align:center;"><span class="mandatory">Amount Deposited Successfully in Savings!!!</span></h4>-->
                <?php } ?>
              </div>
              <!-- /.col-lg-12 -->
   </div>
    <div class="row">
                <div class="col-md-4">
                   <a href="disease-report.php">
                    <div class="panel panel-primary shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $total_disease; ?></div>
                                    <div class="dashboard-content-title">Total Disease Report</div>
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
                    <a href="unit-report.php">
                    <div class="panel panel-yellow shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php print $total_unit; ?></div>
                                    <div class="dashboard-content-title">Total Unit Report</div>
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
                    <a href="medicine-name-report.php">
                    <div class="panel panel-green shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php print $total_medicine_name; ?></div>
                                    <div class="dashboard-content-title">Total Medicine Name Report</div>
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
            
            <div class="row">
                <div class="col-md-4">
                   <a href="stockiest-report.php">
                    <div class="panel panel-red shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo $total_stockiest; ?></div>
                                    <div class="dashboard-content-title">Total Stockiest Report</div>
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
                    <a href="medicine-company-report.php">
                    <div class="panel panel-pink shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php print $total_medicine_Company; ?></div>
                                    <div class="dashboard-content-title">Total Company Report</div>
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
                <!--<div class="col-md-4">
                    <a href="active-users.php">
                    <div class="panel panel-green shadow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="glyphicon glyphicon-plus dashboard"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php //print $new_op; ?></div>
                                    <div class="dashboard-content-title">Medicine Name Entry Report</div>
                                </div>
                            </div>
                        </div>
                            <div class="panel-footer">
                                <span class="pull-left"></span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>

                    </div>
                    </a>
                </div>-->
            </div>
  <!-- Footer -->
  <?php include_once "footer.php"; ?>
  <!-- Footer End -->
</div>
<!-- /#Page wrapper -->
</div>
<!-- /#wrapper -->
<!-- jQuery -->
<script type="text/javascript">

</script>
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
