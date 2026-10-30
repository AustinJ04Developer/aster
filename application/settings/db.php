<?php
/**
 * Database configuration & connection setup
 */
require_once __DIR__ . '/db_connection_provider.php';

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
        global $conn;
        if ($conn instanceof mysqli && @$conn->ping()) {
            $this->conn = $conn;
            return $this->conn;
        }
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
