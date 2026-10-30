<?php
/**
 * Aster - Database Sync & Migration Dashboard
 * 
 * Allows collaborators on GitHub to sync missing tables and migrations
 * without exporting/overwriting the entire database.
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
        $syncResults = DatabaseSync::syncAll($conn);
        $createdCount = count($syncResults['tables_created']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount = count($syncResults['errors']);

        if ($errorCount > 0) {
            $messageType = 'warning';
            $message = "Sync completed with {$errorCount} warning(s). Created {$createdCount} table(s), applied {$migratedCount} migration(s).";
        } elseif ($createdCount > 0 || $migratedCount > 0) {
            $messageType = 'success';
            $message = "Success! Created {$createdCount} missing table(s) and applied {$migratedCount} pending migration(s).";
        } else {
            $messageType = 'info';
            $message = "Your database is already 100% up to date. No non-existent tables or pending migrations found.";
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
$allSchemaSqlFiles = glob(DatabaseSync::getSchemaDir() . '/*.sql') ?: [];
$allMigrationSqlFiles = glob($migrationsDir . '/*.sql') ?: [];
$allSqlFiles = array_merge($allSchemaSqlFiles, $allMigrationSqlFiles);

$detectedTables = [];
foreach ($allSqlFiles as $sqf) {
    $fname = basename($sqf);
    if (stripos($fname, 'backup_') === 0) continue;
    $content = file_get_contents($sqf);
    $found = DatabaseSync::extractCreateTableStatements($content);
    foreach ($found as $tname => $stmt) {
        $cleanTname = strtolower(trim($tname, '`'));
        if (!isset($detectedTables[$cleanTname])) {
            $detectedTables[$cleanTname] = [
                'name' => $cleanTname,
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aster - Database Sync & Migration Manager</title>
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
            <span class="navbar-brand"><i class="glyphicon glyphicon-transfer"></i> Aster Database Sync (GitHub Collab)</span>
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
                        <strong><i class="glyphicon glyphicon-ok-circle"></i> Non-Existent Tables Created:</strong>
                        <ul>
                            <?php foreach ($syncResults['tables_created'] as $tc): ?>
                                <li>Created table <code><?= htmlspecialchars($tc['table']) ?></code> (from <?= htmlspecialchars($tc['source']) ?>)</li>
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
                <div class="label-stat">Existing Tables</div>
                <h3 class="text-success"><?= count($existingTables) ?></h3>
                <small class="text-muted">In your local MySQL</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Non-Existent Tables</div>
                <h3 class="<?= $missingTablesCount > 0 ? 'text-danger' : 'text-muted' ?>"><?= $missingTablesCount ?></h3>
                <small class="text-muted"><?= $missingTablesCount > 0 ? 'Need creation' : 'All tables present' ?></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="label-stat">Pending Migrations</div>
                <h3 class="<?= count($pendingMigrations) > 0 ? 'text-warning' : 'text-muted' ?>"><?= count($pendingMigrations) ?></h3>
                <small class="text-muted"><?= count($appliedMigrations) ?> already executed</small>
            </div>
        </div>
    </div>

    <!-- Main Sync Action Card -->
    <div class="panel panel-primary">
        <div class="panel-body text-center" style="padding: 25px;">
            <h3 style="margin-top:0;">Synchronize Database with GitHub Changes</h3>
            <p class="text-muted" style="max-width: 650px; margin: 0 auto 20px auto;">
                Clicking the button below checks all shared schema and migration files from GitHub.
                Any <strong>non-existent table</strong> will be automatically created, and any <strong>new migration</strong> will be executed.
                Your existing tables and test data will <strong>NEVER be deleted or wiped</strong>!
            </p>
            <form method="POST" style="display: inline-block;">
                <input type="hidden" name="action" value="sync_now">
                <button type="submit" class="btn btn-success btn-lg" style="padding: 12px 35px; font-size: 16px; font-weight: bold;">
                    <i class="glyphicon glyphicon-flash"></i> Sync Database Now
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Missing / Detected Tables Panel -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="glyphicon glyphicon-th-list"></i> Shared Tables Check (<?= count($detectedTables) ?> tracked)
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
                                                <span class="badge badge-missing"><i class="glyphicon glyphicon-alert"></i> Non-Existent</span>
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

    <!-- Instructions / Collaboration Workflow Guide -->
    <div class="panel panel-info">
        <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> How Both Collaborators Share Database Changes via GitHub</div>
        <div class="panel-body">
            <h4><span class="label label-primary">Step 1</span> When Developer A creates a new table or changes schema:</h4>
            <ul>
                <li><strong>Option A (One-Click):</strong> Go to this page, select the new table under <em>"Export a Newly Created Table"</em>, and click Generate.</li>
                <li><strong>Option B (Manual):</strong> Create a new file in <code>DB/migrations/</code> (e.g., <code>20261030_create_new_feature.sql</code>) containing:
                    <div class="code-box" style="margin-top: 5px;">
CREATE TABLE IF NOT EXISTS `your_new_table` (<br>
&nbsp;&nbsp;`id` INT AUTO_INCREMENT PRIMARY KEY,<br>
&nbsp;&nbsp;`name` VARCHAR(100) NOT NULL<br>
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    </div>
                </li>
                <li>Commit and push to GitHub:
                    <div class="code-box" style="margin-top: 5px;">
git add DB/migrations/<br>
git commit -m "Add new table schema"<br>
git push
                    </div>
                </li>
            </ul>

            <hr>

            <h4><span class="label label-success">Step 2</span> When Developer B pulls the code from GitHub:</h4>
            <ul>
                <li>Developer B pulls the changes:
                    <div class="code-box" style="margin-top: 5px;">git pull</div>
                </li>
                <li>Developer B visits <code>http://localhost/aster/db_sync.php</code> and clicks <strong>"Sync Database Now"</strong> (or runs <code>php DB/sync.php</code> from terminal).</li>
                <li>The non-existent table is automatically created in Developer B's MySQL, without touching any of Developer B's existing records or requiring full database export!</li>
            </ul>

            <hr>

            <h4><span class="label label-warning">Step 3</span> Personal MySQL Passwords & Ports (No Git Conflicts):</h4>
            <p>
                Each developer can have their own credentials in <code>application/settings/db_local.php</code> (which is ignored by Git in <code>.gitignore</code>).
                You will never overwrite each other's database port or password again!
            </p>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

