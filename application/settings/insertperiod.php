<?php
$hostname = "localhost";
$user = "root";
//$pass = "RGYhnqUzrHAjnc26";
 $pass = "";
// $db = "cus-testing";
// $db = "cus1";
$db = "mpdn";
global $dbh;
/*db connection*/
try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$db",$user,$pass);
}
catch (PDOException $e) {
    die('Unable to connect to database ' . $e->getMessage());
}

for($i=100;$i<=100000;$i+=100)
{
	$sql = "INSERT INTO pdn_rd_value_master(RDAmount,UserId) VALUES (".$i.",1)";
	$dbh->exec($sql);
}
?>