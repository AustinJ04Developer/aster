<?php
 	ob_start();
 	include_once("../settings/config.php");
	include_once "global-functions.php";
	if(isset($_POST['submit']))
	{
	   if($_POST['eid'] != 0) //If Edit Mode Update designation data
	   {
		   $result = DataUpdate($_POST['eid'],$_POST['designation'], $_POST['description'], $_POST['userid']);
		}
	   else // If Insert mode, Insert designation data
	   {
		   $result = DataInsert($_POST['designation'], $_POST['description'], $_POST['userid']);
	   }
	}
	function  DataInsert($designation,$description,$userid)//Data Insert in Designation
	{
	   global $dbh;
	   try
		{
			$stmt = "INSERT INTO designation (Designation, Description, UserId) VALUES (:designation, :description, :userid)";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':designation'=> $designation,':description'=> $description,':userid'=> $userid));
			if($success == 1)
			{
				header('Location: ../views/designation.php?s=1');
			}
			else
			{
				header('Location: ../views/designation.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('designation-process','DataInsert',$e->getMessage());
		}
	}
	function  DataUpdate($designationid,$designation,$description,$userid)//Data Update in Designation
	{
	   global $dbh;
	   try
		{
			$stmt = "UPDATE designation SET Designation = :designation, Description = :description, UserId = :userid
			         WHERE DesignationId = :designationid";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':designationid'=> $designationid,':designation'=> $designation,':description'=> $description,':userid'=> $userid));
			if($success == 1)
			{
				header('Location: ../views/designation.php?s=2');
			}
			else
			{
				header('Location: ../views/designation.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('designation-process','DataUpdate',$e->getMessage());
		}
	}
?>
