<?php
/**
 * DatabaseSync - Automatic Database Migration & Schema Synchronizer for Aster
 * 
 * Solves the team database synchronization problem:
 * - Automatically detects and creates NON-EXISTENT tables.
 * - Safely skips existing tables without wiping or overwriting existing data.
 * - Tracks applied migrations in `__schema_migrations` table.
 * - Collaborators can commit new `.sql` or `.php` migrations to GitHub;
 *   the other collaborator's database automatically applies them!
 */

class DatabaseSync {
    const MIGRATIONS_TABLE = '__schema_migrations';
    private static $migrationsDir = __DIR__ . '/migrations';
    private static $schemaDir     = __DIR__ . '/schema';

    /**
     * Get or create the migrations directory
     */
    public static function getMigrationsDir() {
        if (!is_dir(self::$migrationsDir)) {
            @mkdir(self::$migrationsDir, 0777, true);
        }
        return self::$migrationsDir;
    }

    /**
     * Get or create the schema directory
     */
    public static function getSchemaDir() {
        if (!is_dir(self::$schemaDir)) {
            @mkdir(self::$schemaDir, 0777, true);
        }
        return self::$schemaDir;
    }

    /**
     * Initialize the tracking table `__schema_migrations`
     */
    public static function initMigrationsTable(mysqli $conn) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::MIGRATIONS_TABLE . "` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL DEFAULT 1,
            `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return $conn->query($sql);
    }

    /**
     * Get list of already applied migrations from database
     */
    public static function getAppliedMigrations(mysqli $conn) {
        self::initMigrationsTable($conn);
        $applied = [];
        $res = $conn->query("SELECT `migration` FROM `" . self::MIGRATIONS_TABLE . "` ORDER BY `id` ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $applied[] = $row['migration'];
            }
        }
        return $applied;
    }

    /**
     * Get list of all existing table names in current database
     */
    public static function getExistingTables(mysqli $conn) {
        $tables = [];
        $res = $conn->query("SHOW TABLES");
        if ($res) {
            while ($row = $res->fetch_row()) {
                $tables[] = strtolower($row[0]);
            }
        }
        return $tables;
    }

    /**
     * Run all missing tables and pending migrations
     * 
     * @param mysqli $conn
     * @return array Results summary
     */
    public static function syncAll(mysqli $conn) {
        self::initMigrationsTable($conn);
        $results = [
            'tables_created' => [],
            'tables_skipped' => [],
            'migrations_run' => [],
            'errors'         => []
        ];

        // 1. Sync missing tables from any schema files (*.sql)
        $missingTableResults = self::syncMissingTables($conn);
        $results['tables_created'] = $missingTableResults['created'];
        $results['tables_skipped'] = $missingTableResults['skipped'];
        if (!empty($missingTableResults['errors'])) {
            $results['errors'] = array_merge($results['errors'], $missingTableResults['errors']);
        }

        // 2. Run pending migrations from DB/migrations/
        $migrationResults = self::runPendingMigrations($conn);
        $results['migrations_run'] = $migrationResults['applied'];
        if (!empty($migrationResults['errors'])) {
            $results['errors'] = array_merge($results['errors'], $migrationResults['errors']);
        }

        return $results;
    }

    /**
     * Detects all CREATE TABLE statements in schema & migration SQL files.
     * If a table does NOT exist in the database, executes the query to create it.
     * If a table already exists, SKIPS it to preserve existing data!
     */
    public static function syncMissingTables(mysqli $conn) {
        $created = [];
        $skipped = [];
        $errors  = [];

        $existingTables = self::getExistingTables($conn);
        $sqlFiles = [];

        // Scan both schema dir and migrations dir for .sql files
        $dirsToScan = [self::getSchemaDir(), self::getMigrationsDir(), __DIR__];
        foreach ($dirsToScan as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*.sql');
                if ($files) {
                    $sqlFiles = array_merge($sqlFiles, $files);
                }
            }
        }
        $sqlFiles = array_unique($sqlFiles);

        foreach ($sqlFiles as $filePath) {
            $fileName = basename($filePath);
            // Skip pure backup dumps to avoid accidental overwrite
            if (stripos($fileName, 'backup_') === 0) {
                continue;
            }

            $content = file_get_contents($filePath);
            if (!$content) continue;

            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) {
                    // Table does not exist! Run query to create it
                    if ($conn->query($createStatement)) {
                        $created[] = [
                            'table' => $cleanTableName,
                            'source' => $fileName
                        ];
                        $existingTables[] = $cleanTableName; // Update in-memory list
                    } else {
                        $errors[] = "Failed creating table `{$cleanTableName}` from `{$fileName}`: " . $conn->error;
                    }
                } else {
                    $skipped[] = [
                        'table' => $cleanTableName,
                        'source' => $fileName
                    ];
                }
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors
        ];
    }

    /**
     * Run all pending migrations found in DB/migrations/
     */
    public static function runPendingMigrations(mysqli $conn) {
        $appliedMigrations = self::getAppliedMigrations($conn);
        $migrationsDir = self::getMigrationsDir();
        $files = scandir($migrationsDir);
        
        $applied = [];
        $errors  = [];

        // Determine current max batch number
        $batch = 1;
        $batchRes = $conn->query("SELECT MAX(`batch`) as max_batch FROM `" . self::MIGRATIONS_TABLE . "`");
        if ($batchRes && $row = $batchRes->fetch_assoc()) {
            $batch = ((int)$row['max_batch']) + 1;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($migrationsDir . '/' . $file)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['sql', 'php'])) {
                continue;
            }

            if (in_array($file, $appliedMigrations)) {
                continue; // Already executed
            }

            $fullPath = $migrationsDir . '/' . $file;
            $success = false;

            if ($ext === 'sql') {
                $sql = file_get_contents($fullPath);
                $success = self::executeMultiSql($conn, $sql);
            } elseif ($ext === 'php') {
                try {
                    // Include script in local scope with $conn available
                    $runPhp = function($dbConn, $path) {
                        $conn = $dbConn;
                        global $conn;
                        return include $path;
                    };
                    $ret = $runPhp($conn, $fullPath);
                    $success = ($ret !== false);
                } catch (Throwable $t) {
                    $errors[] = "Error in PHP migration `{$file}`: " . $t->getMessage();
                    $success = false;
                }
            }

            if ($success) {
                $stmt = $conn->prepare("INSERT INTO `" . self::MIGRATIONS_TABLE . "` (`migration`, `batch`) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("si", $file, $batch);
                    $stmt->execute();
                    $stmt->close();
                }
                $applied[] = $file;
            } else {
                $errors[] = "Migration failed: `{$file}` - " . $conn->error;
            }
        }

        return [
            'applied' => $applied,
            'errors'  => $errors
        ];
    }

    /**
     * Parse SQL file content and extract individual CREATE TABLE statements
     */
    public static function extractCreateTableStatements($sqlContent) {
        $statements = [];
        // Regex to match CREATE TABLE [IF NOT EXISTS] `tableName` (...);
        // Supports multiline, with modifiers like ENGINE=InnoDB
        $pattern = '/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?([a-zA-Z0-9_]+)`?\s*\((?:[^;]|\n)*?\)\s*(?:ENGINE=[^;]+)?;/is';
        
