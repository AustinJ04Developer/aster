-- ========================================================
-- Aster Jewel Loan Master & Transaction Tables
-- Database: aster
-- Designated SQL file for Aster Bank Jewel Loan
-- ========================================================

-- Master: Jewel Loan Schemes
CREATE TABLE IF NOT EXISTS `ast_gl_schemes` (
  `LSID` int(10) NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `Dnumber` int(5) NOT NULL,
  `InterestPercentage` decimal(5,2) NOT NULL,
  `AmountFrom` int(5) NOT NULL,
  `AmountTo` int(5) NOT NULL,
  `UserId` int(3) NOT NULL,
  PRIMARY KEY (`LSID`),
  UNIQUE KEY `uk_gl_schemes_dnumber` (`Dnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Master: Appraiser Charge
CREATE TABLE IF NOT EXISTS `ast_gl_appraiser` (
  `AID` int(10) NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `AmountFrom` bigint(10) NOT NULL,
  `AmountTo` bigint(10) NOT NULL,
  `AppraiserCharge` int(5) NOT NULL,
  `UserId` int(3) NOT NULL,
  PRIMARY KEY (`AID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Master: Processing Fee
CREATE TABLE IF NOT EXISTS `ast_gl_processing_fee` (
  `PFID` int(10) NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `ProcessingFeePercentage` decimal(10,2) NOT NULL,
  `UserId` int(3) NOT NULL,
  PRIMARY KEY (`PFID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- Main Table: Customer Details
-- Primary Identifier: D Number (e.g., D-0001, D-0002)
-- Retains all original project customer & loan overview fields
-- ========================================================

CREATE TABLE IF NOT EXISTS `ast_jewel_loan_customer` (
  `DNumber` varchar(20) NOT NULL,
  `Name` varchar(150) NOT NULL,
  `FathersName` varchar(150) DEFAULT NULL,
  `Job` varchar(100) DEFAULT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Address` text NOT NULL,
  `MobileNumber` varchar(20) NOT NULL,
  `Pincode` varchar(10) DEFAULT NULL,
  `NomineeName` varchar(150) DEFAULT NULL,
  `NomineeRelation` varchar(50) DEFAULT NULL,
  `NomineeAddress` text DEFAULT NULL,
  `IfNomineeIsMinor` varchar(10) DEFAULT 'No',
  `GuardianName` varchar(150) DEFAULT NULL,
  `LoanDate` date NOT NULL,
  `MaxAmountPerGram` decimal(12,2) DEFAULT 0.00,
  `AllowedAmount` decimal(12,2) DEFAULT 0.00,
  `PacketNumber` varchar(50) DEFAULT NULL,
  `Amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ReasonForLoan` text DEFAULT NULL,
  `LoanPeriod` varchar(50) DEFAULT NULL,
  `AadhaarNumber` varchar(20) DEFAULT NULL,
  `TotalWeight` decimal(10,3) DEFAULT 0.000,
  `GoldQuality` varchar(50) DEFAULT NULL,
  `NoOfItems` int(5) DEFAULT 0,
  `UploadSign` varchar(255) DEFAULT NULL,
  `UploadAadhaar` varchar(255) DEFAULT NULL,
  `CreatedBy` int(10) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DNumber`),
  KEY `idx_cust_mobile` (`MobileNumber`),
  KEY `idx_cust_aadhaar` (`AadhaarNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- Repeatable Loan Details Subtable
-- Linked to main customer record via DNumber foreign key
-- Multiple loans can be associated with one D Number
-- ========================================================

CREATE TABLE IF NOT EXISTS `ast_jewel_loan_details` (
  `LoanDetailId` int(11) NOT NULL AUTO_INCREMENT,
  `DNumber` varchar(20) NOT NULL,
  `LoanDate` date NOT NULL,
  `InterestRate` decimal(5,2) NOT NULL,
  `AccountNumber` varchar(30) NOT NULL,
  `TotalNetWeight` decimal(10,3) NOT NULL DEFAULT 0.000,
  `AmountPerGram` decimal(12,2) NOT NULL DEFAULT 0.00,
  `TotalAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `MaxAmountPerGram` decimal(12,2) NOT NULL DEFAULT 0.00,
  `AllowedAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `LoanAmount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `AppraiserCharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ProcessingFeePercentage` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ProcessingFee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `PaymentMethod` enum('Cash','Cheque','RTGS','UPI') NOT NULL DEFAULT 'Cash',
  `PaymentDate` date DEFAULT NULL,
  `PaymentAmount` decimal(12,2) DEFAULT NULL,
  `ChequeNumber` varchar(50) DEFAULT NULL,
  `ChequeFromBank` varchar(100) DEFAULT NULL,
  `ChequeToBank` varchar(100) DEFAULT NULL,
  `RtgsNumber` varchar(50) DEFAULT NULL,
  `RtgsFromBank` varchar(100) DEFAULT NULL,
  `RtgsToBank` varchar(100) DEFAULT NULL,
  `UpiType` varchar(50) DEFAULT NULL,
  `UpiTransactionNumber` varchar(100) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LoanDetailId`),
  UNIQUE KEY `uk_account_number` (`AccountNumber`),
  KEY `idx_loan_dnumber` (`DNumber`),
  CONSTRAINT `fk_jewel_loan_customer` FOREIGN KEY (`DNumber`) 
    REFERENCES `ast_jewel_loan_customer` (`DNumber`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- Gold Items Subtable
-- Repeatable gold items associated with each loan record
-- ========================================================

CREATE TABLE IF NOT EXISTS `ast_jewel_loan_items` (
  `ItemId` int(11) NOT NULL AUTO_INCREMENT,
  `LoanDetailId` int(11) NOT NULL,
  `DNumber` varchar(20) NOT NULL,
  `SlNo` int(5) NOT NULL,
  `ItemName` varchar(150) NOT NULL,
  `NoOfItems` int(5) NOT NULL DEFAULT 1,
  `Gram` decimal(10,3) NOT NULL DEFAULT 0.000,
  `Wastage` decimal(10,3) NOT NULL DEFAULT 0.000,
  `NetWeight` decimal(10,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (`ItemId`),
  KEY `idx_item_loan_detail` (`LoanDetailId`),
  KEY `idx_item_dnumber` (`DNumber`),
  CONSTRAINT `fk_jewel_loan_detail` FOREIGN KEY (`LoanDetailId`) 
    REFERENCES `ast_jewel_loan_details` (`LoanDetailId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_jewel_loan_item_customer` FOREIGN KEY (`DNumber`) 
    REFERENCES `ast_jewel_loan_customer` (`DNumber`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
