<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['username']))
{
$username = $_POST['username'];
$sql = "SELECT count(*) as Users 
                FROM user 
                WHERE UserName = '".$username."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["Users"] > 0)
		{
		echo '<span class="username-exists">Someone already has the User Name <STRONG>'.$username.'</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>