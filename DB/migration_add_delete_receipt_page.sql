-- Migration: Register Delete Receipt page, configure Jewel Loan sidebar, and assign user privileges
-- Database: aster

USE `aster`;

-- 1. Insert Delete Receipt into `page` table under PSMID 50 (Jewel Loan)
INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`)
SELECT 2937, 'Delete Receipt', 'aster-jewel-loan-delete-receipt.php', 'Jewel Loan Delete Receipt', 1, 50, 'glyphicon-bookmark'
WHERE NOT EXISTS (
    SELECT 1 FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-receipt.php'
);

-- 2. Unify all Jewel Loan subpages to use the dedicated glyphicon-bookmark icon
UPDATE `page` SET `IconName` = 'glyphicon-bookmark' WHERE `PSMID` = 50;

-- 3. Insert User Privileges for Admin (1), MD (2), Manager (3), Accountant (4), Clerk (5), Operator (8)
INSERT INTO `user_privilages` (`RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`)
SELECT r.RoleId, CURDATE(), p.PageId, 1, 1, 1, 1, 1, 0
FROM `page` p
CROSS JOIN (
    SELECT 1 AS RoleId UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4 UNION ALL
    SELECT 5 UNION ALL
    SELECT 8
) r
WHERE p.Link = 'aster-jewel-loan-delete-receipt.php'
  AND NOT EXISTS (
      SELECT 1 FROM `user_privilages` up 
      WHERE up.RoleId = r.RoleId AND up.PageId = p.PageId
  );

