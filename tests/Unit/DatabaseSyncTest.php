<?php
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/../../DB/DatabaseSync.php';

class DatabaseSyncTest extends TestCase {
    private $conn;

    public function __construct($conn = null) {
        $this->conn = $conn;
    }

    public function run() {
        $this->setTestName("DatabaseSync Schema Extraction");

        $sqlSample = <<<SQL
-- Test Comment
CREATE TABLE `test_sample_one` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `test_sample_two` (
  `code` varchar(20) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB;
SQL;

        $extracted = DatabaseSync::extractCreateTableStatements($sqlSample);
        $this->assertTrue(isset($extracted['test_sample_one']), "Parsed test_sample_one correctly");
        $this->assertTrue(isset($extracted['test_sample_two']), "Parsed test_sample_two correctly");
        $this->assertTrue(strpos($extracted['test_sample_one'], 'IF NOT EXISTS') !== false, "Automatically injected IF NOT EXISTS");

        $this->setTestName("DatabaseSync Table Elements & Column Parser");
        $sampleTableSql = "CREATE TABLE `ast_sample_tbl` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(50) NOT NULL DEFAULT 'DEF',
            `status` enum('ACTIVE','PENDING','ARCHIVED') NOT NULL,
            `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_code` (`code`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB;";
        $parsedElements = DatabaseSync::parseTableElements($sampleTableSql);
        $this->assertTrue(isset($parsedElements['columns']['id']), "Extracted column `id`");
        $this->assertTrue(isset($parsedElements['columns']['status']), "Extracted column `status`");
        $this->assertTrue(isset($parsedElements['columns']['amount']), "Extracted column `amount`");
        $this->assertEquals("enum('ACTIVE','PENDING','ARCHIVED') NOT NULL", $parsedElements['columns']['status']['definition'], "Extracted enum definition accurately");
        $this->assertGreaterThan(1, count($parsedElements['indexes']), "Extracted indexes & keys");

        $this->setTestName("DatabaseSync Directory Resolution");
        $migrationsDir = DatabaseSync::getMigrationsDir();
        $this->assertTrue(is_dir($migrationsDir), "Migrations directory exists: " . $migrationsDir);
        
        $schemaDir = DatabaseSync::getSchemaDir();
        $this->assertTrue(is_dir($schemaDir), "Schema directory exists: " . $schemaDir);

        $this->setTestName("DatabaseSync Master Schema Integrity");
        $masterFile = $schemaDir . '/aster_structure.sql';
        $this->assertTrue(file_exists($masterFile), "aster_structure.sql exists");
        $masterContent = file_get_contents($masterFile);
        $parsedTables = DatabaseSync::extractCreateTableStatements($masterContent);
        $this->assertTrue(count($parsedTables) >= 46, "Master schema contains all base tables");
        $this->assertTrue(isset($parsedTables['ast_jewel_loan_receipt']), "ast_jewel_loan_receipt exists in master schema");
        $this->assertContains('PRINCIPAL_PAYMENT', $parsedTables['ast_jewel_loan_receipt'], "Master schema contains PRINCIPAL_PAYMENT in receipt table");

        $this->setTestName("Safety Guard: Strict Non-Deletion of Fields & Tables");
        // Verify that executeMultiSql refuses to execute DROP COLUMN or DROP TABLE
        $dummyConn = new class extends mysqli {
            public $executedQueries = [];
            public function __construct() {}
            public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT): mixed {
                $this->executedQueries[] = $query;
                return true;
            }
        };
        $dangerousSql = "ALTER TABLE `test` DROP COLUMN `some_field`; DROP TABLE `test_tbl`; SELECT 1;";
        DatabaseSync::executeMultiSql($dummyConn, $dangerousSql);
        $this->assertEquals(1, count($dummyConn->executedQueries), "Destructive DROP queries were blocked");
        $this->assertEquals("SELECT 1", trim($dummyConn->executedQueries[0]), "Only safe non-destructive query ran");

        $this->setTestName("DatabaseSync Comprehensive Change Management Result Keys");
        if ($this->conn instanceof mysqli) {
            $syncRes = DatabaseSync::syncAll($this->conn, false);
            $this->assertTrue(is_array($syncRes), "syncAll returns an array");
            $this->assertTrue(isset($syncRes['tables_created']), "syncAll reports tables_created");
            $this->assertTrue(isset($syncRes['columns_added']), "syncAll reports columns_added");
            $this->assertTrue(isset($syncRes['columns_modified']), "syncAll reports columns_modified");
            $this->assertTrue(isset($syncRes['indexes_added']), "syncAll reports indexes_added");
            $this->assertTrue(isset($syncRes['migrations_run']), "syncAll reports migrations_run");
        }
    }
}
