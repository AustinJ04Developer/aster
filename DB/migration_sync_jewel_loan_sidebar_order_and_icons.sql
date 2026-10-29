-- =====================================================================
-- Migration: Synchronize Jewel Loan Sidebar Tabs, Order, and Purposeful Icons
-- Order: Accounts -> Masters -> Receipts
-- Database: aster
-- =====================================================================

USE `aster`;

-- 1. Create a temporary mapping or update existing records safely
-- Target Order:
-- 2930: Jewel Loan Application  | Link: aster-jewel-loan-search.php       | Icon: glyphicon-book
-- 2931: JL - Create New Account | Link: aster-jewel-loan.php              | Icon: glyphicon-user
-- 2932: Account List            | Link: aster-jewel-loan-account-list.php | Icon: glyphicon-th-list
-- 2933: Scheme Master           | Link: aster-master-gl-schemes.php       | Icon: glyphicon-list-alt
-- 2934: Appraiser Charge Master | Link: aster-master-gl-appraiser.php     | Icon: glyphicon-usd
-- 2935: Processing Fee Master   | Link: aster-master-gl-processing-fee.php| Icon: glyphicon-certificate
-- 2936: Jewel Loan Receipt      | Link: aster-jewel-loan-receipt.php      | Icon: glyphicon-print
-- 2937: Receipt List            | Link: aster-jewel-loan-receipt-list.php | Icon: glyphicon-tasks
-- 2938: Delete Receipt          | Link: aster-jewel-loan-delete-receipt.php| Icon: glyphicon-trash

-- Update icons and descriptions
UPDATE `page` SET `PageName` = 'Jewel Loan Application', `IconName` = 'glyphicon-book', `Description` = 'Jewel Loan Application & Search' WHERE `Link` = 'aster-jewel-loan-search.php';
UPDATE `page` SET `PageName` = 'JL - Create New Account', `IconName` = 'glyphicon-user', `Description` = 'Create New Jewel Loan Account' WHERE `Link` = 'aster-jewel-loan.php';
UPDATE `page` SET `PageName` = 'Account List', `IconName` = 'glyphicon-th-list', `Description` = 'Jewel Loan Account List & Monthly Due Dates' WHERE `Link` = 'aster-jewel-loan-account-list.php';
UPDATE `page` SET `PageName` = 'Scheme Master', `IconName` = 'glyphicon-list-alt', `Description` = 'Jewel Loan Schemes Master' WHERE `Link` = 'aster-master-gl-schemes.php';
UPDATE `page` SET `PageName` = 'Appraiser Charge Master', `IconName` = 'glyphicon-usd', `Description` = 'Jewel Loan Appraiser Charge Master' WHERE `Link` = 'aster-master-gl-appraiser.php';
UPDATE `page` SET `PageName` = 'Processing Fee Master', `IconName` = 'glyphicon-certificate', `Description` = 'Jewel Loan Processing Fee Master' WHERE `Link` = 'aster-master-gl-processing-fee.php';
UPDATE `page` SET `PageName` = 'Jewel Loan Receipt', `IconName` = 'glyphicon-print', `Description` = 'Jewel Loan Payment & Release Receipt Generation' WHERE `Link` = 'aster-jewel-loan-receipt.php';
UPDATE `page` SET `PageName` = 'Receipt List', `IconName` = 'glyphicon-tasks', `Description` = 'Jewel Loan Receipt List & Historical Records' WHERE `Link` = 'aster-jewel-loan-receipt-list.php';
UPDATE `page` SET `PageName` = 'Delete Receipt', `IconName` = 'glyphicon-trash', `Description` = 'Jewel Loan Delete Today\'s Receipt' WHERE `Link` = 'aster-jewel-loan-delete-receipt.php';

