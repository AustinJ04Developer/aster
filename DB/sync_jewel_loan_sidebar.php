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
    echo " [SUCCESS] Jewel Loan sidebar synchronized and cleaned successfully.\n\n";
    echo "Current Jewel Loan Sidebar Menu Order (PSMID = 50):\n";
    echo "------------------------------------------------------------------------------------\n";
    printf(" %-8s | %-26s | %-32s | %-16s\n", "PageId", "PageName", "Link", "Icon");
    echo "------------------------------------------------------------------------------------\n";
    $res = $conn->query("SELECT PageId, PageName, Link, IconName FROM page WHERE PSMID = 50 ORDER BY PageId");
    while ($r = $res->fetch_assoc()) {
        printf(" %-8s | %-26s | %-32s | %-16s\n", $r['PageId'], $r['PageName'], $r['Link'], $r['IconName']);
    }
    echo "------------------------------------------------------------------------------------\n";
} else {
    echo " [ERROR] Synchronization encountered an issue.\n";
}
