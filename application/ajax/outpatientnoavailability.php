<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['OPNO']))
{
$opno = addslashes(trim($_POST['OPNO']));
$sql = "SELECT count(*) as outpatientcount
                FROM hos_op_details 
                WHERE OPNO = '".$opno."' AND IsDeleted=0";
/*print $sql;
exit;*/
		$queryresult = $dbh->query($sql);
		$queryresult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryresult->fetch();
		if($result["outpatientcount"] > 0)
		{
		echo '<span class="rollno-exists" style="color:#FF0000;"><STRONG>OP Number '.$opno.'</STRONG> already exists.</span>';
		}
		else
		{
		echo '';
		}
}
?>