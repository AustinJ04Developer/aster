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
 * - Syncs changes across Git branches (main / dee / AJ)
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

// 1. Git Remote & Branch Detection
$gitBranch = trim(@shell_exec('git rev-parse --abbrev-ref HEAD 2>&1') ?: '');
$gitRemote = trim(@shell_exec('git remote get-url origin 2>&1') ?: '');
$gitHeadCommit = trim(@shell_exec('git log -1 --pretty=format:"%h - %s" 2>&1') ?: '');

// 2. Discover all available branches (local & remote)
$allBranches = [];
$rawBranchLines = explode("\n", trim(@shell_exec('git branch -a 2>&1') ?: ''));
foreach ($rawBranchLines as $bLine) {
    $clean = trim($bLine, "* \t\r\n");
    if (!$clean || strpos($clean, 'remotes/origin/HEAD') !== false) continue;
    if (strpos($clean, 'remotes/origin/') === 0) {
        $clean = substr($clean, strlen('remotes/origin/'));
    }
    if ($clean && !in_array($clean, $allBranches, true)) {
        $allBranches[] = $clean;
    }
}
if (empty($allBranches)) {
    $allBranches = ['main', 'dee', 'AJ'];
}
usort($allBranches, function($a, $b) use ($gitBranch) {
    if ($a === 'main') return -1;
    if ($b === 'main') return 1;
    if ($a === $gitBranch) return -1;
    if ($b === $gitBranch) return 1;
    return strcmp($a, $b);
});

// Selected target branch to compare / pull from (defaults to 'main')
$targetBranch = trim($_REQUEST['target_branch'] ?? ($_POST['target_branch'] ?? 'main'));
if (!in_array($targetBranch, $allBranches, true)) {
    $targetBranch = in_array('main', $allBranches, true) ? 'main' : ($allBranches[0] ?? 'main');
}

