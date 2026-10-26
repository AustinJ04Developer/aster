<?php
// MYSQL SHUT DOWN FREQUENTLY
// SOLUTION I HAVE CHANGE THE PROT 3306(DEFAULT PORT) TO 3307 USING THE FOLLOWING STEPS
// OPEN MQL/BIN FOLDER
// OPEN MY.INI FILE
// CHANGE THE PORT TO 3307
// OPEN PHPMYQDMIN FOLDER
// OPEN CONFIG.INC.PHP
// CHANGE $cfg['Servers'][$i]['host'] = '127.0.0.1'; TO $cfg['Servers'][$i]['host'] = '127.0.0.1:3307';
// BUT IN CLIENTS SYSTEM DO NOT USE THE PORT IN DATA BASE SETTINGS
ini_set('max_execution_time', 600);
$hostname =  "localhost";
$user = "root";
$pass = "Austin@2109*#";
$db = "aster";
//$db = "gorcery";
global $dbh;
/*db connection*/
try {
    $dbh = new PDO("mysql:host=$hostname;port=3307;dbname=$db",$user,$pass);
}
catch (PDOException $e) {
    die('Unable to connect to database ' . $e->getMessage());
}
date_default_timezone_set('Asia/Kolkata');
?>