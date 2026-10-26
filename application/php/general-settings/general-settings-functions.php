<?php
 	ob_start();
 	include_once("../settings/config.php");
	
	// Fill all fields in the gst page
	function FillCommonDiscountFields($discount_id)
	{
		global $dbh;
		try
		{
			$sql = "SELECT FromDate,ToDate,Festival,IsDefault, Description
					FROM  invt_common_discounts
					WHERE IsDeleted = 0 AND CDId=".$discount_id;
			$query_result = $dbh->query($sql);
			$query_result->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_result->fetch();
			return array($result['FromDate'],$result['ToDate'],$result['Festival'],$result['IsDefault'],$result['Description']);
		}
		catch (PDOException $e)
		{
			ErrorTracking('general-settings-functions','FillCommonDiscountFields',$e->getMessage());
		}
	}
	// Fill all fields in the gst page
	function FillGstFields($gst_id)
	{
		global $dbh;
		try
		{
			$sql = "SELECT GST, Description
					FROM invt_gst
					WHERE IsDeleted = 0 AND GSTId=".$gst_id;
			$query_result = $dbh->query($sql);
			$query_result->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_result->fetch();
			return array($result['GST'],$result['Description']);
		}
		catch (PDOException $e)
		{
			ErrorTracking('general-settings-functions','FillGstFields',$e->getMessage());
		}
	}
	
	function GetAllUsersNameWithUserId() // This function is used to list all names in the user table for assigning manager incharge
	{
		global $dbh;
		try
		{
			$sql = "SELECT Name, UserId 
					FROM user
					WHERE IsDeleted=0 AND RoleId > 3 AND RoleId < 8 ORDER BY UserId ASC";
			$query_section = $dbh->query($sql);
			$query_section->setFetchMode(PDO::FETCH_ASSOC);
			$option = '';
			while ($result = $query_section->fetch())
			{
			 // if($result['AMID'] == $account_head_id)
				  $option .= '<option value="'.$result['UserId'].'">'.$result['Name'].'</option>';
			 // else
//				  $option .= '<option value="'.$result['AMID'].'">'.$result['AMID'].'|'.$result['AccountName'].'</option>';
			}
			return $option;
		}
		catch (PDOException $e)
		{
			$e->getMessage();
			//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
		}
	}
	function AssignedByUsersName() // This function is used to list all names in the user table for assigning manager incharge
	{
		global $dbh;
		try
		{
			$sql = "SELECT Name, UserId 
					FROM user
					WHERE IsDeleted=0 AND UserId  = ".$_SESSION["UserId"];
			$query_section = $dbh->query($sql);
			$query_section->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_section->fetch();
			return $result["Name"];
		}
		catch (PDOException $e)
		{
			$e->getMessage();
			//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
		}
	}
	function CurrentUsersName($userid) // This function is used to list all names in the user table for assigning manager incharge
	{
		global $dbh;
		try
		{
			$sql = "SELECT Name, UserId 
					FROM user
					WHERE IsDeleted=0 AND UserId  = ".$userid;
			$query_section = $dbh->query($sql);
			$query_section->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_section->fetch();
			return $result["Name"];
		}
		catch (PDOException $e)
		{
			$e->getMessage();
			//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
		}
	}
	function FindManagerInchargeCurrentRoleId($manager_incharge) // This function is used to list all names in the user table for assigning manager incharge
	{
		global $dbh;
		try
		{
			$sql = "SELECT RoleId 
					FROM user
					WHERE IsDeleted=0 AND UserId  = ".$manager_incharge;
			$query_section = $dbh->query($sql);
			$query_section->setFetchMode(PDO::FETCH_ASSOC);
			$result = $query_section->fetch();
			return $result["RoleId"];
		}
		catch (PDOException $e)
		{
			$e->getMessage();
			//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
		}
	}
	function FindManagerInchargeCurrentRole() // This function is used to list all  manager incharge
	{
		global $dbh;
		try
		{
			$sql = "SELECT ToUser, DateFrom,  DateTo,AssignedBy, Remarks, AssignedBy
					FROM pdn_manager_incharge_setup
					WHERE IsDeleted=0 AND Status= 1";
			//print $sql;
			$query_section = $dbh->query($sql);
			$query_section->setFetchMode(PDO::FETCH_ASSOC);
			$table = '';
			$slno = 1;
			while($result = $query_section->fetch())
			{
				$manager_incharge = CurrentUsersName($result["ToUser"]);
				$perid_from = date('d/m/Y',strtotime($result["DateFrom"]));
				$perid_to = date('d/m/Y',strtotime($result["DateTo"]));
				$assign_by = CurrentUsersName($result["AssignedBy"]);
				$table .= '<tr>';
				$table .= '<td>'.$slno.'</td><td>'.$manager_incharge.'</td><td>'.$perid_from.'</td><td>'.$perid_to.'</td><td>'.$assign_by.'</td>';
				$table .= '</tr>';
				$slno++;
			}
			return $table;
		}
		catch (PDOException $e)
		{
			$e->getMessage();
			//ErrorTracking('general-functions','UserRegistrationMasterFields',$e->getMessage());
		}
	}
?>
