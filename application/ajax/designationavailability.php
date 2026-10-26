<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['designation']))
{
$designation =  addslashes(trim($_POST['designation']));
$sql = "SELECT count(*) as designame
                FROM designation  
                WHERE Designation = '".$designation."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["designame"] > 0)
		{
		echo '<span class="designation-exists">Designation already Exists <STRONG>'.$designation.'</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>