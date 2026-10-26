<?php
   	session_start();
//	error_reporting(0);
	if(!isset($_SESSION['uid']))
		header('Location: login.php');
	include_once "../settings/config.php";
	global $is_inserted_in_db;
//	$_SESSION['UserId'] = 16;
//print 'jjjjj'.$_SESSION['UserId'];
	$is_inserted_in_db = $_REQUEST['s'];
	include_once "../php/general-functions.php";
	global $pagename;
	global $description;
	global $link;
	global $role;
	$role = $_SESSION['RoleId'];
	//include_once "../php/designation-process.php";
	$is_edit_mode = $_REQUEST['eid'];
	if($is_edit_mode != '') // Edit Process
	{
		list($pagename,$link,$description,$selected_heading) = GetPageDetails($is_edit_mode); //php/general-functions.php
	}
	$is_delete_mode = $_REQUEST['did']; // Delete Process
	if($is_delete_mode != '')
	{
		DeletePage($is_delete_mode);
	}
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
  <?php
   include_once "header.php";
   list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage(); //php/general-functions.php
	/*if($page_access_permission == 0)
	{
		print PageAuthorizationMessage();
	}
	else
	{*/
	?>
  <!-- Page Code Start From Here -->
  <div id="page-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <h2 class="page-header">Create Page</h2>
        <hr class="hr-background">
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <!-- Page Code Start From Here -->
    <!-- .row -->
     <div class="row">
      <div class="col-lg-12">
        <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <div class="row">
      <div class="col-md-4">
        <?php 
			if($is_inserted_in_db == 1)
			print '<div class="success-message">Data Saved Successfully</div>';
		?>
        <form name="page" method="post" action="../php/page-process.php" autocomplete="off">
          <div class="form-group">
            <label>Page Name</label><span class="mandatory">*</span>
            <input type="text" placeholder="Enter Page Name" class="form-control" name="pagename" id="page-name" maxlength="100" value="<?php print $pagename ?>" required autocomplete="off" autofocus>
			<input style="display:none" type="text" name="fakeusernameremembered"/>
			<input style="display:none" type="password" name="fakepasswordremembered"/>
          </div>
		  <div class="form-group">
            <label>Side Menu Main Heading</label><span class="mandatory">*</span>
            <select class="form-control combobox" id="mainheading" name="mainheading" title="Main heading must not be empty" required autocomplete="off" placeholder="Select Side Menu Main Heading">
				<?php
				 
					$option_details = FillSideMenuMainHeadingInPage('PSMID','MainHeading',$role,'page_sidemenu_main_heading',$selected_heading); ////php/general-functions.php
			 		echo $option_details;
				?>
              </select>
          </div>
          <div class="form-group">
            <label>Page Link</label><span class="mandatory">*</span>
            <input type="text" placeholder="Enter Page Link" class="form-control" name="link" id="link" maxlength="50" value="<?php print $link?>" autocomplete="off" required>
          </div>
          <div class="form-group">
            <label>Icon</label><span class="mandatory">*</span>
            <input type="text" placeholder="Enter icon " class="form-control" name="icon" id="icon" maxlength="50" value="<?php print $icon?>" autocomplete="off" required>
          </div>
          <div class="form-group">
            <label for="description">Page Description</label>
            <textarea class="form-control" rows="5" name="pagedescription" id="page-description" placeholder="Enter Page Description"><?php print $description?></textarea autocomplete="off">
            <input type="hidden" name="eid" value="<?php print $is_edit_mode;?>" />
            <input type="hidden" name="userid" value="<?php print $_SESSION['UserId'];?>" />
          </div>
          <div class="form-group"> <a href="page.php">
            <button type="button" class="btn btn-md btn-info btn-font" id="submit" name="submit">New</button>
            </a>
            <button type="submit" class="btn btn-md btn-info btn-font" id="submit" name="submit">Save</button>
          </div>
        </form>
      </div>
      <div class="col-md-8">
	     <table id="page-details" class="table table-striped table-bordered" cellspacing="0">
          <thead>
            <tr class="header-row">
              <th>SlNo</th>
              <th>Page</th>
              <th>Description</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody class="table-coloum-style">
            <?php 
			//Pass argument as field names seperated by comma and table name
			 $page_details = UserRelatedMaster('PageId','PageName','Description','page.php','page'); //php/general-functions.php
			 echo $page_details;
			?>
          </tbody>
        </table>
        <!--<table id="page-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
          <thead>
            <tr class="header-row">
              <th>SlNo</th>
              <th>Page</th>
              <th>Description</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody class="table-coloum-style">
            <?php 
				//Pass argument as field names seperated by comma and table name
				// $page_details = UserRelatedMaster('PageId','PageName','Description','page.php','page'); //General Functions
				// echo $page_details;
			?>
          </tbody>
        </table>-->
      </div>
    </div>
    <!-- /.row -->
    <!-- Page Code End -->
    <!-- Footer -->
    <!-- Footer End -->
  </div>
  <?php include_once "footer.php"; ?>
  <!-- /#Page wrapper -->
  <?php  //}
 
  ?>
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
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="js/bootstrap-combobox.js"></script>
<script type="text/javascript">
$(document).ready(function() {
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
$('#page-details').DataTable();
$('.combobox').combobox();
$(".combobox").attr("placeholder", "Select Side Menu Main Heading");

} );
$(window).load(function() { // can also try on document ready
    $('input[autocomplete]').removeAttr('autocomplete');
});
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
