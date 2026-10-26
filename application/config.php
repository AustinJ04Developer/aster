<?php
/*$hostname = "localhost";
$user = "root";
//$pass = "rmLb3Z7edvtKp7fu";
$pass = "";
//$db = "mis-prod";
$db = "mis";

global $dbh;
// db connection
try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$db",$user,$pass);
}
catch (PDOException $e) {
    die('Unable to connect to database ' . $e->getMessage());
}
*/
set_time_limit(0);
$servername = "localhost";
$username = "marmis";
$password = 'M@a1#r^@*)"+#mi!&s@((*';
$database = "misnew";
$host = 'localhost';
$db   = 'misnew';
$user = 'marmis';
$pass = 'M@a1#r^@*)"+#mi!&s@((*';
$port = "3306";
$charset = 'utf8mb4';
try {
  $dbh = new PDO("mysql:host=$servername;dbname=$database",$username,$password);
  // set the PDO error mode to exception
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
/*$dbName = $_SERVER["DOCUMENT_ROOT"] . "products\products.mdb";
if (!file_exists($dbName)) {
 //   die("Could not find database file.");
}
$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");*/

/*function dbconnect()
{
	mysql_connect('localhost','root','RGYhnqUzrHAjnc26');
//	mysql_connect('localhost','root','');
	mysql_select_db('mis');
}

*/

?>
