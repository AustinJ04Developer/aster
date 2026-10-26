<?php
include_once("../settings/config.php");
global $dbh;
$account_type = $_GET['accid'];   // account type
//$account_type = $_REQUEST['accid'];   // account type
$acc_type = "";
if($account_type == 0)
{
	$sql = '';
}
else if($account_type == 2)
{
	$sql = "SELECT FDID AS id FROM  pdn_fd_deposit WHERE IsDeleted = 0";
	$acc_type = "FD";
}
else if($account_type == 3)
{
//	$sql = "SELECT FDID AS id FROM  pdn_fd_deposit WHERE IsDeleted = 0";
}
else if($account_type == 4)
{
	$sql = "SELECT SDID AS id,SDNO FROM   pdn_savings_deposit WHERE IsDeleted = 0";
	$acc_type = "SD";
}
/*else if($account_type == 5)
{
}
else if($account_type == 6)
else if($account_type == 7)*/
if(!empty($sql))
{
	$sql_query = $dbh->query($sql);
	$sql_query->setFetchMode(PDO::FETCH_ASSOC);
}
?>
<option disabled selected></option>
<?php
if(!empty($sql))
{
	while($result = $sql_query->fetch())
	{
		$accid = $result['id'];
		$accname = $acc_type."-".$result['SDNO'];
?>
		<option value="<?php echo $result["id"];?>"><?php echo $accname;?></option>
<?php
	}
}
else
{
?>
	<option value="0">0</option>
<?php
}
//encoding array to json format
//print_r($acc_arr);

?>