-- ====================================================================
-- Migration 010: Add PRINCIPAL_PAYMENT transaction type and fix LOAN_CREATION totals
-- The bank pays the customer the loan principal on jewel loan creation;
-- the customer does NOT pay principal when the loan is created.
-- ====================================================================

-- 1. Alter TransactionType column in ast_jewel_loan_receipt to add PRINCIPAL_PAYMENT
ALTER TABLE `ast_jewel_loan_receipt` 
MODIFY COLUMN `TransactionType` ENUM('LOAN_CREATION','INTEREST_PAYMENT','PRINCIPAL_PAYMENT','JEWEL_RELEASE') NOT NULL;

-- 2. Correct existing LOAN_CREATION receipt records in database:
-- Set PrincipalPaid = 0.00 and TotalPaid = AppraiserCharge (the only charge paid by customer)
UPDATE `ast_jewel_loan_receipt`
SET `PrincipalPaid` = 0.00,
    `TotalPaid` = COALESCE(`AppraiserCharge`, 0.00)
WHERE `TransactionType` = 'LOAN_CREATION';

