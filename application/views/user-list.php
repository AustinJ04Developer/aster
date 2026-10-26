<?php
	session_start();
	if(!isset($_SESSION['UserId']))
		header('Location: login.php');
	include_once "../settings/config.php";
	include_once "../php/general-functions.php";
	$success_user_approval = '';
	if(!empty($_REQUEST['s']))
	$success_user_approval = $_REQUEST['s'];
		InsertLoginIdInLogged($_SESSION['UserId']);
	//ForceToLogoutUser($_SESSION['UserId']);

/*	global $designation;
	global $description;
*/	//include_once "../php/designation-process.php";
	/*$is_edit_mode = $_REQUEST['eid'];
	if($is_edit_mode != '') // Edit Process
	{
		list($designation,$description) = GetDesignationDetails($is_edit_mode);
	}
	$is_delete_mode = $_REQUEST['did']; // Delete Process
	if($is_delete_mode != '')
	{
		DeleteDesignation($is_delete_mode);
	}*/
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
</head>
<body>
<div id="wrapper">
  <?php include_once "header.php"; ?>
   <?php
   include_once "header.php";
 list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage(); ///php/general-functions.php
	if($page_access_permission == 0)
	{
		print PageAuthorizationMessage();
	}
	else
	{
	?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">User List</h2>
        <hr class="hr-background">
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <!-- Page Code Start From Here -->
    <!-- .row -->
    <div class="row">
               
      <div class="col-md-12">
        <table id="designation-details" class="table table-striped table-bordered" cellspacing="0">
          <thead>
            <tr class="header-row">
              <th>SlNo</th>
              <th>Name</th>
              <th>User Name</th>
              <th>Designation</th>
              <th>Status</th>
              <th>Active</th>
              <th>Block</th>
              <th>Promote</th>
            </tr>
          </thead>
          <tbody class="table-coloum-style">
            <?php 
			//Pass argument as field names seperated by comma and table name
			 $designation_details = BlockUser('UserId','Name','UserName','Designation','block-profile.php','active-profile.php','promote-user.php','user'); //General Functions
			 echo $designation_details;
			?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- /.row -->
    <!-- Page Code End -->
    <!-- Footer -->
 
    <!-- Footer End -->
  </div>   <?php include_once "footer.php"; ?>
  <!-- /#Page wrapper -->
  <?php } ?>
</div>
<!-- Modal -->

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
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#designation-details').DataTable();
	$('a[data-confirm]').click(function(ev) {
		var href = $(this).attr('href');
		if (!$('#dataConfirmModal').length) {
			$('#page-wrapper').append('<div id="dataConfirmModal" class="modal" role="dialog" aria-labelledby="dataConfirmLabel" aria-hidden="true"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h3 id="dataConfirmLabel">Please Confirm</h3></div><div class="modal-body"></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button><a class="btn btn-primary" id="dataConfirmOK">OK</a></div></div>');
		} 
		$('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
		$('#dataConfirmOK').attr('href', href);
		$('#dataConfirmModal').modal({show:true});
		return false;
	});

} );
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
