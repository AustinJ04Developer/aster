<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['role']))
{
$role=  addslashes(trim($_POST['role']));
$sql = "SELECT count(*) as rolename
                FROM role  
                WHERE RoleName= '".$role ."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["rolename"] > 0)
		{
		echo '<span class="role-exists">Role already Exists <STRONG>'.$role.'</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>