        if (preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableName = trim($match[1], '`');
                $fullSql   = trim($match[0]);
                // Ensure IF NOT EXISTS is present for extra safety
                if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $fullSql)) {
                    $fullSql = preg_replace('/CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $fullSql, 1);
                }
                $statements[$tableName] = $fullSql;
            }
        }
        return $statements;
    }

    /**
     * Execute multi-statement SQL safely
     */
    public static function executeMultiSql(mysqli $conn, $sql) {
        // Strip SQL comments
        $lines = explode("\n", $sql);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        $queries = array_filter(array_map('trim', explode(';', $cleanSql)));
        foreach ($queries as $query) {
            if (empty($query)) continue;
            // Ignore USE db statements so it works regardless of local DB name
            if (preg_match('/^\s*USE\s+/i', $query)) {
                continue;
            }
            if (!$conn->query($query)) {
                // If it fails on duplicate column/table, ignore gracefully
                if ($conn->errno == 1060 || $conn->errno == 1050) {
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    /**
     * Fast automatic check for web requests.
     * Runs in microseconds when no new migrations exist.
     */
    public static function autoSync(mysqli $conn) {
        // Use static or session check to avoid hitting disk on every single sub-request
        static $checked = false;
        if ($checked) return;
        $checked = true;

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $migrationsDir = self::getMigrationsDir();
        if (!is_dir($migrationsDir)) return;

        // Check file count against session cache to make normal requests instant
        $files = scandir($migrationsDir);
        $count = count($files);
        if (isset($_SESSION['__last_migration_count']) && $_SESSION['__last_migration_count'] === $count) {
            return;
        }

        self::syncAll($conn);
        $_SESSION['__last_migration_count'] = $count;
    }

    /**
     * Helper to export a table schema from MySQL into a new migration file.
     * Collaborators can use this whenever they create a new table in phpMyAdmin!
     */
    public static function exportTableToMigration(mysqli $conn, $tableName) {
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        $res = $conn->query("SHOW CREATE TABLE `{$tableName}`");
        if (!$res || !($row = $res->fetch_row())) {
            return false;
        }

        $createSql = $row[1] . ";\n";
        $createSql = preg_replace('/CREATE TABLE/i', 'CREATE TABLE IF NOT EXISTS', $createSql, 1);

        $timestamp = date('Ymd_His');
        $filename = "{$timestamp}_create_{$tableName}.sql";
        $filepath = self::getMigrationsDir() . '/' . $filename;

        $header = "-- Migration: Create {$tableName}\n";
        $header .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- Shared via GitHub for Aster collaborators\n\n";

        return file_put_contents($filepath, $header . $createSql) ? $filename : false;
    }
}

