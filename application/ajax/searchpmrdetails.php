<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['bcodeid']))
{
/*print 1;
exit;*/
$batchcode = $_POST['bcodeid'];
$sql = "SELECT MPSD.PMR,MPSD.BatchCode,MPSD.BalanceQuantity,MND.MedicineName,MND.MID,MPSD.MPSDID,MPSD.GSTPercentage
                FROM hos_pharm_medicine_purchase_stock_details MPSD LEFT JOIN hos_pharm_medicine_details MND ON MPSD.MID=MND.MID 
                WHERE MPSD.MPSDID=".$batchcode." AND MPSD.IsDeleted = 0";
		$sql_query = $dbh->query($sql);
		$sql_query->setFetchMode(PDO::FETCH_ASSOC);
		$result = $sql_query->fetch();
		/*print $sql;
exit;*/
		if(!empty($result["PMR"]))
		{
			$str_pmr_details = $result['PMR']."|".$result['BalanceQuantity']."|".$result['GSTPercentage']."|".$result['BatchCode']."|".$result['MPSDID']."|".$result['MPSDID'];
			echo $str_pmr_details;
			
		}
		else
		{
		echo 'OK';
		}
}
?>