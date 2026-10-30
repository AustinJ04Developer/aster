<?php
/**
 * Aster - Database Sync & Change Manager Dashboard
 * 
 * Manages EVERY change in the database:
 * - Detects and creates non-existent tables
 * - Detects and adds missing columns (ALTER TABLE ADD COLUMN)
 * - Detects and synchronizes modified column types/enums (ALTER TABLE MODIFY COLUMN)
 * - Detects and creates missing indexes
 * - Runs pending migrations
 * - Automatically captures live database structure into aster_structure.sql for Git
 * 
 * Access via browser: http://localhost/aster/db_sync.php
 */

require_once __DIR__ . '/application/settings/config.php';
require_once __DIR__ . '/application/settings/db.php';
require_once __DIR__ . '/DB/DatabaseSync.php';

$message = null;
$messageType = 'info';
$syncResults = null;

// Handle Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'sync_now') {
        $syncResults = DatabaseSync::syncAll($conn, true);
        $createdCount  = count($syncResults['tables_created']);
        $colsAddCount  = count($syncResults['columns_added']);
        $colsModCount  = count($syncResults['columns_modified']);
        $idxAddCount   = count($syncResults['indexes_added']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount    = count($syncResults['errors']);
        $totalChanges  = $createdCount + $colsAddCount + $colsModCount + $idxAddCount + $migratedCount;

        if ($errorCount > 0) {
            $messageType = 'warning';
            $message = "Sync completed with {$errorCount} warning(s). Applied {$totalChanges} change(s).";
        } elseif ($totalChanges > 0) {
            $messageType = 'success';
            $message = "Success! Applied {$totalChanges} database change(s) ({$createdCount} tables, {$colsAddCount} columns added, {$colsModCount} columns modified, {$idxAddCount} indexes, {$migratedCount} migrations) and synchronized master schema.";
        } else {
            $messageType = 'info';
            $message = "Your database is already 100% up to date. Every table, column, index, and migration is synchronized.";
        }
    } elseif ($action === 'export_master_schema') {
        if (DatabaseSync::exportMasterSchema($conn)) {
            $messageType = 'success';
            $message = "Master baseline schema (<strong>DB/schema/aster_structure.sql</strong>) updated with all live database tables and definitions. Ready to commit to Git!";
        } else {
            $messageType = 'danger';
            $message = "Failed to update master schema. Please check filesystem write permissions.";
        }
    } elseif ($action === 'export_table') {
        $tableToExport = trim($_POST['export_table_name'] ?? '');
        if (!empty($tableToExport)) {
            $savedFile = DatabaseSync::exportTableToMigration($conn, $tableToExport);
            if ($savedFile) {
                $messageType = 'success';
                $message = "Created migration file: <strong>DB/migrations/{$savedFile}</strong>. You can now commit and push this file to GitHub!";
            } else {
                $messageType = 'danger';
                $message = "Failed to export table `{$tableToExport}`. Please verify the table name exists.";
            }
        }
    }
}

// Fetch current database state
DatabaseSync::initMigrationsTable($conn);
$existingTables = DatabaseSync::getExistingTables($conn);
$appliedMigrations = DatabaseSync::getAppliedMigrations($conn);

// Scan migration files
$migrationsDir = DatabaseSync::getMigrationsDir();
$migrationFiles = [];
if (is_dir($migrationsDir)) {
    $files = scandir($migrationsDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..' && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['sql', 'php'])) {
            $migrationFiles[] = $f;
        }
    }
}

// Find pending migrations
$pendingMigrations = array_diff($migrationFiles, $appliedMigrations);

// Scan detected tables from SQL files to see if any are missing
$allSqlFiles = DatabaseSync::getScannableSqlFiles();

$detectedTables = [];
foreach ($allSqlFiles as $sqf) {
    $fname = basename($sqf);
    $content = @file_get_contents($sqf);
    if (!$content) continue;
    $found = DatabaseSync::extractCreateTableStatements($content);
    foreach ($found as $tname => $stmt) {
        $cleanTname = strtolower(trim($tname, '`'));
        if (!isset($detectedTables[$cleanTname])) {
            $detectedTables[$cleanTname] = [
                'name'   => $cleanTname,
                'source' => $fname,
                'exists' => in_array($cleanTname, $existingTables)
            ];
        }
    }
}

$missingTablesCount = 0;
foreach ($detectedTables as $dt) {
    if (!$dt['exists']) $missingTablesCount++;
}

