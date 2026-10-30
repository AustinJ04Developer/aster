<?php
/**
 * DatabaseSync - Automatic Database Migration & Schema Synchronizer for Aster
 * 
 * Manages EVERY change in the database:
 * - Detects and creates non-existent tables.
 * - Detects and adds missing columns to existing tables (ALTER TABLE ADD COLUMN).
 * - Detects and synchronizes modified column types/enums (ALTER TABLE MODIFY COLUMN).
 * - Detects and creates missing indexes and unique constraints.
 * - Runs pending migrations in chronological order.
 * - Automatically updates the master schema (aster_structure.sql) to reflect all changes in Git.
 * - Preserves existing table data safely without data loss.
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
     * Get list of all existing table names in current database (lowercase)
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
     * Synchronize EVERY change in the database:
     * 1. Creates missing tables
     * 2. Adds missing columns on existing tables
     * 3. Synchronizes modified column types & definitions (e.g. enum additions)
     * 4. Adds missing indexes
     * 5. Runs pending migrations
     * 6. Updates master schema (aster_structure.sql) to keep code in sync with database
     * 
     * @param mysqli $conn
     * @param bool $autoUpdateSchema Whether to update aster_structure.sql with current DB structure
     * @return array Results summary
     */
    public static function syncAll(mysqli $conn, $autoUpdateSchema = true) {
        self::initMigrationsTable($conn);
        $results = [
            'tables_created'   => [],
            'tables_skipped'   => [],
            'columns_added'    => [],
            'columns_modified' => [],
            'indexes_added'    => [],
            'migrations_run'   => [],
            'schema_updated'   => false,
            'errors'           => []
        ];

        try {
            // 1. Sync missing tables from any schema files (*.sql)
            $missingTableResults = self::syncMissingTables($conn);
            $results['tables_created'] = $missingTableResults['created'];
            $results['tables_skipped'] = $missingTableResults['skipped'];
            if (!empty($missingTableResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $missingTableResults['errors']);
            }

            // 2. Sync missing columns on all existing tables
            $missingColResults = self::syncMissingColumns($conn);
            $results['columns_added'] = $missingColResults['added'];
            if (!empty($missingColResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $missingColResults['errors']);
            }

            // 3. Sync modified column types & enum definitions
            $modifiedColResults = self::syncModifiedColumns($conn);
            $results['columns_modified'] = $modifiedColResults['modified'];
            if (!empty($modifiedColResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $modifiedColResults['errors']);
            }

            // 4. Sync missing indexes on existing tables
            $indexResults = self::syncMissingIndexes($conn);
            $results['indexes_added'] = $indexResults['added'];
            if (!empty($indexResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $indexResults['errors']);
            }

            // 5. Run pending migrations from DB/migrations/
            $migrationResults = self::runPendingMigrations($conn);
            $results['migrations_run'] = $migrationResults['applied'];
            if (!empty($migrationResults['errors'])) {
                $results['errors'] = array_merge($results['errors'], $migrationResults['errors']);
            }

            // 6. Automatically capture any changes back into aster_structure.sql for Git
            if ($autoUpdateSchema) {
                $results['schema_updated'] = self::exportMasterSchema($conn);
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
        $sqlFiles = self::getScannableSqlFiles();

        foreach ($sqlFiles as $filePath) {
            $fileName = basename($filePath);
            $content = @file_get_contents($filePath);
            if (!$content) continue;

            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) {
                    try {
                        if ($conn->query($createStatement)) {
                            $created[] = [
                                'table'  => $cleanTableName,
                                'source' => $fileName
                            ];
                            $existingTables[] = $cleanTableName;
                        } else {
                            $errors[] = "Failed creating table `{$cleanTableName}` from `{$fileName}`: " . $conn->error;
                        }
                    } catch (\Throwable $ex) {
                        $errors[] = "Error creating table `{$cleanTableName}` from `{$fileName}`: " . $ex->getMessage();
                    }
                } else {
                    $skipped[] = [
                        'table'  => $cleanTableName,
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
     * Detects missing columns on existing tables and adds them via ALTER TABLE ADD COLUMN.
     */
    public static function syncMissingColumns(mysqli $conn) {
        $added  = [];
        $errors = [];

        $existingTables = self::getExistingTables($conn);
        $sqlFiles = self::getScannableSqlFiles();

        // Collect all column definitions per table across all schema/migration files
        $schemaTables = [];
        foreach ($sqlFiles as $filePath) {
            $content = @file_get_contents($filePath);
            if (!$content) continue;
            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) continue;

                $elements = self::parseTableElements($createStatement);
                if (!isset($schemaTables[$cleanTableName])) {
                    $schemaTables[$cleanTableName] = [];
                }
                foreach ($elements['columns'] as $colName => $colInfo) {
                    $schemaTables[$cleanTableName][$colName] = $colInfo;
                }
            }
        }

        // Compare against live columns in database
        foreach ($schemaTables as $tableName => $cols) {
            $liveColsRes = $conn->query("SHOW COLUMNS FROM `{$tableName}`");
            if (!$liveColsRes) continue;

            $liveColNames = [];
            while ($row = $liveColsRes->fetch_assoc()) {
                $liveColNames[strtolower($row['Field'])] = $row['Field'];
            }

            $prevCol = null;
            foreach ($cols as $colName => $colInfo) {
                $lowCol = strtolower($colName);
                if (!isset($liveColNames[$lowCol])) {
                    // Missing column detected! Add it safely
                    $afterClause = $prevCol ? " AFTER `{$prevCol}`" : "";
                    $alterSql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colInfo['definition']}{$afterClause}";
                    try {
                        if ($conn->query($alterSql)) {
                            $added[] = [
                                'table'  => $tableName,
                                'column' => $colName,
                                'sql'    => $alterSql
                            ];
                            $liveColNames[$lowCol] = $colName;
                        } else {
                            // Benign check: 1060 is duplicate column
                            if ($conn->errno != 1060) {
                                $errors[] = "Failed adding column `{$tableName}`.`{$colName}`: " . $conn->error;
                            }
                        }
                    } catch (\Throwable $e) {
                        if ($e->getCode() != 1060) {
                            $errors[] = "Error adding column `{$tableName}`.`{$colName}`: " . $e->getMessage();
                        }
                    }
                }
                $prevCol = $colName;
            }
        }

        return [
            'added'  => $added,
            'errors' => $errors
        ];
    }

    /**
     * Detects modified column types/enums and synchronizes them via ALTER TABLE MODIFY COLUMN.
     * Especially useful when ENUM options are expanded (e.g. adding 'PRINCIPAL_PAYMENT').
     */
    public static function syncModifiedColumns(mysqli $conn) {
        $modified = [];
        $errors   = [];

        $existingTables = self::getExistingTables($conn);
        $sqlFiles = self::getScannableSqlFiles();

        foreach ($sqlFiles as $filePath) {
            $content = @file_get_contents($filePath);
            if (!$content) continue;
            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) continue;

                $elements = self::parseTableElements($createStatement);
                if (empty($elements['columns'])) continue;

                $liveColsRes = $conn->query("SHOW COLUMNS FROM `{$cleanTableName}`");
                if (!$liveColsRes) continue;

                $liveCols = [];
                while ($row = $liveColsRes->fetch_assoc()) {
                    $liveCols[strtolower($row['Field'])] = $row;
                }

                foreach ($elements['columns'] as $colName => $colInfo) {
                    $lowCol = strtolower($colName);
                    if (!isset($liveCols[$lowCol])) continue;

                    $liveRow = $liveCols[$lowCol];
                    $schemaDef = $colInfo['definition'];

                    // Check for ENUM modifications
                    if (preg_match('/^enum\s*\((.+?)\)/i', $schemaDef, $schemaEnumMatch)) {
                        $schemaEnumValues = self::extractEnumValues($schemaEnumMatch[1]);
                        if (preg_match('/^enum\s*\((.+?)\)/i', $liveRow['Type'], $liveEnumMatch)) {
                            $liveEnumValues = self::extractEnumValues($liveEnumMatch[1]);
                            // If schema contains options not in live DB, update the column
                            $missingEnums = array_diff($schemaEnumValues, $liveEnumValues);
                            if (!empty($missingEnums)) {
                                $alterSql = "ALTER TABLE `{$cleanTableName}` MODIFY COLUMN `{$colName}` {$schemaDef}";
                                try {
                                    if ($conn->query($alterSql)) {
                                        $modified[] = [
                                            'table'       => $cleanTableName,
                                            'column'      => $colName,
                                            'type'        => 'enum_expanded',
                                            'new_options' => array_values($missingEnums),
                                            'sql'         => $alterSql
                                        ];
                                    } else {
                                        $errors[] = "Failed modifying enum `{$cleanTableName}`.`{$colName}`: " . $conn->error;
                                    }
                                } catch (\Throwable $e) {
                                    $errors[] = "Error modifying enum `{$cleanTableName}`.`{$colName}`: " . $e->getMessage();
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'modified' => $modified,
            'errors'   => $errors
        ];
    }

    /**
     * Detects missing indexes on existing tables and adds them via ALTER TABLE ADD INDEX.
     */
    public static function syncMissingIndexes(mysqli $conn) {
        $added  = [];
        $errors = [];

        $existingTables = self::getExistingTables($conn);
        $sqlFiles = self::getScannableSqlFiles();

        foreach ($sqlFiles as $filePath) {
            $content = @file_get_contents($filePath);
            if (!$content) continue;
            $tableStatements = self::extractCreateTableStatements($content);
            foreach ($tableStatements as $tableName => $createStatement) {
                $cleanTableName = strtolower(trim($tableName, '`'));
                if (!in_array($cleanTableName, $existingTables)) continue;

                $elements = self::parseTableElements($createStatement);
                if (empty($elements['indexes'])) continue;

                $liveIdxRes = $conn->query("SHOW INDEX FROM `{$cleanTableName}`");
                $existingIndexes = [];
                if ($liveIdxRes) {
                    while ($iRow = $liveIdxRes->fetch_assoc()) {
                        $existingIndexes[strtolower($iRow['Key_name'])] = true;
                    }
                }

                foreach ($elements['indexes'] as $idxStmt) {
                    // Skip PRIMARY KEY if one already exists
                    if (preg_match('/^PRIMARY\s+KEY/i', $idxStmt)) {
                        if (isset($existingIndexes['primary'])) continue;
                    }

                    // Extract index name
                    if (preg_match('/^(?:UNIQUE\s+(?:KEY|INDEX)|KEY|INDEX)\s+`?([a-zA-Z0-9_]+)`?/i', $idxStmt, $m)) {
                        $idxName = strtolower($m[1]);
                        if (isset($existingIndexes[$idxName])) continue;

                        $alterSql = "ALTER TABLE `{$cleanTableName}` ADD {$idxStmt}";
                        try {
                            if ($conn->query($alterSql)) {
                                $added[] = [
                                    'table' => $cleanTableName,
                                    'index' => $m[1],
                                    'sql'   => $alterSql
                                ];
                                $existingIndexes[$idxName] = true;
                            } else {
                                if ($conn->errno != 1061) { // 1061 is duplicate key name
                                    $errors[] = "Failed adding index on `{$cleanTableName}`: " . $conn->error;
                                }
                            }
                        } catch (\Throwable $e) {
                            if ($e->getCode() != 1061) {
                                $errors[] = "Error adding index on `{$cleanTableName}`: " . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        return [
            'added'  => $added,
            'errors' => $errors
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

        sort($files);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir($migrationsDir . '/' . $file)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['sql', 'php'])) {
                continue;
            }

            if (in_array($file, $appliedMigrations)) {
                continue;
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
     * Exports the complete live database structure of all non-system tables into DB/schema/aster_structure.sql.
     * Ensures every schema change made in phpMyAdmin or by migrations is captured in Git.
     */
    public static function exportMasterSchema(mysqli $conn) {
        try {
            $res = $conn->query("SHOW TABLES");
            if (!$res) return false;

            $tables = [];
            while ($row = $res->fetch_row()) {
                $t = $row[0];
                if ($t === self::MIGRATIONS_TABLE) continue;
                $tables[] = $t;
            }
            sort($tables);

            $header = "-- Aster Master Baseline Schema\n";
            $header .= "-- Auto-synchronizes every change across all tables\n";
            $header .= "-- Auto-generated by DatabaseSync on " . date('Y-m-d H:i:s') . "\n\n";

            $output = $header;
            foreach ($tables as $t) {
                $cRes = $conn->query("SHOW CREATE TABLE `{$t}`");
                if ($cRes && $row = $cRes->fetch_row()) {
                    $createSql = $row[1];
                    if (!preg_match('/CREATE TABLE IF NOT EXISTS/i', $createSql)) {
                        $createSql = preg_replace('/CREATE TABLE/i', 'CREATE TABLE IF NOT EXISTS', $createSql, 1);
                    }
                    $output .= $createSql . ";\n\n";
                }
            }

            $schemaFile = self::getSchemaDir() . '/aster_structure.sql';
            return file_put_contents($schemaFile, $output) !== false;
        } catch (\Throwable $e) {
            error_log("exportMasterSchema error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get scannable SQL files from schema and migrations directories (ignoring backups)
     */
    public static function getScannableSqlFiles() {
        $sqlFiles = [];
        $dirsToScan = [self::getSchemaDir(), self::getMigrationsDir()];
        foreach ($dirsToScan as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*.sql');
                if ($files) {
                    $sqlFiles = array_merge($sqlFiles, $files);
                }
            }
        }
        $filtered = [];
        foreach (array_unique($sqlFiles) as $f) {
            if (stripos(basename($f), 'backup_') === 0) continue;
            $filtered[] = $f;
        }
        return $filtered;
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
     * Robust stateful parser that extracts columns and indexes/constraints from CREATE TABLE syntax,
     * respecting parenthesis depth, quotes, and commas within enum/decimal types.
     */
    public static function parseTableElements($sql) {
        $firstParen = strpos($sql, "(");
        $lastParen = strrpos($sql, ")");
        if ($firstParen === false || $lastParen === false) return ["columns" => [], "indexes" => []];
        
        $body = substr($sql, $firstParen + 1, $lastParen - $firstParen - 1);
        $parts = [];
        $curr = "";
        $inParen = 0;
        $inQuote = false;
        $quoteChar = "";
        $len = strlen($body);

        for ($i = 0; $i < $len; $i++) {
            $ch = $body[$i];
            if (!$inQuote && ($ch === "\x27" || $ch === "\"" || $ch === "`")) {
                $inQuote = true;
                $quoteChar = $ch;
                $curr .= $ch;
            } elseif ($inQuote && $ch === $quoteChar) {
                if ($i > 0 && $body[$i-1] === "\\") {
                    $curr .= $ch;
                } else {
                    $inQuote = false;
                    $curr .= $ch;
                }
            } elseif (!$inQuote && $ch === "(") {
                $inParen++;
                $curr .= $ch;
            } elseif (!$inQuote && $ch === ")") {
                $inParen--;
                $curr .= $ch;
            } elseif (!$inQuote && $inParen === 0 && $ch === ",") {
                $trimmed = trim($curr);
                if (!empty($trimmed)) $parts[] = $trimmed;
                $curr = "";
            } else {
                $curr .= $ch;
            }
        }
        $trimmed = trim($curr);
        if (!empty($trimmed)) $parts[] = $trimmed;

        $columns = [];
        $indexes = [];

        foreach ($parts as $p) {
            $p = trim($p);
            if (preg_match("/^(?:PRIMARY\s+KEY|UNIQUE\s+KEY|UNIQUE\s+INDEX|KEY|INDEX|FULLTEXT|CONSTRAINT)\b/i", $p)) {
                $indexes[] = $p;
            } elseif (preg_match("/^`?([a-zA-Z0-9_]+)`?\s+(.+)$/s", $p, $m)) {
                $columns[$m[1]] = [
                    "name"       => $m[1],
                    "definition" => trim($m[2]),
                    "full"       => $p
                ];
            }
        }
        return ["columns" => $columns, "indexes" => $indexes];
    }

    /**
     * Extract string values from ENUM clause: 'A', 'B', 'C'
     */
    private static function extractEnumValues($enumContent) {
        $values = [];
        if (preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $enumContent, $m)) {
            $values = $m[1];
        }
        return $values;
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
            if (preg_match('/^\s*USE\s+/i', $query)) {
                continue;
            }

            // STRICT SAFETY: Do NOT delete/drop any fields or tables!
            if (preg_match('/\bDROP\s+(COLUMN|TABLE|DATABASE)\b/i', $query)) {
                error_log("[PROTECTED] Blocked destructive statement to preserve all table fields: " . substr($query, 0, 100));
                continue;
            }

            try {
                $res = $conn->query($query);
                if (!$res) {
                    if ($conn->errno == 1060 || $conn->errno == 1050 || $conn->errno == 1061) {
                        continue;
                    }
                    $allOk = false;
                }
            } catch (\Throwable $e) {
                $code = $e->getCode();
                if ($code == 1060 || $code == 1050 || $code == 1061 || $code == 1091) {
                    continue;
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
            $schemaFile = self::getSchemaDir() . '/aster_structure.sql';
            $schemaMtime = file_exists($schemaFile) ? filemtime($schemaFile) : 0;
            $syncStateKey = $count . '_' . $schemaMtime;

            if (isset($_SESSION['__last_sync_state']) && $_SESSION['__last_sync_state'] === $syncStateKey) {
                return;
            }

            self::syncAll($conn, true);
            $_SESSION['__last_sync_state'] = $syncStateKey;
        } catch (\Throwable $t) {
            error_log("autoSync warning: " . $t->getMessage());
        }
    }

    /**
     * Helper to export a table schema from MySQL into a new migration file.
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
