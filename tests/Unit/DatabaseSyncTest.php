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
        $this->assertGreaterThan(40, count($parsedTables), "Master schema contains over 40 base tables");
    }
}