$schemaFile = DatabaseSync::getSchemaDir() . '/aster_structure.sql';
$schemaUpdatedTime = file_exists($schemaFile) ? date('d/m/Y h:i A', filemtime($schemaFile)) : 'Never';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aster - Database Sync & Change Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .navbar-brand { font-weight: bold; }
        .stat-card { background: #fff; border-radius: 6px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-card h3 { margin-top: 0; font-size: 28px; font-weight: 600; }
        .stat-card .label-stat { font-size: 13px; color: #777; text-transform: uppercase; font-weight: 600; }
        .panel { box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 6px; border: none; }
        .panel-heading { font-weight: bold; font-size: 15px; border-top-left-radius: 6px; border-top-right-radius: 6px; }
        .table-responsive { max-height: 380px; overflow-y: auto; }
        .badge-exists { background-color: #5cb85c; }
        .badge-missing { background-color: #d9534f; animation: pulse 1.5s infinite; }
        .badge-pending { background-color: #f0ad4e; }
        .badge-applied { background-color: #5bc0de; }
        .code-box { background: #2d3748; color: #f7fafc; padding: 12px; border-radius: 4px; font-family: Consolas, monospace; font-size: 13px; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }
    </style>
</head>
<body>

<nav class="navbar navbar-inverse navbar-static-top" style="margin-bottom: 25px;">
    <div class="container">
        <div class="navbar-header">
            <span class="navbar-brand"><i class="glyphicon glyphicon-transfer"></i> Aster Database Sync & Change Manager</span>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="application/views/admin-dashboard.php"><i class="glyphicon glyphicon-home"></i> Back to Dashboard</a></li>
        </ul>
    </div>
</nav>

<div class="container">

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($syncResults): ?>
        <div class="panel panel-default">
            <div class="panel-heading"><i class="glyphicon glyphicon-list-alt"></i> Execution Summary</div>
            <div class="panel-body">
                <?php if (!empty($syncResults['tables_created'])): ?>
                    <div class="text-success" style="margin-bottom: 10px;">
                        <strong><i class="glyphicon glyphicon-ok-circle"></i> Tables Created:</strong>
                        <ul>
                            <?php foreach ($syncResults['tables_created'] as $tc): ?>
                                <li>Created table <code><?= htmlspecialchars($tc['table']) ?></code> (from <?= htmlspecialchars($tc['source']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($syncResults['columns_added'])): ?>
                    <div class="text-success" style="margin-bottom: 10px;">
                        <strong><i class="glyphicon glyphicon-plus-sign"></i> Columns Added:</strong>
                        <ul>
                            <?php foreach ($syncResults['columns_added'] as $ca): ?>
                                <li>Added column <code><?= htmlspecialchars($ca['table']) ?>.<?= htmlspecialchars($ca['column']) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($syncResults['columns_modified'])): ?>
                    <div class="text-info" style="margin-bottom: 10px;">
                        <strong><i class="glyphicon glyphicon-edit"></i> Columns Synchronized / Modified:</strong>
                        <ul>
                            <?php foreach ($syncResults['columns_modified'] as $cm): ?>
                                <li>Updated <code><?= htmlspecialchars($cm['table']) ?>.<?= htmlspecialchars($cm['column']) ?></code> (<?= htmlspecialchars($cm['type']) ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($syncResults['indexes_added'])): ?>
                    <div class="text-primary" style="margin-bottom: 10px;">
                        <strong><i class="glyphicon glyphicon-bookmark"></i> Indexes Added:</strong>
                        <ul>
                            <?php foreach ($syncResults['indexes_added'] as $ia): ?>
                                <li>Added index <code><?= htmlspecialchars($ia['table']) ?>: <?= htmlspecialchars($ia['index']) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($syncResults['migrations_run'])): ?>
                    <div class="text-info" style="margin-bottom: 10px;">
                        <strong><i class="glyphicon glyphicon-ok-circle"></i> Migrations Applied:</strong>
                        <ul>
                            <?php foreach ($syncResults['migrations_run'] as $mr): ?>
                                <li>Executed <code><?= htmlspecialchars($mr) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($syncResults['schema_updated']): ?>
                    <div class="text-success" style="margin-bottom: 10px;">
                        <i class="glyphicon glyphicon-check"></i> Master baseline schema (<code>DB/schema/aster_structure.sql</code>) synchronized with live database.
                    </div>
                <?php endif; ?>

                <?php if (!empty($syncResults['errors'])): ?>
                    <div class="text-danger">
                        <strong><i class="glyphicon glyphicon-remove-circle"></i> Issues Encountered:</strong>
                        <ul>
                            <?php foreach ($syncResults['errors'] as $er): ?>
                                <li><?= htmlspecialchars($er) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Overview Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Database</div>
                <h3><?= htmlspecialchars(DB_NAME) ?></h3>
                <small class="text-muted"><?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Live Tables</div>
                <h3 class="text-success"><?= count($existingTables) ?></h3>
                <small class="text-muted">In your local MySQL</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Pending Migrations</div>
                <h3 class="<?= count($pendingMigrations) > 0 ? 'text-warning' : 'text-muted' ?>"><?= count($pendingMigrations) ?></h3>
                <small class="text-muted"><?= count($appliedMigrations) ?> already executed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Master Schema</div>
                <h3 class="text-info"><?= count($detectedTables) ?></h3>
                <small class="text-muted">Updated: <?= $schemaUpdatedTime ?></small>
            </div>
        </div>
    </div>

    <!-- Main Sync Action Card -->
    <div class="panel panel-primary">
        <div class="panel-body text-center" style="padding: 25px;">
            <h3 style="margin-top:0;">Manage Every Change in the Database</h3>
            <p class="text-muted" style="max-width: 700px; margin: 0 auto 20px auto;">
                Synchronizes <strong>tables, missing columns, column definitions (including enums), indexes, and migrations</strong>.
                Ensures both collaborators stay 100% in sync without losing existing data or manual dumps.
            </p>
            <form method="POST" style="display: inline-block; margin-right: 10px;">
                <input type="hidden" name="action" value="sync_now">
                <button type="submit" class="btn btn-success btn-lg" style="padding: 12px 35px; font-size: 16px; font-weight: bold;">
                    <i class="glyphicon glyphicon-flash"></i> Sync Every Change Now
                </button>
            </form>
            <form method="POST" style="display: inline-block;">
                <input type="hidden" name="action" value="export_master_schema">
                <button type="submit" class="btn btn-default btn-lg" style="padding: 12px 25px; font-size: 16px;" title="Updates aster_structure.sql with current database structure">
                    <i class="glyphicon glyphicon-save-file"></i> Save DB Structure to Master Schema
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Missing / Detected Tables Panel -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-th-list"></i> Tracked Tables (<?= count($detectedTables) ?>)
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-hover table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Source File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detectedTables)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No tables defined in schema/migration SQL files yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($detectedTables as $dt): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($dt['name']) ?></code></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($dt['source']) ?></small></td>
                                        <td>
                                            <?php if ($dt['exists']): ?>
                                                <span class="badge badge-exists"><i class="glyphicon glyphicon-ok"></i> Exists</span>
                                            <?php else: ?>
                                                <span class="badge badge-missing"><i class="glyphicon glyphicon-alert"></i> Missing</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Migrations Panel -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-random"></i> Tracked Migrations (<?= count($migrationFiles) ?> total)
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-hover table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Migration File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($migrationFiles)): ?>
                                <tr><td colspan="2" class="text-center text-muted">No migration files found in DB/migrations/</td></tr>
                            <?php else: ?>
                                <?php foreach ($migrationFiles as $mf): ?>
                                    <?php $isApplied = in_array($mf, $appliedMigrations); ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($mf) ?></code></td>
                                        <td>
                                            <?php if ($isApplied): ?>
                                                <span class="badge badge-applied"><i class="glyphicon glyphicon-ok"></i> Applied</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending"><i class="glyphicon glyphicon-time"></i> Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Export Tool for Collaborators -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="glyphicon glyphicon-export"></i> Helper: Export a Newly Created Table to a Migration File</div>
                <div class="panel-body">
                    <p class="text-muted">
                        When you create a new table in phpMyAdmin, select it below and click <strong>Generate Migration File</strong>.
                        It creates a clean <code>CREATE TABLE IF NOT EXISTS</code> file in <code>DB/migrations/</code> so you can commit and push it to GitHub for your collaborator!
                    </p>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="action" value="export_table">
                        <div class="form-group">
                            <label for="export_table_name">Select Table: &nbsp;</label>
                            <select name="export_table_name" id="export_table_name" class="form-control" required style="min-width: 280px;">
                                <option value="">-- Choose table to export --</option>
                                <?php foreach ($existingTables as $tbl): ?>
                                    <option value="<?= htmlspecialchars($tbl) ?>"><?= htmlspecialchars($tbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-floppy-save"></i> Generate Migration File</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
