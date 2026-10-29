<?php
require_once 'e:/xampp/htdocs/aster/application/settings/config.php';
require_once 'e:/xampp/htdocs/aster/application/settings/db.php';

echo "Running Migration: Jewel Loan Calculation Fixes & Rate History...\n";

// 1. Create ast_jewel_loan_rate_history
$sql = "CREATE TABLE IF NOT EXISTS `ast_jewel_loan_rate_history` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if ($conn->query($sql)) {
    echo " [OK] Table ast_jewel_loan_rate_history checked/created.\n";
} else {
    echo " [ERR] Table creation failed: " . $conn->error . "\n";
}

// 2. Add columns to ast_jewel_loan_receipt
$existingCols = [];
$res = $conn->query("SHOW COLUMNS FROM ast_jewel_loan_receipt");
while ($r = $res->fetch_assoc()) {
    $existingCols[strtolower($r['Field'])] = true;
}

$colsToAdd = [
    'RateApplied' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `RateApplied` decimal(5,2) DEFAULT NULL AFTER `InterestPaid`",
    'OverdueInterestRate' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `OverdueInterestRate` decimal(5,2) DEFAULT NULL AFTER `RateApplied`",
    'OverdueInterestPaid' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `OverdueInterestPaid` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `DefaultInterestPaid`",
    'OverduePeriodFrom' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `OverduePeriodFrom` date DEFAULT NULL AFTER `InterestPeriodTo`",
    'OverduePeriodTo' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `OverduePeriodTo` date DEFAULT NULL AFTER `OverduePeriodFrom`",
    'BreakdownJson' => "ALTER TABLE ast_jewel_loan_receipt ADD COLUMN `BreakdownJson` text DEFAULT NULL AFTER `Remarks`"
];

foreach ($colsToAdd as $colName => $alterSql) {
    if (!isset($existingCols[strtolower($colName)])) {
        if ($conn->query($alterSql)) {
            echo " [OK] Added column `{$colName}` to ast_jewel_loan_receipt.\n";
        } else {
            echo " [ERR] Failed adding `{$colName}`: " . $conn->error . "\n";
        }
    } else {
        echo " [SKIP] Column `{$colName}` already exists in ast_jewel_loan_receipt.\n";
    }
}

echo "Migration finished.\n";
