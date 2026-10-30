<?php
/**
 * Jewel Loan Sidebar Synchronizer & Database Integrity Fixer
 * Restores and maintains Jewel Loan sidebar tabs strictly under PSMID = 50 (Jewel Loan).
 * Completely purges any broken temporary records that could affect other menu sections.
 */
class JewelLoanSidebarSync {
    public static function sync($conn) {
        if (!$conn) {
            return false;
        }

        try {
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");

            // 1. Revert and clean up any stray or temporary records outside standard ranges
            $conn->query("DELETE FROM `user_privilages` WHERE `PageId` >= 9000");
            $conn->query("DELETE FROM `page` WHERE `PageId` >= 9000");

            // Purge any orphaned privileges pointing to non-existent pages (these cause blank items above User Settings)
            $conn->query("DELETE up FROM `user_privilages` up LEFT JOIN `page` p ON up.PageId = p.PageId WHERE p.PageId IS NULL");

            // Purge any page with NULL or 0 PSMID
            $conn->query("DELETE FROM `user_privilages` WHERE `PageId` IN (SELECT PageId FROM `page` WHERE `PSMID` IS NULL OR `PSMID` = 0)");
            $conn->query("DELETE FROM `page` WHERE `PSMID` IS NULL OR `PSMID` = 0");

            // 2. Ensure all Jewel Loan pages are strictly assigned to PSMID = 50 (Jewel Loan)
            $conn->query("UPDATE `page` SET `PSMID` = 50 WHERE `Link` LIKE 'aster-jewel-loan%' OR `Link` LIKE 'aster-master-gl%'");
            $conn->query("UPDATE `page` SET `PageName` = 'Jewel Loan Search', `Description` = 'Jewel Loan Search' WHERE `Link` = 'aster-jewel-loan-search.php'");

            // 3. Make sure Receipt List exists under PSMID = 50 with proper icon
            $receiptListId = 2937;
            $rlRes = $conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-receipt-list.php' LIMIT 1");
            if ($rlRes && $rlRes->num_rows > 0) {
                $rlRow = $rlRes->fetch_assoc();
                $receiptListId = (int)$rlRow['PageId'];
                $conn->query("UPDATE `page` SET `PageName` = 'Receipt List', `IconName` = 'glyphicon-tasks', `PSMID` = 50 WHERE `PageId` = {$receiptListId}");
            } else {
                $conn->query("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                              VALUES ({$receiptListId}, 'Receipt List', 'aster-jewel-loan-receipt-list.php', 'Jewel Loan Receipt List', 1, 50, 'glyphicon-tasks')");
            }

            // 4. Register or Update Delete Receipt immediately after Receipt List under PSMID = 50
            $deleteReceiptId = 2938;
            $drRes = $conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-receipt.php' LIMIT 1");
            if ($drRes && $drRes->num_rows > 0) {
                $drRow = $drRes->fetch_assoc();
                $deleteReceiptId = (int)$drRow['PageId'];
                $conn->query("UPDATE `page` SET `PageName` = 'Delete Receipt', `IconName` = 'glyphicon-trash', `PSMID` = 50, `UserId` = 1 WHERE `PageId` = {$deleteReceiptId}");
            } else {
                $conn->query("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                              VALUES ({$deleteReceiptId}, 'Delete Receipt', 'aster-jewel-loan-delete-receipt.php', 'Jewel Loan Delete Today\'s Receipt', 1, 50, 'glyphicon-trash')");
            }

            // 5. Register or Update Closed Accounts List under PSMID = 50
            $closedAccountsId = 2939;
            $caRes = $conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-closed-accounts.php' LIMIT 1");
            if ($caRes && $caRes->num_rows > 0) {
                $caRow = $caRes->fetch_assoc();
                $closedAccountsId = (int)$caRow['PageId'];
                $conn->query("UPDATE `page` SET `PageName` = 'Closed Accounts List', `IconName` = 'glyphicon-folder-close', `PSMID` = 50, `UserId` = 1 WHERE `PageId` = {$closedAccountsId}");
            } else {
                $conn->query("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                              VALUES ({$closedAccountsId}, 'Closed Accounts List', 'aster-jewel-loan-closed-accounts.php', 'Jewel Loan Closed Accounts List', 1, 50, 'glyphicon-folder-close')");
            }

            // 6. Register or Update Delete Account under PSMID = 50
            $deleteAccountId = 2940;
            $daRes = $conn->query("SELECT PageId FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-account.php' LIMIT 1");
            if ($daRes && $daRes->num_rows > 0) {
                $daRow = $daRes->fetch_assoc();
                $deleteAccountId = (int)$daRow['PageId'];
                $conn->query("UPDATE `page` SET `PageName` = 'Delete Account', `IconName` = 'glyphicon-remove', `PSMID` = 50, `UserId` = 1 WHERE `PageId` = {$deleteAccountId}");
            } else {
                $conn->query("INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) 
                              VALUES ({$deleteAccountId}, 'Delete Account', 'aster-jewel-loan-delete-account.php', 'Jewel Loan Delete Account', 1, 50, 'glyphicon-remove')");
            }

            // 7. Ensure privileges for all standard roles (1, 2, 3, 4, 5, 8)
            $roles = [1, 2, 3, 4, 5, 8];
            $allJlPages = [$receiptListId, $deleteReceiptId, $closedAccountsId, $deleteAccountId];
            foreach ($allJlPages as $pId) {
                foreach ($roles as $rId) {
                    $pChk = $conn->query("SELECT UPId FROM `user_privilages` WHERE `RoleId` = {$rId} AND `PageId` = {$pId} LIMIT 1");
                    if ($pChk && $pChk->num_rows > 0) {
                        $conn->query("UPDATE `user_privilages` SET `DView` = 1, `IsDeleted` = 0 WHERE `RoleId` = {$rId} AND `PageId` = {$pId}");
                    } else {
                        $conn->query("INSERT INTO `user_privilages` (`RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`) 
                                      VALUES ({$rId}, CURDATE(), {$pId}, 1, 1, 1, 1, 1, 0)");
                    }
                }
            }

            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
