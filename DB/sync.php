<?php
/**
 * CLI & Web Runner for Database Sync & Migrations
 * 
 * Manages EVERY change in the database:
 * - Creates missing tables
 * - Adds missing columns to existing tables
 * - Synchronizes modified column types / enums
 * - Adds missing indexes
 * - Applies pending migrations
 * - Updates master schema (aster_structure.sql)
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

    echo "Synchronizing every change across tables, columns, indexes, and migrations...\n";
    $results = DatabaseSync::syncAll($conn, true);

    if (!empty($results['tables_created'])) {
        echo "\n[+] Created Non-Existent Tables:\n";
        foreach ($results['tables_created'] as $t) {
            echo "    -> `{$t['table']}` (from {$t['source']})\n";
        }
    } else {
        echo "[-] All required tables exist.\n";
    }

    if (!empty($results['columns_added'])) {
        echo "\n[+] Added Missing Columns:\n";
        foreach ($results['columns_added'] as $col) {
            echo "    -> `{$col['table']}`.`{$col['column']}`\n";
        }
    } else {
        echo "[-] All table columns are in sync.\n";
    }

    if (!empty($results['columns_modified'])) {
        echo "\n[+] Synchronized Modified Column Definitions / Enums:\n";
        foreach ($results['columns_modified'] as $mod) {
            echo "    -> `{$mod['table']}`.`{$mod['column']}` ({$mod['type']})\n";
        }
    } else {
        echo "[-] All column definitions & enums are up to date.\n";
    }

    if (!empty($results['indexes_added'])) {
        echo "\n[+] Added Missing Indexes:\n";
        foreach ($results['indexes_added'] as $idx) {
            echo "    -> `{$idx['table']}`: {$idx['index']}\n";
        }
    } else {
        echo "[-] All indexes are up to date.\n";
    }

    if (!empty($results['migrations_run'])) {
        echo "\n[+] Applied Migrations:\n";
        foreach ($results['migrations_run'] as $m) {
            echo "    -> {$m}\n";
        }
    } else {
        echo "[-] No pending migrations. All migrations applied.\n";
    }

    if ($results['schema_updated']) {
        echo "\n[+] Master schema (DB/schema/aster_structure.sql) updated and in sync with live DB.\n";
    }

    if (!empty($results['errors'])) {
        echo "\n[!] Errors encountered:\n";
        foreach ($results['errors'] as $err) {
            echo "    [ERROR] {$err}\n";
        }
        exit(1);
    }

    echo "\n[SUCCESS] Every change in database synchronized successfully!\n";
    exit(0);
}

// If accessed via web browser, redirect to the friendly UI
header("Location: ../db_sync.php");
exit;
