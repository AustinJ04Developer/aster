<?php
/**
 * Database configuration & connection setup
 */

// Load local developer credentials override if present (ignored by Git)
if (file_exists(__DIR__ . '/db_local.php')) {
    require_once __DIR__ . '/db_local.php';
}

// Fallback Database configuration defaults for shared codebase
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
if (!defined('DB_PORT')) define('DB_PORT', 3306);
if (!defined('AUTO_DB_SYNC')) define('AUTO_DB_SYNC', true);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " (Host: " . DB_HOST . ", Port: " . DB_PORT . ")");
}

// Auto-sync non-existent tables & pending migrations when collaborating via GitHub
if (defined('AUTO_DB_SYNC') && AUTO_DB_SYNC) {
    $syncEnginePath = __DIR__ . '/../../DB/DatabaseSync.php';
    if (file_exists($syncEnginePath)) {
        require_once $syncEnginePath;
        DatabaseSync::autoSync($conn);
    }
}

/**
 * Database Connection class (PDO/mysqli compatibility wrapper)
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->port = DB_PORT;
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->port);
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
