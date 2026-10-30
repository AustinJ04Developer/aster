-- ========================================================
-- Migration: Fix D-Number Uniqueness, Concurrency & Receipts
-- Database: aster
-- (Preserves AccountNumber and all existing fields - NO DROP COLUMN)
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

-- Ensure foreign key from ast_jewel_loan_receipt to ast_jewel_loan_details on LoanDetailId
SET @exist_fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = 'aster' AND TABLE_NAME = 'ast_jewel_loan_receipt' AND CONSTRAINT_NAME = 'fk_receipt_loan_detail');
SET @sql_add_fk := IF(@exist_fk = 0, 'ALTER TABLE `ast_jewel_loan_receipt` ADD CONSTRAINT `fk_receipt_loan_detail` FOREIGN KEY (`LoanDetailId`) REFERENCES `ast_jewel_loan_details` (`LoanDetailId`) ON DELETE CASCADE ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt_add_fk FROM @sql_add_fk;
EXECUTE stmt_add_fk;
DEALLOCATE PREPARE stmt_add_fk;
