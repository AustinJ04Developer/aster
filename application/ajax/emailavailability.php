<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['emailid']))
{
$emailid = $_POST['emailid'];
$sql = "SELECT count(*) as Email 
                FROM user 
                WHERE EmailId = '".$emailid."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["Email"] > 0)
		{
		echo '<span class="username-exists"><STRONG>'.$emailid.'</STRONG> already exists.</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>