-- Migration script for BalanceLoanAmount and UpiToBank
USE `aster`;

-- 1. Add BalanceLoanAmount column to ast_jewel_loan_details if it does not exist
ALTER TABLE `ast_jewel_loan_details` 
ADD COLUMN IF NOT EXISTS `BalanceLoanAmount` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `LoanAmount`;

-- 2. Add UpiToBank column to ast_jewel_loan_details if it does not exist
ALTER TABLE `ast_jewel_loan_details` 
ADD COLUMN IF NOT EXISTS `UpiToBank` varchar(100) DEFAULT NULL AFTER `UpiTransactionNumber`;
