<?php
/**
 * Application Database & Environment Configuration
 * 
 * Supports local configuration override via db_local.php (kept in .gitignore)
 * so multiple collaborators can use different ports/passwords without git conflicts.
 */

if (file_exists(__DIR__ . '/db_local.php')) {
    require_once __DIR__ . '/db_local.php';
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

ini_set('max_execution_time', 600);

$hostname = DB_HOST;
$user     = DB_USER;
$pass     = DB_PASS;
$db       = DB_NAME;
$port     = DB_PORT;

global $dbh;
/*db connection*/
try {
    $dbh = new PDO("mysql:host=$hostname;port=$port;dbname=$db", $user, $pass);
}
catch (PDOException $e) {
    die('Unable to connect to database ' . $e->getMessage() . " (Host: {$hostname}, Port: {$port})");
}

date_default_timezone_set('Asia/Kolkata');
?>
