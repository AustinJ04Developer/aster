-- Migration Script: Add ProcessingFeePercentage and ProcessingFee to ast_jewel_loan_details
-- Database: aster

ALTER TABLE `ast_jewel_loan_details` 
ADD COLUMN IF NOT EXISTS `ProcessingFeePercentage` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `AppraiserCharge`,
ADD COLUMN IF NOT EXISTS `ProcessingFee` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `ProcessingFeePercentage`;
