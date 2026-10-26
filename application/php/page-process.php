<?php
 	ob_start();
 	include_once("../settings/config.php");
	include_once "global-functions.php";
	if(isset($_POST['submit']))
	{
	   if($_POST['eid'] != '') //If Edit Mode Update page data
		   {
		   $result = DataUpdate($_POST['eid'],$_POST['pagename'],$_POST['link'],$_POST['pagedescription'], $_POST['userid'],$_POST['mainheading'],$_POST['icon']);
		   }
	   else // If Insert mode, Insert division data
		   {
		   $result = DataInsert($_POST['pagename'],$_POST['link'],$_POST['pagedescription'], $_POST['userid'], $_POST['mainheading'],$_POST['icon']);
		   }
	}
	function  DataInsert($pagename,$link,$description,$userid,$psmid,$icon)//Data Insert in page
	{
	   global $dbh;
	   try
		{
					
			$stmt ="INSERT INTO page(PageName,Link,Description,UserId,PSMID,IconName) VALUES (:pagename,:link,:description,:userid,:psmid,:icon)";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':pagename'=> $pagename,':link'=>$link,':description'=> $description,':userid'=> $userid,':psmid'=> $psmid,':icon'=> $icon));

			if($success == 1)
			{
				header('Location: ../views/page.php?s=1');
			}
			else
			{
				header('Location: ../views/page.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('page-process','DataInsert',$e->getMessage());
		}
	}
	function  DataUpdate($pageid,$pagename,$pagelink,$description,$userid,$psmid,$icon)//Data Update in Division
	{
	   global $dbh;
	   try
		{
			$stmt = "UPDATE page SET PageName = :pagename, Link = :link, Description = :description, UserId = :userid, PSMID = :psmid, IconName = :icon 
			         WHERE PageId = :pageid";
			$query = $dbh->prepare($stmt);
			$success = $query->execute(array(':pageid'=> $pageid,':pagename'=> $pagename,':link'=> $pagelink,':description'=> $description,':userid'=> $userid,':psmid'=> $psmid,':icon'=> $icon));
			if($success == 1)
			{
				header('Location: ../views/page.php?s=1');
			}
			else
			{
				header('Location: ../views/page.php?s=0');
			}
		}
		catch (PDOException $e)
		{
			 ErrorTracking('page-process','DataUpdate',$e->getMessage());
		}
	}
?>
