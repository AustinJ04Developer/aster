-- ========================================================
-- Migration: Remove Account Number, Fix D-Number Uniqueness, Concurrency & Receipts
-- Database: aster
-- ========================================================

USE `aster`;

-- 1. Create dedicated atomic sequence table for D-Number concurrency safe allocation
CREATE TABLE IF NOT EXISTS `ast_jewel_loan_sequence` (
  `seq_name` varchar(50) NOT NULL,
  `current_val` int(11) NOT NULL,
  PRIMARY KEY (`seq_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Initialize D-Number sequence based on maximum existing D-Number in ast_jewel_loan_customer
INSERT INTO `ast_jewel_loan_sequence` (`seq_name`, `current_val`)
VALUES ('d_number', (
  SELECT COALESCE(MAX(CAST(SUBSTRING(DNumber, 3) AS UNSIGNED)), 0) FROM `ast_jewel_loan_customer`
))
ON DUPLICATE KEY UPDATE `current_val` = (
  SELECT COALESCE(MAX(CAST(SUBSTRING(DNumber, 3) AS UNSIGNED)), 0) FROM `ast_jewel_loan_customer`
);

-- 2. Modify ast_jewel_loan_details: drop AccountNumber column if present
-- Check and drop uk_account_number if exists
SET @exist_uk := (SELECT COUNT(*) FROM information_schema.STATISTICS 
  WHERE TABLE_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_details' AND INDEX_NAME = 'uk_account_number');
SET @sql_drop_uk := IF(@exist_uk > 0, 'ALTER TABLE `ast_jewel_loan_details` DROP INDEX `uk_account_number`', 'SELECT 1');
PREPARE stmt_drop_uk FROM @sql_drop_uk;
EXECUTE stmt_drop_uk;
DEALLOCATE PREPARE stmt_drop_uk;

SET @exist_col := (SELECT COUNT(*) FROM information_schema.COLUMNS 
  WHERE TABLE_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_details' AND COLUMN_NAME = 'AccountNumber');
SET @sql_drop_col := IF(@exist_col > 0, 'ALTER TABLE `ast_jewel_loan_details` DROP COLUMN `AccountNumber`', 'SELECT 1');
PREPARE stmt_drop_col FROM @sql_drop_col;
EXECUTE stmt_drop_col;
DEALLOCATE PREPARE stmt_drop_col;

-- 3. Modify ast_jewel_loan_receipt: remove AccountNumber column if present
SET @exist_r_idx := (SELECT COUNT(*) FROM information_schema.STATISTICS 
  WHERE TABLE_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_receipt' AND INDEX_NAME = 'idx_receipt_acc');
SET @sql_drop_r_idx := IF(@exist_r_idx > 0, 'ALTER TABLE `ast_jewel_loan_receipt` DROP INDEX `idx_receipt_acc`', 'SELECT 1');
PREPARE stmt_drop_r_idx FROM @sql_drop_r_idx;
EXECUTE stmt_drop_r_idx;
DEALLOCATE PREPARE stmt_drop_r_idx;

SET @exist_r_col := (SELECT COUNT(*) FROM information_schema.COLUMNS 
  WHERE TABLE_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_receipt' AND COLUMN_NAME = 'AccountNumber');
SET @sql_drop_r_col := IF(@exist_r_col > 0, 'ALTER TABLE `ast_jewel_loan_receipt` DROP COLUMN `AccountNumber`', 'SELECT 1');
PREPARE stmt_drop_r_col FROM @sql_drop_r_col;
EXECUTE stmt_drop_r_col;
DEALLOCATE PREPARE stmt_drop_r_col;

-- Ensure foreign key from ast_jewel_loan_receipt to ast_jewel_loan_details on LoanDetailId
SET @exist_fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_receipt' AND CONSTRAINT_NAME = 'fk_receipt_loan_detail');
SET @sql_add_fk := IF(@exist_fk = 0, 'ALTER TABLE `ast_jewel_loan_receipt` ADD CONSTRAINT `fk_receipt_loan_detail` FOREIGN KEY (`LoanDetailId`) REFERENCES `ast_jewel_loan_details` (`LoanDetailId`) ON DELETE CASCADE ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt_add_fk FROM @sql_add_fk;
EXECUTE stmt_add_fk;
DEALLOCATE PREPARE stmt_add_fk;
