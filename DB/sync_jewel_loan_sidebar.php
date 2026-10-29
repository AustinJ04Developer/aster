<?php
/**
 * Synchronize Jewel Loan Sidebar Tabs, Order, and Purposeful Icons
 * Reverts any broken/temporary IDs and fixes sidebar integrity.
 */
require_once __DIR__ . '/../application/settings/config.php';
require_once __DIR__ . '/../application/settings/db.php';
require_once __DIR__ . '/../application/views/controller/model/JewelLoanSidebarSync.php';

echo "Synchronizing Jewel Loan Sidebar Tabs and purging corrupted records...\n";
$ok = JewelLoanSidebarSync::sync($conn);
if ($ok) {
    echo " [SUCCESS] Jewel Loan sidebar synchronized and cleaned successfully.\n";
} else {
    echo " [ERROR] Synchronization encountered an issue.\n";
}
