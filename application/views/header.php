<?php include_once "../php/general-functions.php";?>
  <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img src="images/logo.png" class="img-responsive logo-image" style="margin-top:0px;" ><div id="title-color"></div><!--<span id="title-first-part">CUS - </span><span id="title-second-part">SOFT</span>--></a>
                
            </div>
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
            <?php
           	list($page_access_permission, $page_insert_permission, $page_edit_permission, $page_delete_permission) = UserAuthorizedToViewThisPage();//php/general-functions.php
				if($page_access_permission > 0 or $_SESSION['UserId'] != '' or $_SESSION['UserId'] !=0)
				{
			?>
                <li class="dropdown">
                	
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <?php echo '<span id="welcome">Welcome &nbsp;<b><span class="badge">'.UserName().'</span></b></span>'; ?>
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="edit-profile.php"><i class="glyphicon glyphicon-user user-menu-settings"></i> Edit User Profile</a>
                        </li>
                        <li><a href="change-password.php"><i class="glyphicon glyphicon-edit user-menu-settings"></i> Change Password</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="glyphicon glyphicon-log-out user-menu-settings"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
               <?php } ?>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
          <?php
		  	include_once "primary-side-bar.php";
		  ?>
        </nav>
