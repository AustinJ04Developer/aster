<?php
/**
 * Migration runner: Register 'Delete Receipt' page and assign permissions
 */
require_once __DIR__ . '/../application/settings/config.php';
require_once __DIR__ . '/../application/settings/db.php';

echo "Running Migration: Register 'Delete Receipt' page in Jewel Loan sidebar...\n";

// 1. Check or insert into page table
$checkPage = $conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-receipt.php'");
if ($checkPage && $checkPage->num_rows > 0) {
    $row = $checkPage->fetch_assoc();
    $pageId = (int)$row['PageId'];
    echo " [SKIP] Page 'aster-jewel-loan-delete-receipt.php' already exists with PageId: {$pageId}.\n";
} else {
    // Determine PageId
    $maxRes = $conn->query("SELECT MAX(PageId) as max_id FROM `page`");
    $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
    $nextId = max(2937, ($maxRow ? (int)$maxRow['max_id'] + 1 : 2937));

    $sqlInsert = "INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                  VALUES (?, 'Delete Receipt', 'aster-jewel-loan-delete-receipt.php', 'Jewel Loan Delete Receipt', 1, 50, 'glyphicon-bookmark')";
    $stmt = $conn->prepare($sqlInsert);
    if ($stmt) {
        $stmt->bind_param("i", $nextId);
        if ($stmt->execute()) {
            $pageId = $nextId;
            echo " [OK] Inserted 'Delete Receipt' page with PageId: {$pageId}.\n";
        } else {
            echo " [ERR] Failed inserting page: " . $stmt->error . "\n";
            exit(1);
        }
        $stmt->close();
    } else {
        echo " [ERR] Prepare failed: " . $conn->error . "\n";
        exit(1);
    }
}

// 2. Assign privileges for RoleId (1: Admin, 2: MD, 3: Manager, 4: Accountant, 5: Clerk, 8: Operator)
$roles = [1, 2, 3, 4, 5, 8];
$assigned = 0;
foreach ($roles as $roleId) {
    $checkPriv = $conn->query("SELECT 1 FROM `user_privilages` WHERE `RoleId` = {$roleId} AND `PageId` = {$pageId}");
    if ($checkPriv && $checkPriv->num_rows === 0) {
        $insPriv = "INSERT INTO `user_privilages` (`RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`) 
                    VALUES ({$roleId}, CURDATE(), {$pageId}, 1, 1, 1, 1, 1, 0)";
        if ($conn->query($insPriv)) {
            $assigned++;
        }
    }
}
echo " [OK] Privileges checked/assigned for {$assigned} roles.\n";
echo "Migration finished successfully.\n";
