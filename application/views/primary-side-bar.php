<?php
if(!isset($_SESSION['UserId']))
	header('Location: login.php');
include_once "../php/general-functions.php";
include_once "../settings/constants.php";
$flag_dashboard = 0;
//$bank_heading = COMPANYNAME;
$bank_heading = "Aster Public Development Foundation";
//$bank_sub_heading =  "Panachamoodu";
?>
<div style="text-align:center;font-size:25px;margin-top:7px;font-family: arial black;color:#fff;" class="bank-title"> <?php echo $bank_heading; ?></div>
<div class="navbar-default sidebar" role="navigation" style="margin-top:9px;">
    <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
         <?php if($_SESSION['dashboard'] == 'Admin') //Admin Role Start
		       {
			   		$flag_dashboard = 1;
   	     ?> 
            <li>
                <a href="admin-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Admin Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Admin Role End ?>
            <?php if($_SESSION['dashboard'] == 'Managing Director') //MD Role Start
		       {
			   		$flag_dashboard = 2;
   	     ?> 
            <li>
                <a href="managing-director-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Managing Director Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // MD Role End ?>
            <?php if($_SESSION['dashboard'] == 'Manager') //Mechanical Engineer  Role Start
		       {
			   		$flag_dashboard = 3;
   	     ?> 
            <li>
                <a href="manager-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Manager Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Mechanical Engineer  Role End ?>
			 <?php if($_SESSION['dashboard'] == 'Operator') //Mechanical Engineer  Role Start
		       {
			   		$flag_dashboard = 8;
   	     ?> 
            <li>
                <a href="data-entry-operator-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Accountant Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Mechanical Engineer  Role End ?>
            <?php if($_SESSION['dashboard'] == 'Accountant') //QC Role Start
		       {
			   		$flag_dashboard = 4;
   	     ?> 
            <li>
                <a href="accountant-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Accountant Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // QC Role End ?>
            
            
             <?php if($_SESSION['dashboard'] == 'Clerk') //Welding Role Start
		       {
			   		$flag_dashboard = 5;
   	     ?> 
            <li>
                <a href="clerk-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Clerk Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Welding Role End ?>
            <?php if($_SESSION['dashboard'] == 'Office Assistant') //NDT Role Start
		       {
			   		$flag_dashboard = 6;
   	     ?> 
            <li>
                <a href="office-assistant-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Office Assistant Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // NDT Role End ?>
            <?php if($_SESSION['dashboard'] == 'Appraiser') //Instrumentation Team Role Start
		       {
			   		$flag_dashboard = 7;
   	     ?> 
            <li>
                <a href="appraiser-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Appraiser Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Instrumentation Team Role End ?>
            <?php if($_SESSION['dashboard'] == 'ElectricalTeam') //Electrical Team Role Start
		       {
			   		$flag_dashboard = 8;
   	     ?> 
            <li>
                <a href="electrical-team-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Electrical Team Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Electrical Team Role End ?>
            
            <?php if($_SESSION['dashboard'] == 'CalibrationTeam') //Calibration Team Role Start
		       {
			   		$flag_dashboard = 9;
   	        ?> 
            <li>
                <a href="calibration-team-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Calibration Team Dashboard</a>
            </li>
             <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Calibration Team Role End ?>
             
            <?php if($_SESSION['dashboard'] == 'CleaningTeam') //Cleaning Team Role Start
		       {
			   		$flag_dashboard = 10;
   	        ?> 
            <li>
                <a href="cleaning-team-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Cleaning Team Dashboard</a>
            </li>
             <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Cleaning Team Role End ?>
             
             <?php if($_SESSION['dashboard'] == 'PicklingTeam') //Pickling Team Role Start
		       {
			   		$flag_dashboard = 11;
   	        ?> 
            <li>
                <a href="pickling-team-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Pickling  Team Dashboard</a>
            </li>
            <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Pickling Team Role End ?>
             
            <?php if($_SESSION['dashboard'] == 'LeakCheckTeam') //Leakcheck Team Role Start
		       {
			   		$flag_dashboard = 12;
   	        ?> 
            <li>
                <a href="leakcheck-team-dashboard.php"><i class="fa fa-dashboard fa-fw side-menu-settings"></i> Leakcheck Team Dashboard</a>
            </li>
             <?php echo $dashboard = Dashboard($_SESSION['RoleId']);} // Leakcheck Team Role End ?>
             
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
    <?php
		if($flag_dashboard == 1)
			$_SESSION['dashboard'] = 'Admin';
		else if($flag_dashboard == 2)
			$_SESSION['dashboard'] = 'Managing Director';
		else if($flag_dashboard == 3)
			$_SESSION['dashboard'] = 'Branch Manager';
		else if($flag_dashboard == 4)
			$_SESSION['dashboard'] = 'Accountant';
		else if($flag_dashboard == 5)
			$_SESSION['dashboard'] = 'Clerk';
		else if($flag_dashboard == 6)
			$_SESSION['dashboard'] = 'Office Assistant';
		else if($flag_dashboard == 7)
			$_SESSION['dashboard'] = 'Appraiser';

	?>
</div>
<!-- /.navbar-static-side -->
