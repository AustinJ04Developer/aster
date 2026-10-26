<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['username']))
{
$username = $_POST['username'];
$sql = "SELECT count(*) as Total 
                FROM invt_item_master
                WHERE ItemName = '".$username."'";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["Total"] > 0)
		{
		echo '<span class="username-exists"><STRONG>Item Name Already Exist</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>