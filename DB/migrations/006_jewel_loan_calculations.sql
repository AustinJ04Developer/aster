-- =====================================================================
-- Migration: Jewel Loan Calculation Fixes, Rate History & Scenario Support
-- Safe, idempotent schema updates for Aster Jewel Loan Module
-- =====================================================================

-- 1. Create Rate History Table for Scenario 2 / Scenario 3 rate adjustments
CREATE TABLE IF NOT EXISTS `ast_jewel_loan_rate_history` (
    `HistoryId` int(11) NOT NULL AUTO_INCREMENT,
    `LoanDetailId` int(11) NOT NULL,
    `DNumber` varchar(20) NOT NULL,
    `ReceiptId` int(11) DEFAULT NULL,
    `InstallmentDueDate` date DEFAULT NULL,
    `OverduePeriodFrom` date DEFAULT NULL,
    `OverduePeriodTo` date DEFAULT NULL,
    `PreviousRate` decimal(5,2) NOT NULL,
    `NewRate` decimal(5,2) NOT NULL,
    `EffectiveDate` date NOT NULL,
    `Reason` varchar(255) NOT NULL,
    `CalculatedInterest` decimal(12,2) NOT NULL DEFAULT 0.00,
    `IsWaived` tinyint(1) NOT NULL DEFAULT 0,
    `WaivedAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
    `WaivedBy` int(11) DEFAULT NULL,
    `WaiverRemarks` text DEFAULT NULL,
    `AuditTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`HistoryId`),
    KEY `idx_loan_history` (`LoanDetailId`),
    KEY `idx_dnum_history` (`DNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add columns to ast_jewel_loan_receipt
ALTER TABLE `ast_jewel_loan_receipt`
    ADD COLUMN IF NOT EXISTS `RateApplied` decimal(5,2) DEFAULT NULL AFTER `InterestPaid`,
    ADD COLUMN IF NOT EXISTS `OverdueInterestRate` decimal(5,2) DEFAULT NULL AFTER `RateApplied`,
    ADD COLUMN IF NOT EXISTS `OverdueInterestPaid` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `DefaultInterestPaid`,
    ADD COLUMN IF NOT EXISTS `OverduePeriodFrom` date DEFAULT NULL AFTER `InterestPeriodTo`,
    ADD COLUMN IF NOT EXISTS `OverduePeriodTo` date DEFAULT NULL AFTER `OverduePeriodFrom`,
    ADD COLUMN IF NOT EXISTS `BreakdownJson` text DEFAULT NULL AFTER `Remarks`;
