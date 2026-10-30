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
     * Also auto-baselines existing legacy migrations (001-009) if core tables already exist.
     */
    public static function initMigrationsTable(mysqli $conn) {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `" . self::MIGRATIONS_TABLE . "` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL UNIQUE,
                `batch` INT NOT NULL DEFAULT 1,
                `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conn->query($sql);

            // Auto-baseline check: if migrations table is empty but database already has tables,
            // mark existing baseline migrations (001-009) as already applied so they aren't re-run.
            $countRes = $conn->query("SELECT COUNT(*) as cnt FROM `" . self::MIGRATIONS_TABLE . "`");
            $countRow = $countRes ? $countRes->fetch_assoc() : null;
            if ($countRow && (int)$countRow['cnt'] === 0) {
                // Check if jewel loan receipt already exists
                $checkReceipt = $conn->query("SHOW TABLES LIKE 'ast_jewel_loan_receipt'");
                if ($checkReceipt && $checkReceipt->num_rows > 0) {
                    $baselineFiles = [
                        '001_create_jewel_loan_receipt.sql',
                        '002_add_processing_fee_to_jewel_loan.sql',
                        '003_add_balance_loan_and_payment_fields.sql',
                        '004_remove_account_number_and_fix_dnumber.sql',
                        '005_add_account_list_page.sql',
                        '006_jewel_loan_calculations.sql',
                        '007_add_receipt_list_page.sql',
                        '008_add_delete_receipt_page.sql',
                        '009_sync_jewel_loan_sidebar_order_and_icons.sql'
                    ];
                    foreach ($baselineFiles as $bf) {
                        $conn->query("INSERT IGNORE INTO `" . self::MIGRATIONS_TABLE . "` (`migration`, `batch`) VALUES ('{$bf}', 1)");
                    }
                }
            }
            return true;
        } catch (\Throwable $e) {
            error_log("initMigrationsTable error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get list of already applied migrations from database
     */
    public static function getAppliedMigrations(mysqli $conn) {
        self::initMigrationsTable($conn);
        $applied = [];
        try {
            $res = $conn->query("SELECT `migration` FROM `" . self::MIGRATIONS_TABLE . "` ORDER BY `id` ASC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $applied[] = $row['migration'];
                }
            }
        } catch (\Throwable $e) {
            error_log("getAppliedMigrations error: " . $e->getMessage());
        }
        return $applied;
    }

    /**
     * Get list of all existing table names in current database
     */
    public static function getExistingTables(mysqli $conn) {
        $tables = [];
        try {
            $res = $conn->query("SHOW TABLES");
            if ($res) {
                while ($row = $res->fetch_row()) {
                    $tables[] = strtolower($row[0]);
                }
            }
        } catch (\Throwable $e) {
            error_log("getExistingTables error: " . $e->getMessage());
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

        try {
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
        } catch (\Throwable $e) {
            $results['errors'][] = "Sync error: " . $e->getMessage();
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

        // Scan only schema dir and migrations dir for .sql files
        $dirsToScan = [self::getSchemaDir(), self::getMigrationsDir()];
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
            if (stripos($fileName, 'backup_') === 0) {
                continue;
            }

            $content = @file_get_contents($filePath);
            if (!$content) continue;

            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) {
                    // Table does not exist! Run query to create it
                    try {
                        if ($conn->query($createStatement)) {
                            $created[] = [
                                'table' => $cleanTableName,
                                'source' => $fileName
                            ];
                            $existingTables[] = $cleanTableName; // Update in-memory list
                        } else {
                            $errors[] = "Failed creating table `{$cleanTableName}` from `{$fileName}`: " . $conn->error;
                        }
                    } catch (\Throwable $ex) {
                        $errors[] = "Error creating table `{$cleanTableName}` from `{$fileName}`: " . $ex->getMessage();
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
        $files = is_dir($migrationsDir) ? scandir($migrationsDir) : [];
        
        $applied = [];
        $errors  = [];

        // Determine current max batch number
        $batch = 1;
        try {
            $batchRes = $conn->query("SELECT MAX(`batch`) as max_batch FROM `" . self::MIGRATIONS_TABLE . "`");
            if ($batchRes && $row = $batchRes->fetch_assoc()) {
                $batch = ((int)$row['max_batch']) + 1;
            }
        } catch (\Throwable $e) {}

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
                $sql = @file_get_contents($fullPath);
                $success = self::executeMultiSql($conn, $sql);
            } elseif ($ext === 'php') {
                try {
                    $runPhp = function($dbConn, $path) {
                        $conn = $dbConn;
                        global $conn;
                        return include $path;
                    };
                    $ret = $runPhp($conn, $fullPath);
                    $success = ($ret !== false);
                } catch (\Throwable $t) {
                    $errors[] = "Error in PHP migration `{$file}`: " . $t->getMessage();
                    $success = false;
                }
            }

            if ($success) {
                try {
                    $stmt = $conn->prepare("INSERT INTO `" . self::MIGRATIONS_TABLE . "` (`migration`, `batch`) VALUES (?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("si", $file, $batch);
                        $stmt->execute();
                        $stmt->close();
                    }
                    $applied[] = $file;
                } catch (\Throwable $te) {
                    error_log("Failed to record migration {$file}: " . $te->getMessage());
                }
            } else {
                $errors[] = "Migration failed or skipped: `{$file}`";
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
        $pattern = '/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?([a-zA-Z0-9_]+)`?\s*\((?:[^;]|\n)*?\)\s*(?:ENGINE=[^;]+)?;/is';
        
        if (preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $tableName = trim($match[1], '`');
                $fullSql   = trim($match[0]);
                if (!preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/i', $fullSql)) {
                    $fullSql = preg_replace('/CREATE\s+TABLE/i', 'CREATE TABLE IF NOT EXISTS', $fullSql, 1);
                }
                $statements[$tableName] = $fullSql;
            }
        }
        return $statements;
    }

    /**
     * Execute multi-statement SQL safely without crashing on exceptions
     */
    public static function executeMultiSql(mysqli $conn, $sql) {
        if (empty(trim($sql))) return true;

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
        $allOk = true;

        foreach ($queries as $query) {
            if (empty($query)) continue;
            // Ignore USE db statements so it works regardless of local DB name
            if (preg_match('/^\s*USE\s+/i', $query)) {
                continue;
            }

            // STRICT SAFETY: Do NOT delete/drop any fields or tables!
            if (preg_match('/\bDROP\s+(COLUMN|TABLE|DATABASE)\b/i', $query)) {
                error_log("[PROTECTED] Blocked destructive statement to preserve all table fields: " . substr($query, 0, 100));
                continue; // Skip without executing to preserve all fields
            }

            try {
                $res = $conn->query($query);
                if (!$res) {
                    // Check error code
                    if ($conn->errno == 1060 || $conn->errno == 1050 || $conn->errno == 1061) {
                        continue; // Column, table or key already exists
                    }
                    $allOk = false;
                }
            } catch (\Throwable $e) {
                $code = $e->getCode();
                // 1060: Duplicate column name
                // 1050: Table already exists
                // 1061: Duplicate key name
                // 1091: Can't DROP '...'; check that column/key exists
                if ($code == 1060 || $code == 1050 || $code == 1061 || $code == 1091) {
                    continue; // Benign duplicate error
                }
                error_log("executeMultiSql notice: " . $e->getMessage() . " in query: " . substr($query, 0, 100));
                $allOk = false;
            }
        }
        return $allOk;
    }

    /**
     * Fast automatic check for web requests.
     * Runs safely and silently in microseconds when no new migrations exist.
     */
    public static function autoSync(mysqli $conn) {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }

            $migrationsDir = self::getMigrationsDir();
            if (!is_dir($migrationsDir)) return;

            $files = scandir($migrationsDir);
            $count = count($files);
            if (isset($_SESSION['__last_migration_count']) && $_SESSION['__last_migration_count'] === $count) {
                return;
            }

            self::syncAll($conn);
            $_SESSION['__last_migration_count'] = $count;
        } catch (\Throwable $t) {
            error_log("autoSync warning: " . $t->getMessage());
        }
    }

    /**
     * Helper to export a table schema from MySQL into a new migration file.
     * Collaborators can use this whenever they create a new table in phpMyAdmin!
     */
    public static function exportTableToMigration(mysqli $conn, $tableName) {
        try {
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
        } catch (\Throwable $e) {
            error_log("exportTableToMigration error: " . $e->getMessage());
            return false;
        }
    }
}
