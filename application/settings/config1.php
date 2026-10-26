<?php
$hostname = "localhost";
$user = "root";
$pass = "mysql123";
$db = "cus";
global $dbh;
/*db connection*/
try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$db",$user,$pass);
}
catch (PDOException $e) {
    die('Unable to connect to database ' . $e->getMessage());
}
?>