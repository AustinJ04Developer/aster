<?php
 	ob_start();
	session_start();
 	include_once("../../settings/config.php");
	include_once "../global-functions.php";
	include_once("../general-class.php");
	include_once("general-settings-functions.php");
	
	  // Insert Common Discounts
	  if(isset($_POST['save-common-discounts'])) 
	  {
	   global $dbh;
	   $date = checkIsEmpty($_POST['current-date']); //date
	   $from_date = checkIsEmpty($_POST['from-date']); //from date
	   $to_date = checkIsEmpty($_POST['to-date']); // to date
	   $festival = addslashes($_POST['festival']); //festuvak
	   // Check whether check box is checked or not
	   if(isset($_POST['def-festival'])){
		$set_default = 1; //set defauult 
		// If default value of current record is one then set all other records default value as 0
		$sql_default = "UPDATE invt_common_discounts SET IsDefault = 0 WHERE IsDeleted = 0";
		$dbh->exec($sql_default);
		}
		else{
		$set_default = 0; //set defauult 
		}
	   $description = addslashes($_POST['description']); // description
	   $user_id = $_POST['userid'];
	   $edit_mode = $_POST['eid']; // CDId
	   if(!empty($edit_mode))
	   {
	   		$sql = "UPDATE invt_common_discounts SET Date = '".$date."',FromDate = '".$from_date."',ToDate = '".$to_date."',Festival = '".$festival."',IsDefault = ".$set_default.",
					Description = '".$description."',UserId = ".$user_id." 
					WHERE CDId=".$edit_mode;
	   }
	   else
	   {
	   		$sql = "INSERT INTO invt_common_discounts(Date,FromDate,ToDate,IsDefault,Festival,Description,UserId) VALUES (
	   			'".$date."','".$from_date."','".$to_date."',".$set_default.",'".$festival."','".$description."',".$user_id.")";
	   }
	   
	   $success = 0;
	  // $dbh->beginTransaction(); // Begin Transaction
	   try
	   {
	   if(!$dbh->exec($sql)){
		//	$dbh->rollback();
			$success =  -1;
		}
		else
		{
			$success = 1;
		}
		//$dbh->commit();
		} catch (Exception $e) {
		  if($dbh)
			  $dbh->rollBack();
		  echo "Failed: " . $e->getMessage();
		}
  	  	header('Location: ../../views/invt-common-discounts.php?s='.$success);
	  }
	  // Insert GST
	  if(isset($_POST['save-gst'])) 
	  {
	   global $dbh;
	   $date = checkIsEmpty($_POST['current-date']); //date
	   $gst = $_POST['gst']; // unit
	   $description = addslashes($_POST['description']); // description
	   $user_id = $_POST['userid'];
	   $edit_mode = $_POST['eid']; // Unit Id
	   if(!empty($edit_mode))
	   {
	   		$sql = "UPDATE invt_gst SET GST = ".$gst.",Description = '".$description."',UserId = ".$user_id." WHERE GSTId=".$edit_mode;
	   }
	   else
	   {
	   		$sql = "INSERT INTO invt_gst(Date,GST,Description,UserId) VALUES (
	   			'".$date."','".$gst."','".$description."',".$user_id.")";
	   }
	   $success = 0;
	  // $dbh->beginTransaction(); // Begin Transaction
	   try
	   {
	   if(!$dbh->exec($sql)){
		//	$dbh->rollback();
			$success =  -1;
		}
		else
		{
			$success = 1;
		}
		//$dbh->commit();
		} catch (Exception $e) {
		  if($dbh)
			  $dbh->rollBack();
		  echo "Failed: " . $e->getMessage();
		}
  	  	header('Location: ../../views/invt-gst.php?s='.$success);
	  }
	 // End GST Process
	if(isset($_POST['save-managerincharge-settings'])) 
	{
	   global $dbh;
	   $date = checkIsEmpty($_POST['current-date']); //date
	   $period_from = checkIsEmpty($_POST['current-date1']); // date
	   $period_to = checkIsEmpty($_POST['current-date2']); //date
	   $manager_incharge = $_POST['managerincharge']; // User Id
	   $remarks = $_POST['remarks'];
	   $assigned_by = $_SESSION["UserId"];
	   $from_roleid = FindManagerInchargeCurrentRoleId($manager_incharge);
	   $manager_roleid = 3;
	   $sql = "INSERT INTO pdn_manager_incharge_setup(AssigningDate,DateFrom,DateTo,FromRoleId,ToRoleId,Remarks,AssignedBy,Status,ToUser) VALUES (
	   			'".$date."','".$period_from."','".$period_to."',".$from_roleid.",".$manager_roleid.",'".$remarks."',".$assigned_by.",1,".$manager_incharge.")";
	   /*print $sql;
	   exit;*/
	   $success =  1;
	   $dbh->beginTransaction(); // Begin Transaction
				if(!$dbh->exec($sql)){
					$dbh->rollback();
					$success =  0;
				}
	   	$dbh->commit();
  	  	header('Location: ../../views/manager-incharge-settings.php?s='.$success);
	  }
	  // Insert Unit
	  if(isset($_POST['save-unit'])) 
	  {
	   global $dbh;
	   $date = checkIsEmpty($_POST['current-date']); //date
	   $unit = addslashes($_POST['unit']); // unit
	   $unit_short_name = addslashes($_POST['unit-shortname']); // unit short name
	   $description = addslashes($_POST['description']); // description
	   $user_id = $_POST['userid'];
	   $edit_mode = $_POST['eid']; // Unit Id
	   if(!empty($edit_mode))
	   {
	   		$sql = "UPDATE invt_unit SET Unit = '".$unit."',UnitShortName = '".$unit_short_name."',Description = '".$description."',UserId = ".$user_id." WHERE UnitId=".$edit_mode;
	   }
	   else
	   {
	   		$sql = "INSERT INTO invt_unit(Date,Unit,UnitShortName,Description,UserId) VALUES (
	   			'".$date."','".$unit."','".$unit_short_name."','".$description."',".$user_id.")";
	   }
	   $success = 1;
	   $dbh->beginTransaction(); // Begin Transaction
	   if(!$dbh->exec($sql)){
			$dbh->rollback();
			$success =  0;
		}
	   	$dbh->commit();
  	  	header('Location: ../../views/invt-unit.php?s='.$success);
	  }
	  	
?>
