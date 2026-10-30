<?php
/**
 * CLI & Web Runner for Database Sync & Migrations
 * 
 * Usage:
 * CLI: php DB/sync.php
 * Web: http://localhost/aster/DB/sync.php or http://localhost/aster/db_sync.php
 */

$isCli = (php_sapi_name() === 'cli');

require_once __DIR__ . '/../application/settings/config.php';
require_once __DIR__ . '/../application/settings/db.php';
require_once __DIR__ . '/DatabaseSync.php';

if ($isCli) {
    echo "=======================================================\n";
    echo "        ASTER DATABASE SYNC & MIGRATION RUNNER        \n";
    echo "=======================================================\n";
    echo "Target DB: " . DB_NAME . " (" . DB_HOST . ":" . DB_PORT . ")\n\n";

    echo "Checking for non-existent tables and pending migrations...\n";
    $results = DatabaseSync::syncAll($conn);

    if (!empty($results['tables_created'])) {
        echo "\n[+] Created Non-Existent Tables:\n";
        foreach ($results['tables_created'] as $t) {
            echo "    -> `{$t['table']}` (from {$t['source']})\n";
        }
    } else {
        echo "[-] No missing tables found. All required tables exist.\n";
    }

    if (!empty($results['migrations_run'])) {
        echo "\n[+] Applied Migrations:\n";
        foreach ($results['migrations_run'] as $m) {
            echo "    -> {$m}\n";
        }
    } else {
        echo "[-] No pending migrations. Database is up to date.\n";
    }

    if (!empty($results['errors'])) {
        echo "\n[!] Errors encountered:\n";
        foreach ($results['errors'] as $err) {
            echo "    [ERROR] {$err}\n";
        }
        exit(1);
    }

    echo "\n[SUCCESS] Database synchronization completed safely!\n";
    exit(0);
}

// If accessed via web browser, redirect to the friendly UI
header("Location: ../db_sync.php");
exit;

