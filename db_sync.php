<?php
/**
 * Aster - Database Sync & Change Manager Dashboard
 * 
<<<<<<< HEAD
 * Allows collaborators on GitHub to sync missing tables and migrations
 * from `main` or any other branch without exporting/overwriting the entire database.
=======
 * Manages EVERY change in the database:
 * - Detects and creates non-existent tables
 * - Detects and adds missing columns (ALTER TABLE ADD COLUMN)
 * - Detects and synchronizes modified column types/enums (ALTER TABLE MODIFY COLUMN)
 * - Detects and creates missing indexes
 * - Runs pending migrations
 * - Automatically captures live database structure into aster_structure.sql for Git
>>>>>>> AJ
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
// Sort branches with 'main' first, then current branch, then others
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

        // 1. Fetch all remote updates
        $fetchOut = trim(@shell_exec('git fetch origin 2>&1') ?: '');
        if ($fetchOut) $pullLog .= $fetchOut . "\n";

        // 2. Pull / Merge changes from specified target branch
        $mergeCommand = "git merge origin/" . escapeshellarg($srcBranch) . " --no-edit 2>&1";
        $mergeOut = trim(@shell_exec($mergeCommand) ?: '');
        if ($mergeOut) $pullLog .= $mergeOut . "\n";

        // 3. Immediately run DatabaseSync
        $syncResults = DatabaseSync::syncAll($conn);
        $createdCount = count($syncResults['tables_created']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount = count($syncResults['errors']);

        $messageType = ($errorCount > 0) ? 'warning' : 'success';
        $message = "<strong>Merged changes from branch `{$srcBranch}` into `{$currentBranch}` & synced database!</strong><br>";
        $message .= "<small>Created <strong>{$createdCount}</strong> table(s) | Applied <strong>{$migratedCount}</strong> migration(s).</small><br>";
        if ($pullLog) {
            $message .= "<pre style='margin-top:8px;max-height:120px;overflow-y:auto;background:#2d3748;color:#f7fafc;font-size:12px;padding:8px;'>" . htmlspecialchars(trim($pullLog)) . "</pre>";
        }

        // Refresh head commit info after merge
        $gitHeadCommit = trim(@shell_exec('git log -1 --pretty=format:"%h - %s" 2>&1') ?: '');
    } elseif ($action === 'switch_branch_and_sync') {
        $switchLog = '';
        $newBranch = trim($_POST['switch_branch_to'] ?? $targetBranch);

        // Fetch
        $fetchOut = trim(@shell_exec('git fetch origin 2>&1') ?: '');
        if ($fetchOut) $switchLog .= $fetchOut . "\n";

        // Checkout branch
        $checkoutOut = trim(@shell_exec('git checkout ' . escapeshellarg($newBranch) . ' 2>&1') ?: '');
        $switchLog .= $checkoutOut . "\n";

        // Pull latest from remote branch
        $pullOut = trim(@shell_exec('git pull origin ' . escapeshellarg($newBranch) . ' 2>&1') ?: '');
        if ($pullOut) $switchLog .= $pullOut . "\n";

        // Update active branch variable
        $gitBranch = trim(@shell_exec('git rev-parse --abbrev-ref HEAD 2>&1') ?: $newBranch);
        $gitHeadCommit = trim(@shell_exec('git log -1 --pretty=format:"%h - %s" 2>&1') ?: '');

        // Immediately run DatabaseSync
        $syncResults = DatabaseSync::syncAll($conn);
        $createdCount = count($syncResults['tables_created']);
        $migratedCount = count($syncResults['migrations_run']);
        $errorCount = count($syncResults['errors']);

        $messageType = ($errorCount > 0) ? 'warning' : 'success';
        $message = "<strong>Switched active branch to `{$newBranch}` and synced database!</strong><br>";
        $message .= "<small>Created <strong>{$createdCount}</strong> table(s) | Applied <strong>{$migratedCount}</strong> migration(s).</small><br>";
        if ($switchLog) {
            $message .= "<pre style='margin-top:8px;max-height:120px;overflow-y:auto;background:#2d3748;color:#f7fafc;font-size:12px;padding:8px;'>" . htmlspecialchars(trim($switchLog)) . "</pre>";
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

// 4. Fetch current database state
DatabaseSync::initMigrationsTable($conn);
$existingTables = DatabaseSync::getExistingTables($conn);
$appliedMigrations = DatabaseSync::getAppliedMigrations($conn);

// 5. Scan migration files
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

<<<<<<< HEAD
// Scan detected tables from all SQL files across schema, migrations, and DB directory
$allSchemaSqlFiles = glob(DatabaseSync::getSchemaDir() . '/*.sql') ?: [];
$allMigrationSqlFiles = glob($migrationsDir . '/*.sql') ?: [];
$allDbSqlFiles = glob(__DIR__ . '/DB/*.sql') ?: [];
$allSqlFiles = array_unique(array_merge($allSchemaSqlFiles, $allMigrationSqlFiles, $allDbSqlFiles));
=======
// Scan detected tables from SQL files to see if any are missing
$allSqlFiles = DatabaseSync::getScannableSqlFiles();
>>>>>>> AJ

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

<<<<<<< HEAD
// 6. Check incoming commits comparing current HEAD vs origin/{$targetBranch}
$incomingCommits = [];
if ($gitBranch && stripos($gitBranch, 'fatal:') === false) {
    $cmd = "git log HEAD..origin/" . escapeshellarg($targetBranch) . " --oneline 2>&1";
    $incomingLog = trim(@shell_exec($cmd) ?: '');
    if ($incomingLog && stripos($incomingLog, 'fatal:') === false) {
        $lines = explode("\n", $incomingLog);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line) $incomingCommits[] = $line;
        }
    }
}
=======
$schemaFile = DatabaseSync::getSchemaDir() . '/aster_structure.sql';
$schemaUpdatedTime = file_exists($schemaFile) ? date('d/m/Y h:i A', filemtime($schemaFile)) : 'Never';
>>>>>>> AJ
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
        .git-bar { background: #fff; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .git-bar .git-info { font-size: 13px; color: #444; }
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

    <!-- Git Branch Selector & Synchronization Toolbar -->
    <?php if ($gitBranch && stripos($gitBranch, 'fatal:') === false): ?>
        <div class="git-bar">
            <div class="git-info">
                <strong><i class="glyphicon glyphicon-bullhorn"></i> Current Active Branch:</strong> 
                <span class="label label-primary" style="font-size: 13px;"><?= htmlspecialchars($gitBranch) ?></span> &nbsp;|&nbsp;
                <strong>Latest Local Commit:</strong> <span class="text-muted"><?= htmlspecialchars($gitHeadCommit) ?></span>
            </div>
            
            <!-- Target Branch Picker Form -->
            <form method="GET" action="db_sync.php" class="form-inline" style="margin: 0;">
                <label for="target_branch" style="font-size: 13px; margin-right: 5px;">
                    <i class="glyphicon glyphicon-random text-primary"></i> Sync Source:
                </label>
                <div class="input-group">
                    <select name="target_branch" id="target_branch" class="form-control input-sm" onchange="this.form.submit()" style="font-weight: bold;">
                        <?php foreach ($allBranches as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= ($b === $targetBranch) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b) ?><?= ($b === 'main') ? ' (Main / Merged)' : '' ?><?= ($b === $gitBranch) ? ' [Active]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default btn-sm" title="Refresh branch comparison">
                            <i class="glyphicon glyphicon-refresh"></i> Check
                        </button>
                    </span>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Incoming Commits Alert Banner (if target branch has incoming changes) -->
    <?php if (!empty($incomingCommits)): ?>
        <div class="alert alert-warning alert-dismissible" role="alert" style="border-left: 5px solid #f0ad4e; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
            <h4><i class="glyphicon glyphicon-cloud-download"></i> Incoming Merges / Changes Detected from <code>origin/<?= htmlspecialchars($targetBranch) ?></code>!</h4>
            <p>There are <strong><?= count($incomingCommits) ?></strong> new commit(s) on <code>origin/<?= htmlspecialchars($targetBranch) ?></code> that have not yet been applied to your local branch <code><?= htmlspecialchars($gitBranch) ?></code>:</p>
            <ul style="margin-bottom: 15px;">
                <?php foreach (array_slice($incomingCommits, 0, 6) as $ic): ?>
                    <li><code><?= htmlspecialchars($ic) ?></code></li>
                <?php endforeach; ?>
                <?php if (count($incomingCommits) > 6): ?>
                    <li><em>...and <?= count($incomingCommits) - 6 ?> more commit(s).</em></li>
                <?php endif; ?>
            </ul>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="merge_branch_and_sync">
                    <input type="hidden" name="source_branch" value="<?= htmlspecialchars($targetBranch) ?>">
                    <button type="submit" class="btn btn-warning btn-md" style="font-weight: bold;">
                        <i class="glyphicon glyphicon-import"></i> Merge Changes from <code><?= htmlspecialchars($targetBranch) ?></code> & Sync Database Now
                    </button>
                </form>
                <?php if ($gitBranch !== $targetBranch): ?>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="switch_branch_and_sync">
                        <input type="hidden" name="switch_branch_to" value="<?= htmlspecialchars($targetBranch) ?>">
                        <button type="submit" class="btn btn-default btn-md" style="font-weight: bold;">
                            <i class="glyphicon glyphicon-share-alt"></i> Switch to <code><?= htmlspecialchars($targetBranch) ?></code> & Sync
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($gitBranch && stripos($gitBranch, 'fatal:') === false): ?>
        <div class="alert alert-success" style="padding: 10px 15px; margin-bottom: 20px; border-left: 4px solid #5cb85c;">
            <i class="glyphicon glyphicon-ok-sign"></i> 
            Your current branch <code><?= htmlspecialchars($gitBranch) ?></code> is completely up to date with <code>origin/<?= htmlspecialchars($targetBranch) ?></code>.
        </div>
    <?php endif; ?>

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
<<<<<<< HEAD
            <h3 style="margin-top:0;">Synchronize Database with GitHub Changes</h3>
            <p class="text-muted" style="max-width: 700px; margin: 0 auto 20px auto;">
                Checks shared schema and migration files from GitHub.
                Any <strong>non-existent table</strong> will be automatically created, and any <strong>new migration</strong> will be executed.
                Your existing tables and test data will <strong>NEVER be deleted or wiped</strong>!
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <form method="POST" style="display: inline-block;">
                    <input type="hidden" name="action" value="sync_now">
                    <button type="submit" class="btn btn-success btn-lg" style="padding: 12px 30px; font-size: 15px; font-weight: bold;">
                        <i class="glyphicon glyphicon-flash"></i> Sync Database Now (Local Files)
                    </button>
                </form>
                <?php if ($gitBranch && stripos($gitBranch, 'fatal:') === false): ?>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="action" value="merge_branch_and_sync">
                        <input type="hidden" name="source_branch" value="<?= htmlspecialchars($targetBranch) ?>">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 30px; font-size: 15px; font-weight: bold;">
                            <i class="glyphicon glyphicon-cloud-download"></i> Pull & Merge from <code><?= htmlspecialchars($targetBranch) ?></code> + Sync DB
                        </button>
                    </form>
                <?php endif; ?>
            </div>
=======
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
>>>>>>> AJ
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

<<<<<<< HEAD
    <!-- Instructions / Collaboration Workflow Guide -->
    <div class="panel panel-info">
        <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> How Both Collaborators Share Database Changes via GitHub</div>
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

=======
>>>>>>> AJ
</div>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
