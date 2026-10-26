<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['batchno']))
{
	$batchno = $_POST['batchno'];
	$sql = "SELECT count(*) as batchnocount
					FROM hos_pharm_medicine_purchase_stock_details
					WHERE BatchCode = '".$batchno."' AND IsDeleted=0";
	/*print $sql;
	exit;*/
			$queryresult = $dbh->query($sql);
			$queryresult->setFetchMode(PDO::FETCH_ASSOC);
			$result = $queryresult->fetch();
			if($result["batchnocount"] > 0)
			{
			echo '<span class="rollno-exists" style="color:#FF0000;"><STRONG>Batch Code '.$batchno.'</STRONG> already exists.</span>';
			}
			else
			{
			echo '1';
			}
}
?>

