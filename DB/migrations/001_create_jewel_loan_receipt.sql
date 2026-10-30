-- ========================================================
-- Migration: Add ast_jewel_loan_receipt table
-- Supports: LOAN_CREATION, INTEREST_PAYMENT, JEWEL_RELEASE
-- Database: aster
-- ========================================================

USE `aster`;

CREATE TABLE IF NOT EXISTS `ast_jewel_loan_receipt` (
  `ReceiptId` int(11) NOT NULL AUTO_INCREMENT,
  `ReceiptNo` varchar(50) NOT NULL,
  `ReceiptDate` date NOT NULL,
  `LoanDetailId` int(11) NOT NULL,
  `AccountNumber` varchar(30) NOT NULL,
  `DNumber` varchar(20) NOT NULL,
  `TransactionType` enum('LOAN_CREATION','INTEREST_PAYMENT','JEWEL_RELEASE') NOT NULL,
  `PaymentMethod` enum('Cash','Cheque','RTGS','UPI') NOT NULL DEFAULT 'Cash',
  `PrincipalPaid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `InterestPaid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `AppraiserCharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ProcessingFee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `TotalPaid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `BalanceLoanAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `InterestMonths` decimal(5,2) DEFAULT 0.00,
  `InterestPeriodFrom` date DEFAULT NULL,
  `InterestPeriodTo` date DEFAULT NULL,
  `ChequeNumber` varchar(50) DEFAULT NULL,
  `ChequeFromBank` varchar(100) DEFAULT NULL,
  `ChequeToBank` varchar(100) DEFAULT NULL,
  `RtgsNumber` varchar(50) DEFAULT NULL,
  `RtgsFromBank` varchar(100) DEFAULT NULL,
  `RtgsToBank` varchar(100) DEFAULT NULL,
  `UpiType` varchar(50) DEFAULT NULL,
  `UpiTransactionNumber` varchar(100) DEFAULT NULL,
  `UpiToBank` varchar(100) DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `CreatedBy` int(11) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ReceiptId`),
  UNIQUE KEY `uk_receipt_no` (`ReceiptNo`),
  KEY `idx_receipt_acc` (`AccountNumber`),
  KEY `idx_receipt_dnum` (`DNumber`),
  KEY `idx_receipt_loandetail` (`LoanDetailId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Loan Creation receipt for existing loan record if not exists
INSERT INTO `ast_jewel_loan_receipt` (
  `ReceiptNo`, `ReceiptDate`, `LoanDetailId`, `AccountNumber`, `DNumber`,
  `TransactionType`, `PaymentMethod`, `PrincipalPaid`, `InterestPaid`,
  `AppraiserCharge`, `ProcessingFee`, `TotalPaid`, `BalanceLoanAmount`,
  `ChequeNumber`, `ChequeFromBank`, `ChequeToBank`,
  `RtgsNumber`, `RtgsFromBank`, `RtgsToBank`,
  `UpiType`, `UpiTransactionNumber`, `UpiToBank`,
  `Remarks`, `CreatedBy`
)
SELECT 
  CONCAT('JLR-', DATE_FORMAT(d.LoanDate, '%Y'), '-0001'),
  d.LoanDate,
  d.LoanDetailId,
  d.AccountNumber,
  d.DNumber,
  'LOAN_CREATION',
  d.PaymentMethod,
  d.LoanAmount,
  0.00,
  d.AppraiserCharge,
  d.ProcessingFee,
  d.LoanAmount,
  d.LoanAmount,
  d.ChequeNumber,
  d.ChequeFromBank,
  d.ChequeToBank,
  d.RtgsNumber,
  d.RtgsFromBank,
  d.RtgsToBank,
  d.UpiType,
  d.UpiTransactionNumber,
  d.UpiToBank,
  'Initial Loan Disbursement Receipt',
  1
FROM `ast_jewel_loan_details` d
WHERE NOT EXISTS (
  SELECT 1 FROM `ast_jewel_loan_receipt` r WHERE r.LoanDetailId = d.LoanDetailId AND r.TransactionType = 'LOAN_CREATION'
)
LIMIT 1;
