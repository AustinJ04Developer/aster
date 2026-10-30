-- =====================================================================
-- Migration 011: Rename Jewel Loan Application to Jewel Loan Search,
-- Register Closed Accounts List and Delete Account pages under PSMID = 50
-- Database: aster
-- =====================================================================

USE `aster`;

-- 1. Rename Jewel Loan Application to Jewel Loan Search
UPDATE `page` 
SET `PageName` = 'Jewel Loan Search', 
    `Description` = 'Jewel Loan Search' 
WHERE `Link` = 'aster-jewel-loan-search.php';

-- 2. Register Closed Accounts List page (PageId 2939)
INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`)
SELECT 2939, 'Closed Accounts List', 'aster-jewel-loan-closed-accounts.php', 'Jewel Loan Closed Accounts List', 1, 50, 'glyphicon-folder-close'
WHERE NOT EXISTS (
    SELECT 1 FROM `page` WHERE `Link` = 'aster-jewel-loan-closed-accounts.php'
);

-- 3. Register Delete Account page (PageId 2940)
INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`)
SELECT 2940, 'Delete Account', 'aster-jewel-loan-delete-account.php', 'Jewel Loan Delete Account', 1, 50, 'glyphicon-remove'
WHERE NOT EXISTS (
    SELECT 1 FROM `page` WHERE `Link` = 'aster-jewel-loan-delete-account.php'
);

-- 4. Grant privileges for Closed Accounts List to standard roles (1, 2, 3, 4, 5, 8)
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
WHERE p.Link = 'aster-jewel-loan-closed-accounts.php'
  AND NOT EXISTS (
      SELECT 1 FROM `user_privilages` up 
      WHERE up.RoleId = r.RoleId AND up.PageId = p.PageId
  );

-- 5. Grant privileges for Delete Account to standard roles (1, 2, 3, 4, 5, 8)
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
WHERE p.Link = 'aster-jewel-loan-delete-account.php'
  AND NOT EXISTS (
      SELECT 1 FROM `user_privilages` up 
      WHERE up.RoleId = r.RoleId AND up.PageId = p.PageId
  );

