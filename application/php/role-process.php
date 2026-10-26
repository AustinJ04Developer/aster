<?php
 	ob_start();
 	include_once("../settings/config.php");
	include_once "global-functions.php";
	if(isset($_POST['submit']))
	{
	   if($_POST['eid'] != '') //If Edit Mode Update role data
	   {
		   $result = DataUpdate($_POST['eid'],$_POST['current-date'],$_POST['role'],$_POST['description'],$_POST['userid']);
	   }
	   else // If Insert mode, Insert role data
	   {
		   // $result = DataInsert($_POST['current-date'], $_POST['role'], $_POST['description'],$_POST['userid']);
		   header('Location: ../views/role.php?p=1');
	   }
	}
	function  DataInsert($date,$role,$description,$userid)//Data Insert in ROle
	{
	   global $dbh;
	   try
		{
			$date = checkIsEmpty($date); 
			$stmt = "INSERT INTO role(Date,RoleName,Description,UserId) VALUES (:date, :role, :description, :userid)";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':date'=> $date,':role'=> $role,':description'=> $description,':userid'=> $userid));
			if($success == 1)
			{
				header('Location: ../views/role.php?s=1');
			}
			else
			{
				header('Location: ../views/role.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('role-process','DataInsert',$e->getMessage());
		}
	}
	function  DataUpdate($roleid,$date,$role,$description,$userid)//Data Update in Role
	{
	   global $dbh;
	   try
		{
			$stmt = "UPDATE role SET Date=:date,RoleName = :role, Description = :description, UserId = :userid
			         WHERE RoleId = :roleid";
					
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':roleid'=> $roleid,':date'=>$date,':role'=> $role,':description'=> $description,':userid'=> $userid));
			if($success == 1)
			{
				header('Location: ../views/role.php?s=2');
			}
			else
			{
				header('Location: ../views/role.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('role-process','DataUpdate',$e->getMessage());
		}
	}
?>
