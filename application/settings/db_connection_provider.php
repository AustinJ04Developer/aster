<?php
/**
 * Aster Universal Database Connection Provider
 * 
 * Auto-detects local credentials, handles port 3306 / 3307 switching,
 * automatically creates the `aster` database if it doesn't exist yet,
 * and seamlessly synchronizes schema for team collaboration.
 */

// 1. Load local developer override if present (.gitignore)
if (file_exists(__DIR__ . '/db_local.php')) {
    require_once __DIR__ . '/db_local.php';
}

// 2. Default target credentials
$targetHost = defined('DB_HOST') ? DB_HOST : 'localhost';
$targetUser = defined('DB_USER') ? DB_USER : 'root';
$targetPass = defined('DB_PASS') ? DB_PASS : '';
$targetName = defined('DB_NAME') ? DB_NAME : 'aster';
$targetPort = defined('DB_PORT') ? (int)DB_PORT : 3306;

// 3. Fallback candidates matrix (supports both collaborator setups automatically)
$candidates = [
    ['host' => $targetHost, 'port' => $targetPort, 'user' => $targetUser, 'pass' => $targetPass],
    ['host' => $targetHost, 'port' => ($targetPort == 3307 ? 3306 : 3307), 'user' => $targetUser, 'pass' => $targetPass],
    ['host' => $targetHost, 'port' => $targetPort, 'user' => $targetUser, 'pass' => ''],
    ['host' => $targetHost, 'port' => ($targetPort == 3307 ? 3306 : 3307), 'user' => $targetUser, 'pass' => ''],
    ['host' => 'localhost', 'port' => 3307, 'user' => 'root', 'pass' => 'Austin@2109*#'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3307, 'user' => 'root', 'pass' => ''],
];

global $conn, $dbh, $hostname, $user, $pass, $db, $port;

if (!isset($conn) || !$conn instanceof mysqli || @$conn->ping() === false) {
    $conn = null;
    $lastError = '';

    foreach ($candidates as $cand) {
        try {
            // First connect without selecting DB to ensure server responds and DB exists
            $testConn = @new mysqli($cand['host'], $cand['user'], $cand['pass'], '', (int)$cand['port']);
            if (!$testConn->connect_error) {
                // Ensure the database exists
                $testConn->query("CREATE DATABASE IF NOT EXISTS `{$targetName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $testConn->select_db($targetName);
                
                $conn = $testConn;
                $targetHost = $cand['host'];
                $targetPort = (int)$cand['port'];
                $targetUser = $cand['user'];
                $targetPass = $cand['pass'];
                break;
            } else {
                $lastError = $testConn->connect_error;
            }
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
        }
    }

    if (!$conn) {
        die("<h3>Database Connection Error</h3><p>Could not connect to MySQL server on port 3306 or 3307.</p><p>Details: " . htmlspecialchars($lastError) . "</p><p>Please ensure Apache and MySQL are running in your XAMPP Control Panel.</p>");
    }
}

// 4. Define final active constants
if (!defined('DB_HOST')) define('DB_HOST', $targetHost);
if (!defined('DB_USER')) define('DB_USER', $targetUser);
if (!defined('DB_PASS')) define('DB_PASS', $targetPass);
if (!defined('DB_NAME')) define('DB_NAME', $targetName);
if (!defined('DB_PORT')) define('DB_PORT', $targetPort);
if (!defined('AUTO_DB_SYNC')) define('AUTO_DB_SYNC', true);

// Compatibility variables for config.php
$hostname = DB_HOST;
$user     = DB_USER;
$pass     = DB_PASS;
$db       = DB_NAME;
$port     = DB_PORT;

// 5. Initialize PDO connection ($dbh)
if (!isset($dbh) || !$dbh instanceof PDO) {
    try {
        $dbh = new PDO("mysql:host={$hostname};port={$port};dbname={$db}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die("Unable to connect to database via PDO: " . $e->getMessage());
    }
}

date_default_timezone_set('Asia/Kolkata');

// 6. Safe Auto-Sync non-existent tables & pending migrations
if (defined('AUTO_DB_SYNC') && AUTO_DB_SYNC && $conn instanceof mysqli) {
    $syncEnginePath = __DIR__ . '/../../DB/DatabaseSync.php';
    if (file_exists($syncEnginePath)) {
        require_once $syncEnginePath;
        try {
            DatabaseSync::autoSync($conn);
        } catch (\Throwable $t) {
            error_log("AutoSync notice: " . $t->getMessage());
        }
    }
}

