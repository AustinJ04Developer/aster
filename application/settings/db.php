<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'Austin@2109*#');
if (!defined('DB_NAME')) define('DB_NAME', 'aster');
if (!defined('DB_PORT')) define('DB_PORT', 3307);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
// Database Connection: coe/application/settings/dbh.php
class Database {
    private $host = "localhost";
    private $db_name = "aster"; // Update with your database name
    private $username = "root"; // Update with your database username
    private $password = "Austin@2109*#"; // Update with your database password
    private $port = 3307;
    public $conn;

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