<?php
session_start();
include_once("../settings/config.php");
include_once("../php/global-settings.php");
global $dbh;
$currentinstrument=REGINSTTABLE;
$sysid =trim($_POST['sysid']);
$subsysid=trim($_POST['subsysid']);
$subasmbid=trim($_POST['subasmbid']);
$tagname=trim($_POST['tagname']);
if(($sysid=="null") or ($sysid=="") or ($subsysid=="null") or ($subsysid=="") or ($subasmbid=="null") or ($subasmbid=="") or ($tagname=="null")or ($tagname==""))
{
}
else
{
$sql = "SELECT count(*) as tagnamecount 
                FROM ".$currentinstrument."
                WHERE Tag= '".$tagname."' AND SystemId=".$sysid." AND SubSystemId=".$subsysid." AND SubAssemblyId=".$subasmbid." AND Tag NOT IN (SELECT NEWTag FROM 
				log_invt_reg_inst_component WHERE NEWSystemId=".$sysid." AND NEWSubSystemId=".$subsysid." AND NEWSubAssemblyId=".$subasmbid." AND QCApproved = 1 AND IsDeleted = 1
				ORDER BY LogInsCompId)";
				//print $sql;
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["tagnamecount"] > 0)
		{
		echo '<span class="tagname-exists"><STRONG>'.$tagname.'</STRONG>  Already Exists. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
}
?>