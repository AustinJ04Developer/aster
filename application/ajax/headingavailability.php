<?php
include_once("../settings/config.php");
global $dbh;
if(isset($_POST['heading']))
{
$heading = $_POST['heading'];

$sql = "SELECT count(*) as headingname 
                FROM page_sidemenu_main_heading 
                WHERE MainHeading = '".$heading."'";
  
		$queryrersult = $dbh->query($sql);
		$queryrersult->setFetchMode(PDO::FETCH_ASSOC);
		$result = $queryrersult->fetch();
		if($result["headingname"] > 0)
		{
		echo '<span class="headingname-exists">Already Heading Name <STRONG>'.$heading.'</STRONG>. Try another?</span>';
		}
		else
		{
		echo 'OK';
		}
		
		 
}
?>