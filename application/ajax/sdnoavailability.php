<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['sdno']))
{
$sdno =  addslashes(trim($_POST['sdno']));
$sql = "SELECT count(*) as sdnos
                FROM pdn_savings_deposit  
                WHERE SDNO = '".$sdno."' AND IsDeleted=0";
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["sdnos"] > 0)
		{
		echo '<span class="designation-exists">SDNO already Exists <STRONG>'.$sdno.'</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>