<?php
   	session_start();
	if(!isset($_SESSION['UserId']))
		header('Location: login.php');
	global $is_inserted_in_db;
	$is_inserted_in_db = '';
	include_once "../settings/config.php";
	if(!empty($_REQUEST['s']))
	$is_inserted_in_db = $_REQUEST['s'];
	include_once "../php/general-functions.php";
		InsertLoginIdInLogged($_SESSION['UserId']);
	//ForceToLogoutUser($_SESSION['UserId']);

	global $pagename;
	global $description;
	global $link;
	global $role;
	$role = '';
	if(!empty($_REQUEST['r']))
	$role = $_REQUEST['r'];

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
<link href="css/bootstrap-combobox.css" rel="stylesheet" type="text/css">
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
        <h2 class="page-header">Setup User Role Privilages</h2>
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
    <!-- .row -->
  <form name="page" method="post" action="../php/user-process.php" autocomplete="off">
    <div class="row">
      <div class="col-md-4 col-sm-12 col-xs-12">
        <?php 
			if($is_inserted_in_db == 1)
			print '<div style="text-align:center;color:#FF0000;font-weight:bold;">Data Saved Successfully</div>';
		?>
		  <div class="form-group">
            <label>Select Role </label><span class="mandatory">*</span>
            <select class="form-control combobox" id="role" name="role" title="Role Name must not be empty" required autocomplete="off" autofocus >
			  <option disabled selected></option>
				<?php
					$option_details = FillValuesInPage('RoleId','RoleName',$role,'role'); //General Functions
			 		echo $option_details;
				?>
              </select>
              <input type="hidden" name="userid" value="<?php echo $_SESSION['UserId'];?>" >
          </div>
      </div>
    </div>
	<div class="row">
		<div class="col-md-12">
			<span id="status"></span>
		</div>
	</div>
   <!-- <div class="row">
    	<div class="col-md-12">
       	 <table id="designation-details" class="table table-striped table-bordered" cellspacing="0">
          <thead>
            <tr class="header-row">
              <th class="table-header">SlNo</th>
              <th class="table-header">Page</th>
              <th class="table-header">View</th>
              <th class="table-header">Add</th>
              <th class="table-header">Edit</th>
              <th class="table-header">Delete</th>
            </tr>
          </thead>
          <tbody class="table-coloum-style">
            <?php 
			//Pass argument as field names seperated by comma and table name
		//	 $privilages_details = ShowAllPages('PageId','PageName','page'); //General Functions
		//	 echo $privilages_details;
			// $count = substr_count($privilages_details, "<tr>");
 			?>
          </tbody>
        </table>
        </div>
    </div>-->
   
  </form>
  
    <!-- /.row -->
    <!-- Page Code End -->
    <!-- Footer -->
    <!-- Footer End -->
  </div>
 <?php } ?>
  <!-- /#Page wrapper -->
</div> <?php include_once "footer.php"; ?>
<!-- /#wrapper -->
<!-- jQuery -->
<script src="js/jquery-1.9.1.min.js"></script>

<script src="js/bootstrap-datepicker.js"></script>
<script src="js/show-date-picker.js"></script>
<!-- Bootstrap Core JavaScript -->
<script type="text/javascript">
$(document).ready(function() {
    $('#page-details').DataTable();
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
$('.combobox').combobox();
$(".combobox").attr("placeholder", "Select Role");
} );
$(window).load(function() { // can also try on document ready
    $('input[autocomplete]').removeAttr('autocomplete');
});
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

</body>
</html>
