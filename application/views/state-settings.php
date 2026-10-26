<?php
   	session_start();
	if(!isset($_SESSION['UserId']))
		header('Location: login.php');
    include_once "../php/general-functions.php";
	InsertLoginIdInLogged($_SESSION['UserId']);
	
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
</head>
<body>
    <div id="wrapper">
      	<?php include_once "header.php"; ?>
        <?php
		    include_once "header.php";
		    list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage(); //php/general-functions.php
			
		?>
        <!-- Page Code Start From Here -->
        <div id="page-wrapper"> 
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">Create / Update State</h2>
                    <hr class="hr-background">
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
           <div class="row">
              <div class="col-lg-12">
                <h6 class="madatory-caption"><span class="mandatory">*</span> Fields are mandatory</h6>
              </div>
              <!-- /.col-lg-12 -->
   		   </div>
           <!-- /.row -->
		    <!-- Page Code Start From Here -->
            <!-- .row -->
            <div class="row">
            	<div class="col-md-4col-sm-4 col-xs-4">
                 <fieldset id="major-feild-set" style="margin-top:15px;">
                	<?php 
					  if($is_inserted_in_db == 1)
					  	print '<div class="success-message">Data Saved Successfully</div>';
					  if($is_inserted_in_db == 2)
					  	print '<div class="success-message">Data Updated Successfully</div>';
					   if($is_inserted_in_db == 3)
					  	print '<div class="success-message">Data Deleted Successfully</div>';
						if($is_inserted_in_db == 30)
			            print '<div class="success-message">Can\'t Delete. Role Used in User Registration</div>';
						if($is_add_role == 1)
			            print '<div class="success-message">You do not have permission to add role</div>';
					?>
                    <form role="frm-account-type" method="post" action="../php/process.php">
                      
                      <div class="form-group">
                        <label for="role">State:</label><span class="mandatory">*</span>
                        <input type="text" class="form-control" id="state" name="state" style="" placeholder="Enter State Name" maxlength="25" value="<?php print $state ?>" required autofocus /> <span id="status-role"></span> 
                      </div>
                      <div class="form-group">
                      	 <button type="submit" class="btn btn-md btn-info" id="editAlert" name="submit" style="display:none;"></button>
                      </div>
                   </form>
                   </fieldset>
                </div>
                <div class="col-md-8">
                    <table id="designation-details" class="table table-striped table-bordered" cellspacing="0" width="100%">
                      <thead>
                        <tr class="header-row">
                          <th>SlNo</th>
                          <th>State</th>
                          <th>Edit</th>
                          <th>Delete</th>
                        </tr>
                      </thead>
                      <tbody class="table-coloum-style">
                        <?php 
						
						
                        ?>
                      </tbody>
                    </table>
      			</div>
      
            </div>
            <!-- /.row -->
            <!-- Page Code End -->
        </div>
        <!-- Footer -->
        <?php include_once "footer.php"; ?>
        <!-- Footer End --> 
		</div>
  		<!-- /#Page wrapper -->
    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
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