// 3. Handle Form Actions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
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
    } elseif ($action === 'git_pull_and_sync' || $action === 'merge_branch_and_sync') {
        $pullLog = '';
        $srcBranch = trim($_POST['source_branch'] ?? $targetBranch);
        $currentBranch = $gitBranch ?: 'main';

        $fetchOut = trim(@shell_exec('git fetch origin 2>&1') ?: '');
        if ($fetchOut) $pullLog .= $fetchOut . "\n";

        $mergeCommand = "git merge origin/" . escapeshellarg($srcBranch) . " --no-edit 2>&1";
        $mergeOut = trim(@shell_exec($mergeCommand) ?: '');
        if ($mergeOut) $pullLog .= $mergeOut . "\n";

        $syncResults = DatabaseSync::syncAll($conn, true);
        $createdCount = count($syncResults['tables_created']);
        $colsAddCount = count($syncResults['columns_added']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount = count($syncResults['errors']);

        $messageType = ($errorCount > 0) ? 'warning' : 'success';
        $message = "<strong>Merged changes from branch `{$srcBranch}` into `{$currentBranch}` & synced database!</strong><br>";
        $message .= "<small>Created <strong>{$createdCount}</strong> table(s) | Added <strong>{$colsAddCount}</strong> column(s) | Applied <strong>{$migratedCount}</strong> migration(s).</small><br>";
        if ($pullLog) {
            $message .= "<pre style='margin-top:8px;max-height:120px;overflow-y:auto;background:#2d3748;color:#f7fafc;font-size:12px;padding:8px;'>" . htmlspecialchars(trim($pullLog)) . "</pre>";
        }

        $gitHeadCommit = trim(@shell_exec('git log -1 --pretty=format:"%h - %s" 2>&1') ?: '');
    } elseif ($action === 'switch_branch_and_sync') {
        $switchLog = '';
        $newBranch = trim($_POST['switch_branch_to'] ?? $targetBranch);

        $fetchOut = trim(@shell_exec('git fetch origin 2>&1') ?: '');
        if ($fetchOut) $switchLog .= $fetchOut . "\n";

        $checkoutOut = trim(@shell_exec('git checkout ' . escapeshellarg($newBranch) . ' 2>&1') ?: '');
        $switchLog .= $checkoutOut . "\n";

        $pullOut = trim(@shell_exec('git pull origin ' . escapeshellarg($newBranch) . ' 2>&1') ?: '');
        if ($pullOut) $switchLog .= $pullOut . "\n";

        $gitBranch = trim(@shell_exec('git rev-parse --abbrev-ref HEAD 2>&1') ?: $newBranch);
        $gitHeadCommit = trim(@shell_exec('git log -1 --pretty=format:"%h - %s" 2>&1') ?: '');

        $syncResults = DatabaseSync::syncAll($conn, true);
        $createdCount = count($syncResults['tables_created']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount = count($syncResults['errors']);

        $messageType = ($errorCount > 0) ? 'warning' : 'success';
        $message = "<strong>Switched to branch `{$newBranch}` & pulled latest updates!</strong><br>";
        $message .= "<small>Created <strong>{$createdCount}</strong> table(s) | Applied <strong>{$migratedCount}</strong> migration(s).</small><br>";
        if ($switchLog) {
            $message .= "<pre style='margin-top:8px;max-height:120px;overflow-y:auto;background:#2d3748;color:#f7fafc;font-size:12px;padding:8px;'>" . htmlspecialchars(trim($switchLog)) . "</pre>";
        }
    } elseif ($action === 'export_table') {
        $tableName = trim($_POST['export_table_name'] ?? '');
        if ($tableName) {
            $fileCreated = DatabaseSync::exportTableToMigration($conn, $tableName);
            if ($fileCreated) {
                $messageType = 'success';
                $message = "Created migration file: <strong>DB/migrations/{$fileCreated}</strong> for table `{$tableName}`. Commit this file to GitHub to share with your collaborator!";
            } else {
                $messageType = 'danger';
                $message = "Failed to export table `{$tableName}`.";
            }
        }
    }
}

// 4. Gather status information
$existingTables = [];
$res = $conn->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
if ($res) {
    while ($row = $res->fetch_row()) {
        $existingTables[] = $row[0];
    }
}

// Read applied migrations from DB
$appliedMigrations = DatabaseSync::getAppliedMigrations($conn);

// Read available migrations from directory
$migrationsDir = DatabaseSync::getMigrationsDir();
$migrationFiles = [];
if (is_dir($migrationsDir)) {
    foreach (scandir($migrationsDir) as $f) {
        if (preg_match('/\.sql$/i', $f)) {
            $migrationFiles[] = $f;
        }
    }
    sort($migrationFiles);
}

// Find pending migrations
$pendingMigrations = array_diff($migrationFiles, $appliedMigrations);

// Read master schema file info
$schemaFile = DatabaseSync::getSchemaDir() . '/aster_structure.sql';
$schemaUpdatedTime = file_exists($schemaFile) ? date('d/m/Y h:i A', filemtime($schemaFile)) : 'Not generated yet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Aster - Database Sync & Change Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; padding-top: 30px; padding-bottom: 50px; font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .hero { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff; padding: 25px; border-radius: 6px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .hero h1 { margin: 0 0 8px 0; font-size: 26px; font-weight: 700; }
        .hero p { margin: 0; opacity: 0.9; font-size: 14px; }
        .stat-card { background: #fff; border-radius: 6px; padding: 18px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-left: 4px solid #337ab7; }
        .stat-card.success { border-left-color: #5cb85c; }
        .stat-card.warning { border-left-color: #f0ad4e; }
        .stat-card.info { border-left-color: #5bc0de; }
        .stat-card .num { font-size: 26px; font-weight: bold; }
        .stat-card .lbl { color: #777; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .panel { box-shadow: 0 2px 6px rgba(0,0,0,0.05); border-radius: 6px; }
        .panel-heading { font-weight: 600; }
        .badge-git { background-color: #fff; color: #1e3c72; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 13px; }
        .code-box { background: #2d3748; color: #f7fafc; padding: 12px; border-radius: 4px; font-family: Consolas, monospace; font-size: 12px; }
        .nav-actions { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="hero">
        <div class="row">
            <div class="col-md-7">
                <h1><i class="glyphicon glyphicon-transfer"></i> Aster Database Sync Engine</h1>
                <p>Collaborative schema synchronizer for Git & MySQL. Automatically fuses missing tables, columns, types, indexes, and migrations.</p>
            </div>
            <div class="col-md-5 text-right" style="padding-top: 10px;">
                <?php if ($gitBranch && stripos($gitBranch, 'fatal:') === false): ?>
                    <span class="badge-git"><i class="glyphicon glyphicon-random"></i> Branch: <?= htmlspecialchars($gitBranch) ?></span>
                    <?php if ($gitHeadCommit): ?>
                        <div style="font-size: 11px; margin-top: 6px; opacity: 0.85; font-family: monospace;">
                            <?= htmlspecialchars($gitHeadCommit) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="nav-actions">
        <a href="application/views/aster-jewel-loan-account-list.php" class="btn btn-default"><i class="glyphicon glyphicon-arrow-left"></i> Back to Account List</a>
        <a href="application/views/aster-jewel-loan-receipt.php" class="btn btn-default"><i class="glyphicon glyphicon-file"></i> Open Jewel Loan Receipt</a>
        <a href="application/views/aster-jewel-loan-receipt-list.php" class="btn btn-default"><i class="glyphicon glyphicon-tasks"></i> Receipt List</a>
        <a href="tests/run_all_tests.php" target="_blank" class="btn btn-default"><i class="glyphicon glyphicon-ok"></i> Run Automated Tests</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="num text-primary"><?= count($existingTables) ?></div>
                <div class="lbl">Active Tables in DB</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card success">
                <div class="num text-success"><?= count($appliedMigrations) ?></div>
                <div class="lbl">Applied Migrations</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card <?= count($pendingMigrations) > 0 ? 'warning' : 'info' ?>">
                <div class="num <?= count($pendingMigrations) > 0 ? 'text-warning' : 'text-info' ?>"><?= count($pendingMigrations) ?></div>
                <div class="lbl">Pending Migrations</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="num" style="font-size: 16px; margin-top: 6px; font-weight: bold;"><?= $schemaUpdatedTime ?></div>
                <div class="lbl">Master Schema Status</div>
            </div>
        </div>
    </div>

    <!-- Main Sync Action Card -->
    <div class="panel panel-primary">
        <div class="panel-body text-center" style="padding: 25px;">
            <h3 style="margin-top:0;">Manage Every Change in the Database</h3>
            <p class="text-muted" style="max-width: 700px; margin: 0 auto 20px auto;">
                Synchronizes <strong>tables, missing columns, column definitions (including enums), indexes, and migrations</strong>.
                Ensures all collaborators stay 100% in sync without losing existing data or manual dumps.
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-bottom: 10px;">
                <form method="POST" style="display: inline-block;">
                    <input type="hidden" name="action" value="sync_now">
                    <button type="submit" class="btn btn-success btn-lg" style="padding: 12px 30px; font-size: 15px; font-weight: bold;">
                        <i class="glyphicon glyphicon-flash"></i> Sync Every Change Now
                    </button>
                </form>
                <?php if ($gitBranch && stripos($gitBranch, 'fatal:') === false): ?>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="action" value="git_pull_and_sync">
                        <input type="hidden" name="source_branch" value="<?= htmlspecialchars($targetBranch) ?>">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 25px; font-size: 15px;">
                            <i class="glyphicon glyphicon-cloud-download"></i> Pull & Merge from <code><?= htmlspecialchars($targetBranch) ?></code> + Sync DB
                        </button>
                    </form>
                <?php endif; ?>
                <form method="POST" style="display: inline-block;">
                    <input type="hidden" name="action" value="export_master_schema">
                    <button type="submit" class="btn btn-default btn-lg" style="padding: 12px 25px; font-size: 15px;" title="Updates aster_structure.sql with current database structure">
                        <i class="glyphicon glyphicon-save-file"></i> Save DB Structure to Master Schema
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Migrations Tracking Panel -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="glyphicon glyphicon-list-alt"></i> Migration History & Tracking (<code>DB/migrations/</code>)</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin-bottom:0; font-size: 13px;">
                        <thead>
                            <tr style="background:#f9f9f9;">
                                <th style="width: 50px;">#</th>
                                <th>Migration File</th>
                                <th style="width: 140px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($migrationFiles)): ?>
                                <tr><td colspan="3" class="text-center text-muted" style="padding: 15px;">No migration files found in DB/migrations/</td></tr>
                            <?php else: ?>
                                <?php foreach ($migrationFiles as $idx => $mFile): 
                                    $isApplied = in_array($mFile, $appliedMigrations, true);
                                ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><code><?= htmlspecialchars($mFile) ?></code></td>
                                        <td class="text-center">
                                            <?php if ($isApplied): ?>
                                                <span class="label label-success"><i class="glyphicon glyphicon-ok"></i> Applied</span>
                                            <?php else: ?>
                                                <span class="label label-warning"><i class="glyphicon glyphicon-time"></i> Pending</span>
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
        <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> How Collaborators Share Database Changes via GitHub</div>
        <div class="panel-body">
            <h4><span class="label label-primary">Step 1</span> When Developer creates a new table or changes schema:</h4>
            <ul>
                <li><strong>Option A (One-Click):</strong> Go to this page, select the new table under <em>"Export a Newly Created Table"</em>, and click Generate.</li>
                <li><strong>Option B (Manual):</strong> Create a new file in <code>DB/migrations/</code> (e.g., <code>011_create_new_feature.sql</code>) containing:
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

            <h4><span class="label label-success">Step 2</span> When Collaborator syncs changes from GitHub:</h4>
            <ul>
                <li>Visit <code>http://localhost/aster/db_sync.php</code>.</li>
                <li>Choose the source branch from the <strong>Sync Source</strong> dropdown (e.g. <code>main</code> or any other branch).</li>
                <li>Click <strong>"Pull & Merge from {branch} + Sync DB"</strong>.</li>
                <li>The non-existent table is automatically created in MySQL, and all new migrations are safely executed without touching existing records or requiring full database export!</li>
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
