<?php
session_start();
include_once "../settings/config.php";
include_once("../php/global-functions.php");
$uid=$_SESSION['UserId'];
logoutuser($uid);
session_destroy();
$session_logout = $_REQUEST['f'];
if($session_logout == 1)
	header('Location: index.php?f=1');
else
	header('Location: index.php');

?>