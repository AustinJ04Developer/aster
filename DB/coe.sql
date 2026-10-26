-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 02:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coe`
--

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_additional_credentials`
--

CREATE TABLE `coe_master_additional_credentials` (
  `ACID` int(3) NOT NULL,
  `AdditionalCredentials` varchar(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coe_master_additional_credentials`
--

INSERT INTO `coe_master_additional_credentials` (`ACID`, `AdditionalCredentials`) VALUES
(1, 'jhjhhhjkkkkkkkkkkkkkhhhh'),
(3, 'hjhjhj'),
(4, 'ggggg');

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_batch`
--

CREATE TABLE `coe_master_batch` (
  `BID` int(10) NOT NULL,
  `DID` int(10) NOT NULL,
  `BatchName` varchar(50) NOT NULL,
  `FromYear` int(3) NOT NULL,
  `ToYear` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coe_master_batch`
--

INSERT INTO `coe_master_batch` (`BID`, `DID`, `BatchName`, `FromYear`, `ToYear`) VALUES
(3, 3, 'aasdfsdfsdf', 2022, 2025),
(4, 3, '2022-2024', 2022, 2024),
(5, 6, '2025-2029', 2025, 2029),
(6, 5, '2025-2030', 2025, 2030),
(7, 16, '2025-2028', 2025, 2028),
(8, 9, '2025-2030', 2025, 2030),
(9, 7, '2025-2027', 2025, 2027),
(10, 13, '2025-2030', 2025, 2030),
(12, 5, '2025-2030', 2025, 2030),
(13, 1, '2022-2025', 2022, 2025),
(14, 1, '2023-2026', 2023, 2026),
(15, 1, '2024-2027', 2024, 2027),
(16, 1, '2025-2028', 2025, 2028),
(17, 2, '2024-2025', 2024, 2025),
(18, 2, '2025-2026', 2025, 2026),
(20, 26, '2025-2026', 2025, 2026);

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_branch`
--

CREATE TABLE `coe_master_branch` (
  `BRID` int(3) NOT NULL,
  `DID` int(10) NOT NULL,
  `BID` int(10) NOT NULL,
  `BranchName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coe_master_branch`
--

INSERT INTO `coe_master_branch` (`BRID`, `DID`, `BID`, `BranchName`) VALUES
(1, 1, 13, 'tyujtffff'),
(2, 1, 15, 'hfghfgh');

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_course_type`
--

CREATE TABLE `coe_master_course_type` (
  `CTID` int(3) NOT NULL,
  `CourseType` varchar(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coe_master_course_type`
--

INSERT INTO `coe_master_course_type` (`CTID`, `CourseType`) VALUES
(2, 'kjkjkjkjkkkkkk');

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_degree`
--

CREATE TABLE `coe_master_degree` (
  `DID` int(10) NOT NULL,
  `Date` date DEFAULT NULL,
  `DegreeName` varchar(50) NOT NULL,
  `NoOfYear` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coe_master_degree`
--

INSERT INTO `coe_master_degree` (`DID`, `Date`, `DegreeName`, `NoOfYear`) VALUES
(1, '2025-06-12', 'BE', 4),
(2, '2025-06-12', 'ME', 2);

-- --------------------------------------------------------

--
-- Table structure for table `coe_master_degree_branch`
--

CREATE TABLE `coe_master_degree_branch` (
  `DBID` int(3) NOT NULL,
  `BRID` int(3) NOT NULL,
  `DegreeBranch` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `coe_master_degree_branch`
--

INSERT INTO `coe_master_degree_branch` (`DBID`, `BRID`, `DegreeBranch`) VALUES
(1, 1, 'aaaa'),
(2, 2, 'fffff'),
(5, 1, 'ddd'),
(6, 2, 'ddd'),
(7, 2, 'ddd'),
(8, 2, 'hhhhmmm');

-- --------------------------------------------------------

--
-- Table structure for table `designation`
--

CREATE TABLE `designation` (
  `DesignationId` int(3) NOT NULL,
  `Designation` varchar(50) NOT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `designation`
--

INSERT INTO `designation` (`DesignationId`, `Designation`, `Description`, `UserId`, `IsDeleted`) VALUES
(1, 'Admin', 'Admin', 1, 0),
(2, 'Controller of Examination', 'Controller of Examination', 1, 0),
(3, 'Manager', 'Manager', 1, 0),
(4, 'Operator', 'Operator  aaa', 1, 0),
(5, 'Faculty', 'Faculty', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `error_tracking`
--

CREATE TABLE `error_tracking` (
  `ErrorId` int(5) NOT NULL,
  `PageName` varchar(50) NOT NULL,
  `FunctionName` varchar(50) NOT NULL,
  `Message` varchar(250) NOT NULL,
  `Date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `error_tracking`
--

INSERT INTO `error_tracking` (`ErrorId`, `PageName`, `FunctionName`, `Message`, `Date`) VALUES
(1, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.InvoiceNo\' in \'where clause\'', '2024-11-07 00:00:00'),
(2, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.InvoiceNo\' in \'where clause\'', '2024-11-07 00:00:00'),
(3, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.InvoiceNo\' in \'where clause\'', '2024-11-07 00:00:00'),
(4, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.InvoiceNo\' in \'where clause\'', '2024-11-07 00:00:00'),
(5, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.ReceiptNo\' in \'where clause\'', '2024-11-07 00:00:00'),
(6, 'general-functions', 'PrintDunarInvoide', 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'s.ReceiptNo\' in \'where clause\'', '2024-11-07 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `invt_account_year`
--

CREATE TABLE `invt_account_year` (
  `AYID` int(3) NOT NULL,
  `AccountYear` varchar(25) NOT NULL,
  `UserId` int(3) NOT NULL,
  `IsDeleted` int(1) NOT NULL,
  `IsDefault` int(1) NOT NULL DEFAULT 0,
  `AccountStartDate` date DEFAULT NULL,
  `AccountEndDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `invt_account_year`
--

INSERT INTO `invt_account_year` (`AYID`, `AccountYear`, `UserId`, `IsDeleted`, `IsDefault`, `AccountStartDate`, `AccountEndDate`) VALUES
(1, '2023-2024', 1, 0, 0, '2023-04-01', '2024-03-31'),
(2, '2024-2025', 1, 0, 1, '2024-04-01', '2025-03-31'),
(3, '2025-2026', 1, 0, 1, '2025-04-01', '2026-03-31'),
(4, '2026-2027', 1, 0, 1, '2026-04-01', '2027-03-31');

-- --------------------------------------------------------

--
-- Table structure for table `lead_generation`
--

CREATE TABLE `lead_generation` (
  `LeadID` int(10) NOT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Nature` varchar(50) DEFAULT NULL,
  `MainLocation` varchar(100) DEFAULT NULL,
  `SubLocation` varchar(100) DEFAULT NULL,
  `Owner` varchar(50) DEFAULT NULL,
  `OwnerMobile` varchar(15) DEFAULT NULL,
  `OwnerPhone` varchar(15) DEFAULT NULL,
  `OwnerEmail` varchar(50) DEFAULT NULL,
  `ShopGST` varchar(50) DEFAULT NULL,
  `CurrentProduct` varchar(100) DEFAULT NULL,
  `Reference` varchar(100) DEFAULT NULL,
  `PriceTag` varchar(50) DEFAULT NULL,
  `PriceTagRemarks` varchar(100) DEFAULT NULL,
  `DateTime` datetime(6) NOT NULL,
  `Remarks` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `ShopName` varchar(250) DEFAULT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `OutstandingAmount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lead_generation`
--

INSERT INTO `lead_generation` (`LeadID`, `Name`, `Nature`, `MainLocation`, `SubLocation`, `Owner`, `OwnerMobile`, `OwnerPhone`, `OwnerEmail`, `ShopGST`, `CurrentProduct`, `Reference`, `PriceTag`, `PriceTagRemarks`, `DateTime`, `Remarks`, `UserId`, `ShopName`, `IsDeleted`, `OutstandingAmount`) VALUES
(1, NULL, '1', 'dddddd', 'ffffff', 'aaaaa', 'sdfsdf', '3453453', '4dddd@a.com', 'sdfsf', 'sdfsdf', 'dfsdf', '2', 'sdfsdf', '1970-01-01 05:30:00.000000', NULL, 1, 'sssss', 0, 0.00),
(2, NULL, '2', 'test', 'est', 'test', 'stest', 'test', 'test@test.com', 'test', 'test', 'test', '1', 'test', '2024-03-11 11:15:21.000000', NULL, 1, 'estt', 1, 0.00),
(3, NULL, '2', 'test', 'est', 'test', '8888888888888', '8888888', 'test@test.com', 'test', 'test', 'test', '1', 'test', '2024-03-11 11:15:21.000000', NULL, 1, 'estt', 0, 0.00),
(4, NULL, '2', 'test', 'est', 'test', '77777', '5555555', 'test@test.com', '777777', 'test', 'test', '1', 'test', '2024-03-11 11:15:21.000000', NULL, 1, 'estt', 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `logged`
--

CREATE TABLE `logged` (
  `loginid` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `logged`
--

INSERT INTO `logged` (`loginid`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `PageId` int(3) NOT NULL,
  `PageName` varchar(500) NOT NULL,
  `Link` varchar(50) NOT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL,
  `PSMID` int(3) NOT NULL,
  `IconName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `page`
--

INSERT INTO `page` (`PageId`, `PageName`, `Link`, `Description`, `UserId`, `PSMID`, `IconName`) VALUES
(8, 'Designation', 'designation.php', 'Description', 1, 1, 'glyphicon-lock'),
(11, 'Role', 'role.php', 'Role', 1, 1, 'glyphicon-user'),
(12, 'User\'s Pending List', 'user-approval-list.php', 'Users Pending List', 1, 1, 'glyphicon-th'),
(13, 'User List', 'user-list.php', 'Users list', 1, 1, 'glyphicon-th-list'),
(14, 'Setup Users Privilages', 'user-privilages.php', 'User Privilages', 1, 1, 'glyphicon-wrench'),
(56, 'Logged in Users', 'current-active-users.php', '', 1, 1, 'glyphicon-pawn'),
(111, 'Profile Approved', 'approved-profile.php', 'approved-profile.php', 1, 1, 'glyphicon-th-list'),
(112, 'Role List', 'role-list.php', '', 1, 1, 'glyphicon-th-list'),
(113, 'Blocked Users List', 'blocked-users.php', '', 1, 1, 'glyphicon-th-list'),
(114, 'Active Users List', 'active-users.php', '', 1, 1, 'glyphicon-th-list'),
(115, 'Branch Settings', 'branch-settings.php', 'Branch Settings', 1, 2, 'glyphicon glyphicon-check'),
(116, 'Share Settings', 'share-settings.php', 'Share Settings', 1, 2, 'glyphicon glyphicon-check'),
(117, 'Cash Book', 'cash-book.php', 'Day Book', 1, 44, 'glyphicon glyphicon-calendar'),
(118, 'Daily Collection Report', 'daily-collection-report.php', 'Daily collection report', 1, 44, 'glyphicon glyphicon-calendar'),
(119, 'State Settings', 'state-settings.php', 'State Settings', 1, 2, 'glyphicon glyphicon-check'),
(136, 'Financial Year Setup', 'account-year.php', '', 1, 2, 'glyphicon glyphicon-check'),
(137, 'Company Bank Account ', 'bank-account-setting.php', '', 1, 2, 'glyphicon glyphicon-check'),
(165, 'Journal Entries', 'accounts-journal-entries.php', '', 1, 24, 'fa fa-check-square'),
(166, 'Ledger Postings', 'accounts-ledger-postings.php', 'fa fa-check-square', 1, 24, 'fa fa-check-square'),
(167, 'General Ledger', 'accounts-general-ledgers.php', '', 1, 24, 'fa fa-check-square'),
(168, 'Trial Balance', 'trial-balance.php', '', 1, 24, 'fa fa-check-square'),
(169, 'P & L Statement', 'accounts-profit-loss-statement.php', '', 1, 24, 'fa fa-check-square'),
(170, 'Balance Sheet', 'accounts-balance-sheet.php', '', 1, 24, 'fa fa-check-square'),
(276, 'Unit', 'invt-unit.php', '', 1, 2, 'glyphicon glyphicon-check'),
(277, 'Category', 'invt-category.php', '', 1, 6, 'glyphicon glyphicon-check'),
(278, 'Item Type', 'invt-type.php', '', 1, 6, 'glyphicon glyphicon-check'),
(280, 'GST', 'invt-gst.php', '', 1, 2, 'glyphicon glyphicon-check'),
(281, 'Festival Offer', 'invt-common-discounts.php', '', 1, 2, 'glyphicon glyphicon-check'),
(282, 'Brand', 'invt-brand.php', '', 1, 6, ' glyphicon glyphicon-check'),
(283, 'Item', 'invt-item.php', '', 1, 6, 'glyphicon glyphicon-check'),
(284, 'Split Item', 'invt-split-item.php', '', 1, 6, 'glyphicon glyphicon glyphicon glyphicon-check'),
(2835, 'Customer List', 'invt-report-customers.php', '', 1, 44, 'glyphicon glyphicon-th-list'),
(2836, 'Category List', 'invt-report-category-list.php', '', 1, 44, 'glyphicon glyphicon-th-list'),
(2837, 'Item Type List', 'invt-report-item-type.php', '', 1, 44, 'glyphicon glyphicon-th-list'),
(2838, 'Brand List', 'invt-report-brand-list.php', '', 1, 44, 'glyphicon glyphicon-th-list'),
(2839, 'Item List', 'invt-report-item.php', '', 1, 44, 'glyphicon glyphicon-th-list'),
(2840, 'Vendors', 'invt-suppliers.php', '', 1, 3, 'glyphicon glyphicon glyphicon-user'),
(2841, 'Customer List', 'invt-report-customer-list.php', '', 1, 3, 'glyphicon glyphicon glyphicon-user'),
(2842, 'Vendor List', 'invt-report-vendor-list.php', '', 1, 3, 'glyphicon glyphicon glyphicon-user'),
(2843, 'Opening Stock', 'invt-report-opening-stock-list.php', '', 1, 6, 'glyphicon glyphicon glyphicon-th-list'),
(2844, 'Current Stock', 'invt-report-current-stock-list.php', '', 1, 6, 'glyphicon glyphicon glyphicon-th-list'),
(2845, 'Purchase Bill List', 'invt-purchase-bill-list.php', '', 1, 9, 'glyphicon glyphicon-check'),
(2846, 'New Purchase Bill', 'invt-purchase-bill.php', '', 1, 9, 'glyphicon glyphicon glyphicon-check'),
(2847, 'Edit Purchase Bill', 'invt-purchase-item-edit.php', '', 1, 9, 'glyphicon glyphicon glyphicon glyphicon-check'),
(2848, 'Sales Enry  ', 'invt-sales-entry.php', '', 1, 13, 'glyphicon glyphicon-pencil'),
(2850, 'Sales Bill List', 'invt-sales-bill-list.php', '', 1, 13, 'glyphicon glyphicon-pencil'),
(2854, 'Purchase - Report', 'invt-report-audit-purchase.php', '', 1, 14, 'glyphicon glyphicon-ok'),
(2855, 'Sales - Report', 'invt-report-audit-sales.php', '', 1, 14, 'glyphicon glyphicon-ok'),
(2856, 'Reorder Items List', 'invt-report-rol-list.php', '', 1, 6, 'glyphicon glyphicon glyphicon-th-list'),
(2857, 'Expired Item List', 'invt-report-expired-item-list.php', '', 1, 6, 'glyphicon glyphicon glyphicon-th-list'),
(2858, 'Purchase', 'invt-report-purchase.php', '', 1, 44, 'glyphicon glyphicon glyphicon-th-list'),
(2859, 'Sales', 'invt-report-sales.php', '', 1, 44, 'glyphicon glyphicon glyphicon-th-list'),
(2860, 'New Lead', 'invt-lead.php', '', 1, 12, 'glyphicon glyphicon-user'),
(2861, 'Lead', 'invt-report-lead.php', '', 1, 44, 'glyphicon glyphicon glyphicon glyphicon-th-list'),
(2862, 'Order Entry', 'invt-lead-order-entry.php', '', 1, 12, 'glyphicon glyphicon glyphicon-user'),
(2863, 'Add Sales Person', 'invt-sales-persons.php', '', 1, 3, 'glyphicon glyphicon glyphicon-user'),
(2864, 'Customer Outstanding', 'invt-report-customer-outstanding.php', '', 1, 44, 'glyphicon glyphicon glyphicon-th-list'),
(2865, 'Salesman List', 'invt-report-sales-man-list.php', '', 1, 3, 'glyphicon glyphicon glyphicon-user'),
(2866, 'Sales - Payment In', 'invt-payment-in.php', '', 1, 13, 'glyphicon glyphicon glyphicon-pencil'),
(2867, 'Sales - Cancel Bill', 'invt-sales-bill-cancel.php', '', 1, 13, 'glyphicon glyphicon glyphicon glyphicon-pencil'),
(2868, 'Report - Payment In', 'invt-report-payment-in.php', '', 1, 44, 'glyphicon glyphicon glyphicon glyphicon-pencil'),
(2869, 'Customer - Payment In', 'invt-report-payment-in.php', '', 1, 44, 'glyphicon glyphicon glyphicon-th-list'),
(2870, 'Degree', 'coe-master-degree.php', '', 1, 3, 'glyphicon glyphicon-screenshot'),
(2871, 'Batch', 'coe-master-batch.php', '', 1, 3, 'glyphicon glyphicon-screenshot'),
(2872, 'Branch', 'coe-master-branch.php', '', 1, 3, 'glyphicon glyphicon glyphicon-screenshot'),
(2873, 'Degree Branch', 'coe-master-degree-branch.php', '', 1, 3, 'glyphicon glyphicon glyphicon-screenshot'),
(2874, 'Regulations', 'coe-master-regulations.php', '', 1, 3, 'glyphicon glyphicon-screenshot'),
(2875, 'Course Type', 'coe-master-course-type.php', '', 1, 3, 'glyphicon glyphicon-screenshot'),
(2876, 'Additinal Credentials', 'coe-master-additional-credentials.php', '', 1, 3, 'glyphicon glyphicon glyphicon-screenshot');

-- --------------------------------------------------------

--
-- Table structure for table `page_sidemenu_main_heading`
--

CREATE TABLE `page_sidemenu_main_heading` (
  `PSMID` int(3) NOT NULL,
  `MainHeading` varchar(100) NOT NULL,
  `RoleId` int(3) NOT NULL,
  `UserId` int(3) NOT NULL,
  `IconName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `page_sidemenu_main_heading`
--

INSERT INTO `page_sidemenu_main_heading` (`PSMID`, `MainHeading`, `RoleId`, `UserId`, `IconName`) VALUES
(1, 'User Settings', 1, 1, 'glyphicon-object-align-vertical'),
(2, 'General Settings', 1, 1, 'glyphicon glyphicon-lock'),
(3, 'Master', 1, 1, 'glyphicon glyphicon-user'),
(4, 'Students', 1, 1, 'glyphicon glyphicon-equalizer'),
(5, 'Faculties', 1, 1, 'fa fa-inr'),
(6, 'Register No Generation', 1, 1, 'fa fa-send'),
(9, 'Students Elective', 1, 1, 'glyphicon glyphicon-object-align-vertical'),
(12, 'Pre Assessment', 1, 1, 'glyphicon glyphicon-king'),
(13, 'During Assessment', 1, 1, 'glyphicon glyphicon-shopping-cart'),
(14, 'Post Assessment', 1, 1, 'fa fa-signal'),
(20, 'Cumulative deposit', 1, 1, 'fa fa-building-o'),
(21, 'Manavatti  CD', 1, 1, 'fa fa-handshake-o'),
(22, 'Others', 1, 1, 'glyphicon glyphicon-screenshot'),
(23, 'Interest', 1, 1, 'fa fa-check-square-o'),
(24, '<span style=\"color:#FF0000;font-weight:bold;\">Accounts</span>', 1, 1, 'glyphicon glyphicon-font'),
(25, 'TR.Data From OLD Soft', 1, 1, 'glyphicon glyphicon-repeat'),
(26, 'Cumulative Deposit', 1, 1, 'glyphicon glyphicon-signal'),
(27, 'Mortgage Loan', 1, 1, 'glyphicon glyphicon-new-window'),
(28, 'Staff Loan', 1, 1, 'glyphicon glyphicon-export'),
(44, 'Reports', 1, 4, 'fa fa-edit'),
(45, 'Pharmacy Stock', 2, 1, 'glyphicon glyphicon-screenshot'),
(46, 'Pharmacy Sales', 1, 1, 'glyphicon glyphicon-transfer');

-- --------------------------------------------------------

--
-- Table structure for table `pdn_error_log`
--

CREATE TABLE `pdn_error_log` (
  `EID` bigint(10) NOT NULL,
  `Date` date DEFAULT NULL,
  `PageName` varchar(250) DEFAULT NULL,
  `FunctionName` varchar(200) DEFAULT NULL,
  `Message` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pdn_manager_incharge_setup`
--

CREATE TABLE `pdn_manager_incharge_setup` (
  `MISID` int(10) NOT NULL,
  `AssigningDate` date NOT NULL,
  `DateFrom` datetime NOT NULL,
  `DateTo` datetime NOT NULL,
  `FromRoleId` int(3) NOT NULL DEFAULT 0,
  `ToRoleId` int(3) NOT NULL DEFAULT 0,
  `ToUser` int(3) NOT NULL DEFAULT 0,
  `Remarks` varchar(1000) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `AssignedBy` int(3) NOT NULL DEFAULT 0,
  `Status` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `RoleId` int(3) NOT NULL,
  `RoleName` varchar(50) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `Date` date NOT NULL,
  `UserId` int(3) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`RoleId`, `RoleName`, `Description`, `Date`, `UserId`, `IsDeleted`) VALUES
(1, 'Admin', 'Administrator ', '0000-00-00', 1, 0),
(2, 'Controller of Examinations', 'Managing Director', '0000-00-00', 1, 0),
(3, 'Manager', 'Manager', '0000-00-00', 1, 0),
(4, 'Operator', 'Operator ', '0000-00-00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `STID` int(3) NOT NULL,
  `State` varchar(100) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`STID`, `State`, `IsDeleted`, `UserId`) VALUES
(1, 'Tamil Nadu', 0, 1),
(2, 'Kerala', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserId` int(3) NOT NULL,
  `Date` date NOT NULL,
  `Name` varchar(50) NOT NULL,
  `UserName` varchar(50) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `RoleId` int(3) NOT NULL,
  `StaffCodeNumber` varchar(25) NOT NULL,
  `DesignationId` int(3) NOT NULL,
  `DivisionId` int(3) NOT NULL,
  `EntityId` int(3) NOT NULL,
  `EmailId` varchar(50) NOT NULL,
  `MobileNumber` varchar(15) NOT NULL,
  `IsBlocked` tinyint(1) NOT NULL DEFAULT 0,
  `IsApproved` tinyint(1) NOT NULL DEFAULT 0,
  `PhoneNumber` varchar(15) NOT NULL,
  `IsFirstLogin` tinyint(1) NOT NULL DEFAULT 0,
  `ApprovedBy` int(3) NOT NULL DEFAULT 0,
  `Signature` varchar(100) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Branch` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserId`, `Date`, `Name`, `UserName`, `Password`, `RoleId`, `StaffCodeNumber`, `DesignationId`, `DivisionId`, `EntityId`, `EmailId`, `MobileNumber`, `IsBlocked`, `IsApproved`, `PhoneNumber`, `IsFirstLogin`, `ApprovedBy`, `Signature`, `IsDeleted`, `Branch`) VALUES
(1, '2016-06-16', 'Admin', 'admin', '202cb962ac59075b964b07152d234b70', 1, '1', 1, 1, 1, 'mrkesavaprfffasad@gmail.com', '454554545445', 0, 1, '9999999999994', 1, 0, 'admin.png', 0, 1),
(2, '2021-09-29', 'Controller of Examinations', 'coe', '202cb962ac59075b964b07152d234b70', 2, '111', 2, 0, 0, 'coe@marephraem.edu.in', '', 0, 1, '', 0, 1, '', 0, 1),
(4, '2023-05-03', 'Manager', 'manager', '202cb962ac59075b964b07152d234b70', 3, 'Karungal', 3, 0, 0, 'manager@abc.com', '111111111', 0, 1, '123', 0, 1, 'manager-', 0, 1),
(5, '2023-05-03', 'Data Entry Operator', 'operator', '202cb962ac59075b964b07152d234b70', 4, 'Data Entry Operator', 2, 0, 0, 'operator@abc.com', '1111111111', 0, 1, '11111111111', 0, 1, 'operator-', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_privilages`
--

CREATE TABLE `user_privilages` (
  `UpId` int(3) NOT NULL,
  `RoleId` int(3) NOT NULL,
  `Date` date NOT NULL,
  `PageId` int(3) NOT NULL,
  `DInsert` tinyint(1) NOT NULL DEFAULT 0,
  `DEdit` tinyint(1) NOT NULL DEFAULT 0,
  `DDelete` tinyint(1) NOT NULL DEFAULT 0,
  `DView` tinyint(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_privilages`
--

INSERT INTO `user_privilages` (`UpId`, `RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`) VALUES
(1, 1, '2018-11-28', 14, 1, 1, 1, 1, 1, 0),
(2, 1, '2018-11-28', 8, 1, 1, 1, 1, 1, 0),
(5, 1, '2018-11-28', 11, 0, 0, 0, 1, 1, 0),
(6, 1, '2018-11-28', 12, 0, 0, 0, 1, 1, 0),
(7, 1, '2018-11-28', 13, 0, 0, 0, 1, 1, 0),
(8, 1, '2018-11-28', 56, 0, 0, 0, 1, 1, 0),
(9, 1, '2018-11-28', 111, 0, 0, 0, 0, 1, 0),
(64, 1, '2018-12-05', 112, 0, 0, 0, 1, 1, 0),
(65, 1, '2018-12-05', 113, 0, 0, 0, 1, 1, 0),
(66, 1, '2018-12-05', 114, 0, 0, 0, 1, 1, 0),
(67, 4, '2018-12-05', 8, 0, 0, 0, 0, 1, 0),
(68, 4, '2018-12-05', 11, 0, 0, 0, 0, 1, 0),
(69, 4, '2018-12-05', 12, 0, 0, 0, 0, 1, 0),
(70, 4, '2018-12-05', 13, 0, 0, 0, 0, 1, 0),
(71, 4, '2018-12-05', 14, 0, 0, 0, 0, 1, 0),
(72, 4, '2018-12-05', 56, 0, 0, 0, 0, 1, 0),
(73, 4, '2018-12-05', 111, 0, 0, 0, 0, 1, 0),
(74, 4, '2018-12-05', 112, 0, 0, 0, 0, 1, 0),
(75, 4, '2018-12-05', 113, 0, 0, 0, 0, 1, 0),
(76, 4, '2018-12-05', 114, 0, 0, 0, 0, 1, 0),
(77, 3, '2019-01-05', 8, 0, 0, 0, 0, 1, 0),
(78, 3, '2019-01-05', 11, 0, 0, 0, 0, 1, 0),
(79, 3, '2019-01-05', 12, 0, 0, 0, 0, 1, 0),
(80, 3, '2019-01-05', 13, 0, 0, 0, 0, 1, 0),
(81, 3, '2019-01-05', 14, 0, 0, 0, 0, 1, 0),
(82, 3, '2019-01-05', 56, 0, 0, 0, 0, 1, 0),
(83, 3, '2019-01-05', 111, 0, 0, 0, 0, 1, 0),
(84, 3, '2019-01-05', 112, 0, 0, 0, 0, 1, 0),
(85, 3, '2019-01-05', 113, 0, 0, 0, 0, 1, 0),
(86, 3, '2019-01-05', 114, 0, 0, 0, 0, 1, 0),
(87, 1, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(88, 1, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(90, 3, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(91, 3, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(93, 4, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(94, 4, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(95, 4, '2019-01-23', 117, 0, 0, 0, 0, 1, 0),
(96, 4, '2019-01-23', 118, 0, 0, 0, 1, 1, 0),
(100, 3, '2019-01-23', 117, 0, 0, 0, 1, 1, 0),
(101, 3, '2019-01-23', 118, 0, 0, 0, 1, 1, 0),
(103, 1, '2019-01-23', 117, 0, 0, 0, 0, 1, 0),
(104, 1, '2019-01-23', 118, 0, 0, 0, 0, 1, 0),
(105, 1, '2019-02-05', 119, 0, 0, 0, 0, 1, 0),
(106, 3, '2019-02-05', 119, 0, 0, 0, 0, 1, 0),
(143, 1, '2019-03-11', 136, 0, 0, 0, 0, 1, 0),
(146, 3, '2019-03-11', 136, 0, 0, 0, 0, 1, 0),
(160, 4, '2019-03-14', 119, 0, 0, 0, 0, 1, 0),
(177, 4, '2019-03-14', 136, 0, 0, 0, 0, 1, 0),
(194, 2, '2019-03-16', 8, 0, 0, 0, 1, 1, 0),
(195, 2, '2019-03-16', 11, 0, 0, 0, 1, 1, 0),
(196, 2, '2019-03-16', 12, 0, 0, 0, 1, 1, 0),
(197, 2, '2019-03-16', 13, 0, 0, 0, 1, 1, 0),
(198, 2, '2019-03-16', 14, 0, 0, 0, 1, 1, 0),
(199, 2, '2019-03-16', 56, 0, 0, 0, 1, 1, 0),
(200, 2, '2019-03-16', 111, 0, 0, 0, 1, 1, 0),
(201, 2, '2019-03-16', 112, 0, 0, 0, 1, 1, 0),
(202, 2, '2019-03-16', 113, 0, 0, 0, 1, 1, 0),
(203, 2, '2019-03-16', 114, 0, 0, 0, 1, 1, 0),
(204, 2, '2019-03-16', 115, 0, 0, 0, 1, 1, 0),
(205, 2, '2019-03-16', 116, 0, 0, 0, 0, 1, 0),
(206, 2, '2019-03-16', 117, 0, 0, 0, 0, 1, 0),
(207, 2, '2019-03-16', 118, 0, 0, 0, 0, 1, 0),
(208, 2, '2019-03-16', 119, 0, 0, 0, 0, 1, 0),
(225, 2, '2019-03-16', 136, 0, 0, 0, 0, 1, 0),
(286, 1, '2019-05-06', 137, 0, 0, 0, 0, 1, 0),
(302, 3, '2019-05-13', 137, 0, 0, 0, 0, 1, 0),
(312, 1, '2019-05-18', 165, 0, 0, 0, 0, 1, 0),
(313, 1, '2019-05-18', 166, 0, 0, 0, 0, 1, 0),
(314, 1, '2019-05-18', 167, 0, 0, 0, 0, 1, 0),
(315, 1, '2019-05-18', 168, 0, 0, 0, 0, 1, 0),
(316, 1, '2019-05-18', 169, 0, 0, 0, 0, 1, 0),
(317, 1, '2019-05-18', 170, 0, 0, 0, 0, 1, 0),
(336, 2, '2019-08-22', 137, 0, 0, 0, 0, 1, 0),
(345, 2, '2019-08-22', 165, 0, 0, 0, 0, 1, 0),
(346, 2, '2019-08-22', 166, 0, 0, 0, 0, 1, 0),
(347, 2, '2019-08-22', 167, 0, 0, 0, 0, 1, 0),
(348, 2, '2019-08-22', 168, 0, 0, 0, 0, 1, 0),
(349, 2, '2019-08-22', 169, 0, 0, 0, 0, 1, 0),
(350, 2, '2019-08-22', 170, 0, 0, 0, 0, 1, 0),
(396, 3, '2019-08-22', 165, 0, 0, 0, 0, 1, 0),
(397, 3, '2019-08-22', 166, 0, 0, 0, 1, 1, 0),
(398, 3, '2019-08-22', 167, 0, 0, 0, 1, 1, 0),
(399, 3, '2019-08-22', 168, 0, 0, 0, 1, 1, 0),
(400, 3, '2019-08-22', 169, 0, 0, 0, 1, 1, 0),
(401, 3, '2019-08-22', 170, 0, 0, 0, 1, 1, 0),
(438, 5, '2019-10-23', 8, 0, 0, 0, 0, 1, 0),
(439, 5, '2019-10-23', 11, 0, 0, 0, 0, 1, 0),
(440, 5, '2019-10-23', 12, 0, 0, 0, 0, 1, 0),
(441, 5, '2019-10-23', 13, 0, 0, 0, 0, 1, 0),
(442, 5, '2019-10-23', 14, 0, 0, 0, 0, 1, 0),
(443, 5, '2019-10-23', 56, 0, 0, 0, 0, 1, 0),
(444, 5, '2019-10-23', 111, 0, 0, 0, 0, 1, 0),
(445, 5, '2019-10-23', 112, 0, 0, 0, 0, 1, 0),
(446, 5, '2019-10-23', 113, 0, 0, 0, 0, 1, 0),
(447, 5, '2019-10-23', 114, 0, 0, 0, 0, 1, 0),
(448, 5, '2019-10-23', 115, 0, 0, 0, 0, 1, 0),
(449, 5, '2019-10-23', 116, 0, 0, 0, 0, 1, 0),
(450, 5, '2019-10-23', 117, 0, 0, 0, 1, 1, 0),
(451, 5, '2019-10-23', 118, 0, 0, 0, 1, 1, 0),
(452, 5, '2019-10-23', 119, 0, 0, 0, 0, 1, 0),
(469, 5, '2019-10-23', 136, 0, 0, 0, 0, 1, 0),
(470, 5, '2019-10-23', 137, 0, 0, 0, 0, 1, 0),
(498, 5, '2019-10-23', 165, 0, 0, 0, 0, 1, 0),
(499, 5, '2019-10-23', 166, 0, 0, 0, 0, 1, 0),
(500, 5, '2019-10-23', 167, 0, 0, 0, 0, 1, 0),
(501, 5, '2019-10-23', 168, 0, 0, 0, 0, 1, 0),
(502, 5, '2019-10-23', 169, 0, 0, 0, 0, 1, 0),
(503, 5, '2019-10-23', 170, 0, 0, 0, 0, 1, 0),
(565, 4, '2019-10-23', 137, 0, 0, 0, 0, 1, 0),
(582, 4, '2019-10-23', 165, 0, 0, 0, 0, 1, 0),
(583, 4, '2019-10-23', 166, 0, 0, 0, 0, 1, 0),
(584, 4, '2019-10-23', 167, 0, 0, 0, 0, 1, 0),
(585, 4, '2019-10-23', 168, 0, 0, 0, 0, 1, 0),
(586, 4, '2019-10-23', 169, 0, 0, 0, 0, 1, 0),
(587, 4, '2019-10-23', 170, 0, 0, 0, 0, 1, 0),
(801, 8, '2020-12-28', 8, 0, 0, 0, 0, 1, 0),
(802, 8, '2020-12-28', 11, 0, 0, 0, 0, 1, 0),
(803, 8, '2020-12-28', 12, 0, 0, 0, 0, 1, 0),
(804, 8, '2020-12-28', 13, 0, 0, 0, 0, 1, 0),
(805, 8, '2020-12-28', 14, 0, 0, 0, 0, 1, 0),
(806, 8, '2020-12-28', 56, 0, 0, 0, 0, 1, 0),
(807, 8, '2020-12-28', 111, 0, 0, 0, 0, 1, 0),
(808, 8, '2020-12-28', 112, 0, 0, 0, 0, 1, 0),
(809, 8, '2020-12-28', 113, 0, 0, 0, 0, 1, 0),
(810, 8, '2020-12-28', 114, 0, 0, 0, 0, 1, 0),
(811, 8, '2020-12-28', 115, 0, 0, 0, 1, 1, 0),
(812, 8, '2020-12-28', 116, 0, 0, 0, 1, 1, 0),
(813, 8, '2020-12-28', 117, 0, 0, 0, 0, 1, 0),
(814, 8, '2020-12-28', 118, 0, 0, 0, 1, 1, 0),
(815, 8, '2020-12-28', 119, 0, 0, 0, 1, 1, 0),
(832, 8, '2020-12-28', 136, 0, 0, 0, 1, 1, 0),
(833, 8, '2020-12-28', 137, 0, 0, 0, 1, 1, 0),
(861, 8, '2020-12-28', 165, 0, 0, 0, 1, 1, 0),
(862, 8, '2020-12-28', 166, 0, 0, 0, 1, 1, 0),
(863, 8, '2020-12-28', 167, 0, 0, 0, 1, 1, 0),
(864, 8, '2020-12-28', 168, 0, 0, 0, 1, 1, 0),
(865, 8, '2020-12-28', 169, 0, 0, 0, 1, 1, 0),
(866, 8, '2020-12-28', 170, 0, 0, 0, 1, 1, 0),
(1179, 1, '2023-04-10', 276, 0, 0, 0, 0, 1, 0),
(1180, 1, '2023-04-10', 277, 0, 0, 0, 1, 1, 0),
(1181, 1, '2023-04-10', 282, 0, 0, 0, 1, 1, 0),
(1183, 1, '2023-04-11', 280, 0, 0, 0, 0, 1, 0),
(1184, 1, '2023-04-11', 281, 0, 0, 0, 0, 1, 0),
(1185, 1, '2023-04-15', 283, 0, 0, 0, 1, 1, 0),
(1186, 1, '2023-04-15', 278, 0, 0, 0, 1, 1, 0),
(1187, 1, '2023-04-19', 2835, 0, 0, 0, 0, 1, 0),
(1188, 1, '2023-04-19', 2836, 0, 0, 0, 1, 1, 0),
(1189, 1, '2023-04-19', 2837, 0, 0, 0, 1, 1, 0),
(1190, 1, '2023-04-19', 2838, 0, 0, 0, 1, 1, 0),
(1191, 1, '2023-04-19', 2839, 0, 0, 0, 1, 1, 0),
(1194, 1, '2023-04-19', 2840, 0, 0, 0, 0, 1, 0),
(1195, 1, '2023-04-19', 2841, 0, 0, 0, 0, 1, 0),
(1196, 1, '2023-04-19', 2842, 0, 0, 0, 0, 1, 0),
(1197, 1, '2023-04-23', 2843, 0, 0, 0, 1, 1, 0),
(1198, 1, '2023-04-23', 2844, 0, 0, 0, 1, 1, 0),
(1199, 1, '2023-04-24', 2845, 0, 0, 0, 1, 1, 0),
(1200, 1, '2023-05-02', 2846, 0, 0, 0, 1, 1, 0),
(1201, 2, '2023-05-03', 276, 0, 0, 0, 1, 1, 0),
(1202, 2, '2023-05-03', 277, 0, 0, 0, 0, 1, 0),
(1203, 2, '2023-05-03', 278, 0, 0, 0, 0, 1, 0),
(1205, 2, '2023-05-03', 280, 0, 0, 0, 1, 1, 0),
(1206, 2, '2023-05-03', 281, 0, 0, 0, 0, 1, 0),
(1207, 2, '2023-05-03', 282, 0, 0, 0, 0, 1, 0),
(1208, 2, '2023-05-03', 283, 0, 0, 0, 0, 1, 0),
(1209, 2, '2023-05-03', 2835, 0, 0, 0, 0, 1, 0),
(1210, 2, '2023-05-03', 2836, 0, 0, 0, 1, 1, 0),
(1211, 2, '2023-05-03', 2837, 0, 0, 0, 1, 1, 0),
(1212, 2, '2023-05-03', 2838, 0, 0, 0, 1, 1, 0),
(1213, 2, '2023-05-03', 2839, 0, 0, 0, 1, 1, 0),
(1214, 2, '2023-05-03', 2840, 0, 0, 0, 0, 1, 0),
(1215, 2, '2023-05-03', 2841, 0, 0, 0, 1, 1, 0),
(1216, 2, '2023-05-03', 2842, 0, 0, 0, 1, 1, 0),
(1217, 2, '2023-05-03', 2843, 0, 0, 0, 1, 1, 0),
(1218, 2, '2023-05-03', 2844, 0, 0, 0, 1, 1, 0),
(1219, 2, '2023-05-03', 2845, 0, 0, 0, 1, 1, 0),
(1220, 2, '2023-05-03', 2846, 0, 0, 0, 0, 1, 0),
(1232, 3, '2023-05-03', 276, 0, 0, 0, 0, 1, 0),
(1233, 3, '2023-05-03', 277, 0, 0, 0, 1, 1, 0),
(1234, 3, '2023-05-03', 278, 0, 0, 0, 1, 1, 0),
(1236, 3, '2023-05-03', 280, 0, 0, 0, 0, 1, 0),
(1237, 3, '2023-05-03', 281, 0, 0, 0, 0, 1, 0),
(1238, 3, '2023-05-03', 282, 0, 0, 0, 1, 1, 0),
(1239, 3, '2023-05-03', 283, 0, 0, 0, 1, 1, 0),
(1240, 3, '2023-05-03', 2835, 0, 0, 0, 1, 1, 0),
(1241, 3, '2023-05-03', 2836, 0, 0, 0, 1, 1, 0),
(1242, 3, '2023-05-03', 2837, 0, 0, 0, 1, 1, 0),
(1243, 3, '2023-05-03', 2838, 0, 0, 0, 1, 1, 0),
(1244, 3, '2023-05-03', 2839, 0, 0, 0, 1, 1, 0),
(1245, 3, '2023-05-03', 2840, 0, 0, 0, 1, 1, 0),
(1246, 3, '2023-05-03', 2841, 0, 0, 0, 1, 1, 0),
(1247, 3, '2023-05-03', 2842, 0, 0, 0, 1, 1, 0),
(1248, 3, '2023-05-03', 2843, 0, 0, 0, 1, 1, 0),
(1249, 3, '2023-05-03', 2844, 0, 0, 0, 1, 1, 0),
(1250, 3, '2023-05-03', 2845, 0, 0, 0, 0, 1, 0),
(1251, 3, '2023-05-03', 2846, 0, 0, 0, 0, 1, 0),
(1263, 4, '2023-05-03', 276, 0, 0, 0, 0, 1, 0),
(1264, 4, '2023-05-03', 277, 0, 0, 0, 0, 1, 0),
(1265, 4, '2023-05-03', 278, 0, 0, 0, 0, 1, 0),
(1267, 4, '2023-05-03', 280, 0, 0, 0, 0, 1, 0),
(1268, 4, '2023-05-03', 281, 0, 0, 0, 0, 1, 0),
(1269, 4, '2023-05-03', 282, 0, 0, 0, 0, 1, 0),
(1270, 4, '2023-05-03', 283, 0, 0, 0, 0, 1, 0),
(1271, 4, '2023-05-03', 2835, 0, 0, 0, 0, 1, 0),
(1272, 4, '2023-05-03', 2836, 0, 0, 0, 0, 1, 0),
(1273, 4, '2023-05-03', 2837, 0, 0, 0, 0, 1, 0),
(1274, 4, '2023-05-03', 2838, 0, 0, 0, 0, 1, 0),
(1275, 4, '2023-05-03', 2839, 0, 0, 0, 0, 1, 0),
(1276, 4, '2023-05-03', 2840, 0, 0, 0, 0, 1, 0),
(1277, 4, '2023-05-03', 2841, 1, 0, 0, 1, 1, 0),
(1278, 4, '2023-05-03', 2842, 0, 0, 0, 0, 1, 0),
(1279, 4, '2023-05-03', 2843, 0, 0, 0, 1, 1, 0),
(1280, 4, '2023-05-03', 2844, 0, 0, 0, 1, 1, 0),
(1281, 4, '2023-05-03', 2845, 0, 0, 0, 0, 1, 0),
(1282, 4, '2023-05-03', 2846, 0, 0, 0, 0, 1, 0),
(1294, 1, '2023-05-08', 2847, 0, 0, 0, 1, 1, 0),
(1295, 2, '2023-05-08', 2847, 0, 0, 0, 0, 1, 0),
(1296, 1, '2023-05-20', 2848, 0, 0, 0, 1, 1, 0),
(1297, 1, '2023-05-25', 2850, 0, 0, 0, 1, 1, 0),
(1298, 1, '2023-06-14', 284, 0, 0, 0, 1, 1, 0),
(1299, 4, '2023-06-14', 284, 0, 0, 0, 0, 1, 0),
(1300, 4, '2023-06-14', 2847, 0, 0, 0, 0, 1, 0),
(1301, 4, '2023-06-14', 2848, 0, 0, 0, 1, 1, 0),
(1302, 4, '2023-06-14', 2850, 0, 0, 0, 1, 1, 0),
(1303, 2, '2023-07-15', 284, 0, 0, 0, 0, 1, 0),
(1304, 2, '2023-07-15', 2848, 0, 0, 0, 1, 1, 0),
(1305, 2, '2023-07-15', 2850, 0, 0, 0, 1, 1, 0),
(1306, 2, '2023-07-15', 2854, 0, 0, 0, 1, 1, 0),
(1307, 2, '2023-07-15', 2855, 0, 0, 0, 1, 1, 0),
(1310, 1, '2023-07-15', 2854, 0, 0, 0, 1, 1, 0),
(1311, 1, '2023-07-15', 2855, 0, 0, 0, 1, 1, 0),
(1312, 1, '2023-07-20', 2856, 0, 0, 0, 1, 1, 0),
(1313, 1, '2023-07-20', 2857, 0, 0, 0, 1, 1, 0),
(1314, 1, '2023-07-20', 2858, 0, 0, 0, 1, 1, 0),
(1315, 1, '2023-07-20', 2859, 0, 0, 0, 1, 1, 0),
(1319, 2, '2023-07-20', 2856, 0, 0, 0, 1, 1, 0),
(1320, 2, '2023-07-20', 2857, 0, 0, 0, 1, 1, 0),
(1321, 2, '2023-07-20', 2858, 0, 0, 0, 1, 1, 0),
(1322, 2, '2023-07-20', 2859, 0, 0, 0, 1, 1, 0),
(1326, 4, '2023-07-20', 2854, 0, 0, 0, 0, 1, 0),
(1327, 4, '2023-07-20', 2855, 0, 0, 0, 0, 1, 0),
(1328, 4, '2023-07-20', 2856, 0, 0, 0, 0, 1, 0),
(1329, 4, '2023-07-20', 2857, 0, 0, 0, 0, 1, 0),
(1330, 4, '2023-07-20', 2858, 0, 0, 0, 0, 1, 0),
(1331, 4, '2023-07-20', 2859, 0, 0, 0, 1, 1, 0),
(1332, 3, '2023-07-25', 284, 0, 0, 0, 1, 1, 0),
(1333, 3, '2023-07-25', 2847, 0, 0, 0, 0, 1, 0),
(1334, 3, '2023-07-25', 2848, 0, 0, 0, 1, 1, 0),
(1335, 3, '2023-07-25', 2850, 0, 0, 0, 1, 1, 0),
(1336, 3, '2023-07-25', 2854, 0, 0, 0, 1, 1, 0),
(1337, 3, '2023-07-25', 2855, 0, 0, 0, 1, 1, 0),
(1338, 3, '2023-07-25', 2856, 0, 0, 0, 1, 1, 0),
(1339, 3, '2023-07-25', 2857, 0, 0, 0, 1, 1, 0),
(1340, 3, '2023-07-25', 2858, 0, 0, 0, 0, 1, 0),
(1341, 3, '2023-07-25', 2859, 0, 0, 0, 1, 1, 0),
(1342, 1, '2024-03-11', 2860, 0, 0, 0, 1, 1, 0),
(1343, 1, '2024-03-11', 2861, 0, 0, 0, 1, 1, 0),
(1344, 1, '2024-03-12', 2862, 0, 0, 0, 1, 1, 0),
(1345, 4, '2024-06-13', 2860, 0, 0, 0, 0, 1, 0),
(1346, 4, '2024-06-13', 2861, 0, 0, 0, 0, 1, 0),
(1347, 4, '2024-06-13', 2862, 0, 0, 0, 0, 1, 0),
(1348, 3, '2024-06-13', 2860, 0, 0, 0, 0, 1, 0),
(1349, 3, '2024-06-13', 2861, 0, 0, 0, 0, 1, 0),
(1350, 3, '2024-06-13', 2862, 0, 0, 0, 0, 1, 0),
(1351, 1, '2024-06-28', 2863, 0, 0, 0, 0, 1, 0),
(1352, 2, '2024-06-28', 2860, 0, 0, 0, 0, 1, 0),
(1353, 2, '2024-06-28', 2861, 0, 0, 0, 0, 1, 0),
(1354, 2, '2024-06-28', 2862, 0, 0, 0, 0, 1, 0),
(1355, 2, '2024-06-28', 2863, 0, 0, 0, 1, 1, 0),
(1356, 1, '2024-07-01', 2864, 0, 0, 0, 1, 1, 0),
(1357, 2, '2024-07-01', 2864, 0, 0, 0, 1, 1, 0),
(1358, 1, '2024-07-01', 2865, 0, 0, 0, 0, 1, 0),
(1359, 2, '2024-07-01', 2865, 0, 0, 0, 1, 1, 0),
(1360, 1, '2024-07-11', 2866, 0, 0, 0, 1, 1, 0),
(1361, 2, '2024-07-11', 2866, 0, 0, 0, 1, 1, 0),
(1362, 4, '2024-07-11', 2863, 0, 0, 0, 0, 1, 0),
(1363, 4, '2024-07-11', 2864, 0, 0, 0, 1, 1, 0),
(1364, 4, '2024-07-11', 2865, 0, 0, 0, 0, 1, 0),
(1365, 4, '2024-07-11', 2866, 0, 0, 0, 1, 1, 0),
(1366, 1, '2024-10-24', 2867, 0, 0, 0, 1, 1, 0),
(1367, 1, '2024-10-24', 2868, 0, 0, 0, 0, 1, 0),
(1368, 1, '2024-11-06', 2869, 0, 0, 0, 1, 1, 0),
(1369, 2, '2024-11-06', 2867, 0, 0, 0, 0, 1, 0),
(1370, 2, '2024-11-06', 2868, 0, 0, 0, 0, 1, 0),
(1371, 2, '2024-11-06', 2869, 0, 0, 0, 1, 1, 0),
(1372, 3, '2024-11-06', 2863, 0, 0, 0, 0, 1, 0),
(1373, 3, '2024-11-06', 2864, 0, 0, 0, 0, 1, 0),
(1374, 3, '2024-11-06', 2865, 0, 0, 0, 0, 1, 0),
(1375, 3, '2024-11-06', 2866, 0, 0, 0, 0, 1, 0),
(1376, 3, '2024-11-06', 2867, 0, 0, 0, 0, 1, 0),
(1377, 3, '2024-11-06', 2868, 0, 0, 0, 0, 1, 0),
(1378, 3, '2024-11-06', 2869, 0, 0, 0, 1, 1, 0),
(1379, 4, '2024-11-06', 2867, 0, 0, 0, 0, 1, 0),
(1380, 4, '2024-11-06', 2868, 0, 0, 0, 0, 1, 0),
(1381, 4, '2024-11-06', 2869, 0, 0, 0, 1, 1, 0),
(1382, 0, '2025-04-09', 8, 0, 0, 0, 0, 1, 0),
(1383, 0, '2025-04-09', 11, 0, 0, 0, 0, 1, 0),
(1384, 0, '2025-04-09', 12, 0, 0, 0, 0, 1, 0),
(1385, 0, '2025-04-09', 13, 0, 0, 0, 0, 1, 0),
(1386, 0, '2025-04-09', 14, 0, 0, 0, 0, 1, 0),
(1387, 0, '2025-04-09', 56, 0, 0, 0, 0, 1, 0),
(1388, 0, '2025-04-09', 111, 0, 0, 0, 0, 1, 0),
(1389, 0, '2025-04-09', 112, 0, 0, 0, 0, 1, 0),
(1390, 0, '2025-04-09', 113, 0, 0, 0, 0, 1, 0),
(1391, 0, '2025-04-09', 114, 0, 0, 0, 0, 1, 0),
(1392, 0, '2025-04-09', 115, 0, 0, 0, 0, 1, 0),
(1393, 0, '2025-04-09', 116, 0, 0, 0, 0, 1, 0),
(1394, 0, '2025-04-09', 117, 0, 0, 0, 0, 1, 0),
(1395, 0, '2025-04-09', 118, 0, 0, 0, 0, 1, 0),
(1396, 0, '2025-04-09', 119, 0, 0, 0, 0, 1, 0),
(1397, 0, '2025-04-09', 136, 0, 0, 0, 0, 1, 0),
(1398, 0, '2025-04-09', 137, 0, 0, 0, 0, 1, 0),
(1399, 0, '2025-04-09', 165, 0, 0, 0, 0, 1, 0),
(1400, 0, '2025-04-09', 166, 0, 0, 0, 0, 1, 0),
(1401, 0, '2025-04-09', 167, 0, 0, 0, 0, 1, 0),
(1402, 0, '2025-04-09', 168, 0, 0, 0, 0, 1, 0),
(1403, 0, '2025-04-09', 169, 0, 0, 0, 0, 1, 0),
(1404, 0, '2025-04-09', 170, 0, 0, 0, 0, 1, 0),
(1405, 0, '2025-04-09', 276, 0, 0, 0, 0, 1, 0),
(1406, 0, '2025-04-09', 277, 0, 0, 0, 0, 1, 0),
(1407, 0, '2025-04-09', 278, 0, 0, 0, 0, 1, 0),
(1409, 0, '2025-04-09', 280, 0, 0, 0, 0, 1, 0),
(1410, 0, '2025-04-09', 281, 0, 0, 0, 0, 1, 0),
(1411, 0, '2025-04-09', 282, 0, 0, 0, 0, 1, 0),
(1412, 0, '2025-04-09', 283, 0, 0, 0, 0, 1, 0),
(1413, 0, '2025-04-09', 284, 0, 0, 0, 0, 1, 0),
(1414, 0, '2025-04-09', 2835, 0, 0, 0, 0, 1, 0),
(1415, 0, '2025-04-09', 2836, 0, 0, 0, 0, 1, 0),
(1416, 0, '2025-04-09', 2837, 0, 0, 0, 0, 1, 0),
(1417, 0, '2025-04-09', 2838, 0, 0, 0, 0, 1, 0),
(1418, 0, '2025-04-09', 2839, 0, 0, 0, 0, 1, 0),
(1419, 0, '2025-04-09', 2840, 0, 0, 0, 0, 1, 0),
(1420, 0, '2025-04-09', 2841, 0, 0, 0, 0, 1, 0),
(1421, 0, '2025-04-09', 2842, 0, 0, 0, 0, 1, 0),
(1422, 0, '2025-04-09', 2843, 0, 0, 0, 0, 1, 0),
(1423, 0, '2025-04-09', 2844, 0, 0, 0, 0, 1, 0),
(1424, 0, '2025-04-09', 2845, 0, 0, 0, 0, 1, 0),
(1425, 0, '2025-04-09', 2846, 0, 0, 0, 0, 1, 0),
(1426, 0, '2025-04-09', 2847, 0, 0, 0, 0, 1, 0),
(1427, 0, '2025-04-09', 2848, 0, 0, 0, 0, 1, 0),
(1428, 0, '2025-04-09', 2850, 0, 0, 0, 0, 1, 0),
(1429, 0, '2025-04-09', 2854, 0, 0, 0, 0, 1, 0),
(1430, 0, '2025-04-09', 2855, 0, 0, 0, 0, 1, 0),
(1431, 0, '2025-04-09', 2856, 0, 0, 0, 0, 1, 0),
(1432, 0, '2025-04-09', 2857, 0, 0, 0, 0, 1, 0),
(1433, 0, '2025-04-09', 2858, 0, 0, 0, 0, 1, 0),
(1434, 0, '2025-04-09', 2859, 0, 0, 0, 0, 1, 0),
(1435, 0, '2025-04-09', 2860, 0, 0, 0, 0, 1, 0),
(1436, 0, '2025-04-09', 2861, 0, 0, 0, 0, 1, 0),
(1437, 0, '2025-04-09', 2862, 0, 0, 0, 0, 1, 0),
(1438, 0, '2025-04-09', 2863, 0, 0, 0, 0, 1, 0),
(1439, 0, '2025-04-09', 2864, 0, 0, 0, 0, 1, 0),
(1440, 0, '2025-04-09', 2865, 0, 0, 0, 0, 1, 0),
(1441, 0, '2025-04-09', 2866, 0, 0, 0, 0, 1, 0),
(1442, 0, '2025-04-09', 2867, 0, 0, 0, 0, 1, 0),
(1443, 0, '2025-04-09', 2868, 0, 0, 0, 0, 1, 0),
(1444, 0, '2025-04-09', 2869, 0, 0, 0, 0, 1, 0),
(1445, 0, '2025-04-09', 8, 0, 0, 0, 0, 1, 0),
(1446, 0, '2025-04-09', 11, 0, 0, 0, 0, 1, 0),
(1447, 0, '2025-04-09', 12, 0, 0, 0, 0, 1, 0),
(1448, 0, '2025-04-09', 13, 0, 0, 0, 0, 1, 0),
(1449, 0, '2025-04-09', 14, 0, 0, 0, 0, 1, 0),
(1450, 0, '2025-04-09', 56, 0, 0, 0, 0, 1, 0),
(1451, 0, '2025-04-09', 111, 0, 0, 0, 0, 1, 0),
(1452, 0, '2025-04-09', 112, 0, 0, 0, 0, 1, 0),
(1453, 0, '2025-04-09', 113, 0, 0, 0, 0, 1, 0),
(1454, 0, '2025-04-09', 114, 0, 0, 0, 0, 1, 0),
(1455, 0, '2025-04-09', 115, 0, 0, 0, 0, 1, 0),
(1456, 0, '2025-04-09', 116, 0, 0, 0, 0, 1, 0),
(1457, 0, '2025-04-09', 117, 0, 0, 0, 0, 1, 0),
(1458, 0, '2025-04-09', 118, 0, 0, 0, 0, 1, 0),
(1459, 0, '2025-04-09', 119, 0, 0, 0, 0, 1, 0),
(1460, 0, '2025-04-09', 136, 0, 0, 0, 0, 1, 0),
(1461, 0, '2025-04-09', 137, 0, 0, 0, 0, 1, 0),
(1462, 0, '2025-04-09', 165, 0, 0, 0, 0, 1, 0),
(1463, 0, '2025-04-09', 166, 0, 0, 0, 0, 1, 0),
(1464, 0, '2025-04-09', 167, 0, 0, 0, 0, 1, 0),
(1465, 0, '2025-04-09', 168, 0, 0, 0, 0, 1, 0),
(1466, 0, '2025-04-09', 169, 0, 0, 0, 0, 1, 0),
(1467, 0, '2025-04-09', 170, 0, 0, 0, 0, 1, 0),
(1468, 0, '2025-04-09', 276, 0, 0, 0, 0, 1, 0),
(1469, 0, '2025-04-09', 277, 0, 0, 0, 0, 1, 0),
(1470, 0, '2025-04-09', 278, 0, 0, 0, 0, 1, 0),
(1472, 0, '2025-04-09', 280, 0, 0, 0, 0, 1, 0),
(1473, 0, '2025-04-09', 281, 0, 0, 0, 0, 1, 0),
(1474, 0, '2025-04-09', 282, 0, 0, 0, 0, 1, 0),
(1475, 0, '2025-04-09', 283, 0, 0, 0, 0, 1, 0),
(1476, 0, '2025-04-09', 284, 0, 0, 0, 0, 1, 0),
(1477, 0, '2025-04-09', 2835, 0, 0, 0, 0, 1, 0),
(1478, 0, '2025-04-09', 2836, 0, 0, 0, 0, 1, 0),
(1479, 0, '2025-04-09', 2837, 0, 0, 0, 0, 1, 0),
(1480, 0, '2025-04-09', 2838, 0, 0, 0, 0, 1, 0),
(1481, 0, '2025-04-09', 2839, 0, 0, 0, 0, 1, 0),
(1482, 0, '2025-04-09', 2840, 0, 0, 0, 0, 1, 0),
(1483, 0, '2025-04-09', 2841, 0, 0, 0, 0, 1, 0),
(1484, 0, '2025-04-09', 2842, 0, 0, 0, 0, 1, 0),
(1485, 0, '2025-04-09', 2843, 0, 0, 0, 0, 1, 0),
(1486, 0, '2025-04-09', 2844, 0, 0, 0, 0, 1, 0),
(1487, 0, '2025-04-09', 2845, 0, 0, 0, 0, 1, 0),
(1488, 0, '2025-04-09', 2846, 0, 0, 0, 0, 1, 0),
(1489, 0, '2025-04-09', 2847, 0, 0, 0, 0, 1, 0),
(1490, 0, '2025-04-09', 2848, 0, 0, 0, 0, 1, 0),
(1491, 0, '2025-04-09', 2850, 0, 0, 0, 0, 1, 0),
(1492, 0, '2025-04-09', 2854, 0, 0, 0, 0, 1, 0),
(1493, 0, '2025-04-09', 2855, 0, 0, 0, 0, 1, 0),
(1494, 0, '2025-04-09', 2856, 0, 0, 0, 0, 1, 0),
(1495, 0, '2025-04-09', 2857, 0, 0, 0, 0, 1, 0),
(1496, 0, '2025-04-09', 2858, 0, 0, 0, 0, 1, 0),
(1497, 0, '2025-04-09', 2859, 0, 0, 0, 0, 1, 0),
(1498, 0, '2025-04-09', 2860, 0, 0, 0, 0, 1, 0),
(1499, 0, '2025-04-09', 2861, 0, 0, 0, 0, 1, 0),
(1500, 0, '2025-04-09', 2862, 0, 0, 0, 0, 1, 0),
(1501, 0, '2025-04-09', 2863, 0, 0, 0, 0, 1, 0),
(1502, 0, '2025-04-09', 2864, 0, 0, 0, 0, 1, 0),
(1503, 0, '2025-04-09', 2865, 0, 0, 0, 0, 1, 0),
(1504, 0, '2025-04-09', 2866, 0, 0, 0, 0, 1, 0),
(1505, 0, '2025-04-09', 2867, 0, 0, 0, 0, 1, 0),
(1506, 0, '2025-04-09', 2868, 0, 0, 0, 0, 1, 0),
(1507, 0, '2025-04-09', 2869, 0, 0, 0, 0, 1, 0),
(1508, 0, '2025-06-11', 8, 0, 0, 0, 0, 1, 0),
(1509, 0, '2025-06-11', 11, 0, 0, 0, 0, 1, 0),
(1510, 0, '2025-06-11', 12, 0, 0, 0, 0, 1, 0),
(1511, 0, '2025-06-11', 13, 0, 0, 0, 0, 1, 0),
(1512, 0, '2025-06-11', 14, 0, 0, 0, 0, 1, 0),
(1513, 0, '2025-06-11', 56, 0, 0, 0, 0, 1, 0),
(1514, 0, '2025-06-11', 111, 0, 0, 0, 0, 1, 0),
(1515, 0, '2025-06-11', 112, 0, 0, 0, 0, 1, 0),
(1516, 0, '2025-06-11', 113, 0, 0, 0, 0, 1, 0),
(1517, 0, '2025-06-11', 114, 0, 0, 0, 0, 1, 0),
(1518, 0, '2025-06-11', 115, 0, 0, 0, 0, 1, 0),
(1519, 0, '2025-06-11', 116, 0, 0, 0, 0, 1, 0),
(1520, 0, '2025-06-11', 117, 0, 0, 0, 0, 1, 0),
(1521, 0, '2025-06-11', 118, 0, 0, 0, 0, 1, 0),
(1522, 0, '2025-06-11', 119, 0, 0, 0, 0, 1, 0),
(1523, 0, '2025-06-11', 136, 0, 0, 0, 0, 1, 0),
(1524, 0, '2025-06-11', 137, 0, 0, 0, 0, 1, 0),
(1525, 0, '2025-06-11', 165, 0, 0, 0, 0, 1, 0),
(1526, 0, '2025-06-11', 166, 0, 0, 0, 0, 1, 0),
(1527, 0, '2025-06-11', 167, 0, 0, 0, 0, 1, 0),
(1528, 0, '2025-06-11', 168, 0, 0, 0, 0, 1, 0),
(1529, 0, '2025-06-11', 169, 0, 0, 0, 0, 1, 0),
(1530, 0, '2025-06-11', 170, 0, 0, 0, 0, 1, 0),
(1531, 0, '2025-06-11', 276, 0, 0, 0, 0, 1, 0),
(1532, 0, '2025-06-11', 277, 0, 0, 0, 0, 1, 0),
(1533, 0, '2025-06-11', 278, 0, 0, 0, 0, 1, 0),
(1535, 0, '2025-06-11', 280, 0, 0, 0, 0, 1, 0),
(1536, 0, '2025-06-11', 281, 0, 0, 0, 0, 1, 0),
(1537, 0, '2025-06-11', 282, 0, 0, 0, 0, 1, 0),
(1538, 0, '2025-06-11', 283, 0, 0, 0, 0, 1, 0),
(1539, 0, '2025-06-11', 284, 0, 0, 0, 0, 1, 0),
(1540, 0, '2025-06-11', 2835, 0, 0, 0, 0, 1, 0),
(1541, 0, '2025-06-11', 2836, 0, 0, 0, 0, 1, 0),
(1542, 0, '2025-06-11', 2837, 0, 0, 0, 0, 1, 0),
(1543, 0, '2025-06-11', 2838, 0, 0, 0, 0, 1, 0),
(1544, 0, '2025-06-11', 2839, 0, 0, 0, 0, 1, 0),
(1545, 0, '2025-06-11', 2840, 0, 0, 0, 0, 1, 0),
(1546, 0, '2025-06-11', 2841, 0, 0, 0, 0, 1, 0),
(1547, 0, '2025-06-11', 2842, 0, 0, 0, 0, 1, 0),
(1548, 0, '2025-06-11', 2843, 0, 0, 0, 0, 1, 0),
(1549, 0, '2025-06-11', 2844, 0, 0, 0, 0, 1, 0),
(1550, 0, '2025-06-11', 2845, 0, 0, 0, 0, 1, 0),
(1551, 0, '2025-06-11', 2846, 0, 0, 0, 0, 1, 0),
(1552, 0, '2025-06-11', 2847, 0, 0, 0, 0, 1, 0),
(1553, 0, '2025-06-11', 2848, 0, 0, 0, 0, 1, 0),
(1554, 0, '2025-06-11', 2850, 0, 0, 0, 0, 1, 0),
(1555, 0, '2025-06-11', 2854, 0, 0, 0, 0, 1, 0),
(1556, 0, '2025-06-11', 2855, 0, 0, 0, 0, 1, 0),
(1557, 0, '2025-06-11', 2856, 0, 0, 0, 0, 1, 0),
(1558, 0, '2025-06-11', 2857, 0, 0, 0, 0, 1, 0),
(1559, 0, '2025-06-11', 2858, 0, 0, 0, 0, 1, 0),
(1560, 0, '2025-06-11', 2859, 0, 0, 0, 0, 1, 0),
(1561, 0, '2025-06-11', 2860, 0, 0, 0, 0, 1, 0),
(1562, 0, '2025-06-11', 2861, 0, 0, 0, 0, 1, 0),
(1563, 0, '2025-06-11', 2862, 0, 0, 0, 0, 1, 0),
(1564, 0, '2025-06-11', 2863, 0, 0, 0, 0, 1, 0),
(1565, 0, '2025-06-11', 2864, 0, 0, 0, 0, 1, 0),
(1566, 0, '2025-06-11', 2865, 0, 0, 0, 0, 1, 0),
(1567, 0, '2025-06-11', 2866, 0, 0, 0, 0, 1, 0),
(1568, 0, '2025-06-11', 2867, 0, 0, 0, 0, 1, 0),
(1569, 0, '2025-06-11', 2868, 0, 0, 0, 0, 1, 0),
(1570, 0, '2025-06-11', 2869, 0, 0, 0, 0, 1, 0),
(1571, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1572, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1573, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1574, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1575, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1576, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1577, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1578, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1579, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1580, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1581, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1582, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1583, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1584, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1585, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1586, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1587, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1588, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1589, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1590, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1591, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1592, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1593, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1594, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1595, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1596, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1598, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1599, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1600, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1601, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1602, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1603, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1604, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1605, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1606, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1607, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1608, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1609, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1610, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1611, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1612, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1613, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1614, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1615, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1616, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1617, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1618, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1619, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1620, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(1621, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(1622, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(1623, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(1624, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(1625, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(1626, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(1627, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(1628, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(1629, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(1630, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(1631, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(1632, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(1633, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(1634, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1635, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1636, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1637, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1638, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1639, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1640, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1641, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1642, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1643, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1644, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1645, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1646, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1647, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1648, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1649, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1650, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1651, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1652, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1653, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1654, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1655, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1656, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1657, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1658, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1659, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1661, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1662, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1663, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1664, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1665, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1666, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1667, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1668, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1669, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1670, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1671, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1672, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1673, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1674, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1675, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1676, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1677, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1678, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1679, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1680, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1681, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1682, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1683, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(1684, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(1685, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(1686, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(1687, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(1688, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(1689, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(1690, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(1691, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(1692, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(1693, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(1694, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(1695, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(1696, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(1697, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1698, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1699, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1700, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1701, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1702, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1703, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1704, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1705, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1706, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1707, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1708, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1709, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1710, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1711, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1712, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1713, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1714, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1715, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1716, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1717, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1718, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1719, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1720, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1721, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1722, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1724, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1725, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1726, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1727, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1728, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1729, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1730, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1731, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1732, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1733, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1734, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1735, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1736, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1737, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1738, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1739, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1740, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1741, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1742, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1743, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1744, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1745, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1746, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(1747, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(1748, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(1749, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(1750, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(1751, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(1752, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(1753, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(1754, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(1755, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(1756, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(1757, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(1758, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(1759, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(1760, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1761, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1762, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1763, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1764, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1765, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1766, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1767, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1768, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1769, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1770, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1771, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1772, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1773, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1774, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1775, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1776, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1777, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1778, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1779, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1780, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1781, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1782, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1783, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1784, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1785, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1787, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1788, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1789, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1790, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1791, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1792, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1793, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1794, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1795, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1796, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1797, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1798, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1799, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1800, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1801, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1802, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1803, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1804, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1805, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1806, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1807, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1808, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1809, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(1810, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(1811, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(1812, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(1813, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(1814, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(1815, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(1816, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(1817, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(1818, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(1819, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(1820, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(1821, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(1822, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(1823, 0, '2025-06-12', 2870, 0, 0, 0, 0, 1, 0),
(1887, 1, '2025-06-12', 2870, 0, 0, 0, 1, 1, 0),
(1888, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1889, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1890, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1891, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1892, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1893, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1894, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1895, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1896, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1897, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1898, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1899, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1900, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1901, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1902, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1903, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1904, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1905, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1906, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1907, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1908, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1909, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1910, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1911, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1912, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1913, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1914, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1915, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1916, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1917, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1918, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1919, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1920, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1921, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1922, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1923, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1924, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1925, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1926, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1927, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1928, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1929, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1930, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1931, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1932, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1933, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1934, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1935, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1936, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(1937, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(1938, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(1939, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(1940, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(1941, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(1942, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(1943, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(1944, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(1945, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(1946, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(1947, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(1948, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(1949, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(1950, 0, '2025-06-12', 2870, 0, 0, 0, 0, 1, 0),
(1951, 0, '2025-06-12', 8, 0, 0, 0, 0, 1, 0),
(1952, 0, '2025-06-12', 11, 0, 0, 0, 0, 1, 0),
(1953, 0, '2025-06-12', 12, 0, 0, 0, 0, 1, 0),
(1954, 0, '2025-06-12', 13, 0, 0, 0, 0, 1, 0),
(1955, 0, '2025-06-12', 14, 0, 0, 0, 0, 1, 0),
(1956, 0, '2025-06-12', 56, 0, 0, 0, 0, 1, 0),
(1957, 0, '2025-06-12', 111, 0, 0, 0, 0, 1, 0),
(1958, 0, '2025-06-12', 112, 0, 0, 0, 0, 1, 0),
(1959, 0, '2025-06-12', 113, 0, 0, 0, 0, 1, 0),
(1960, 0, '2025-06-12', 114, 0, 0, 0, 0, 1, 0),
(1961, 0, '2025-06-12', 115, 0, 0, 0, 0, 1, 0),
(1962, 0, '2025-06-12', 116, 0, 0, 0, 0, 1, 0),
(1963, 0, '2025-06-12', 117, 0, 0, 0, 0, 1, 0),
(1964, 0, '2025-06-12', 118, 0, 0, 0, 0, 1, 0),
(1965, 0, '2025-06-12', 119, 0, 0, 0, 0, 1, 0),
(1966, 0, '2025-06-12', 136, 0, 0, 0, 0, 1, 0),
(1967, 0, '2025-06-12', 137, 0, 0, 0, 0, 1, 0),
(1968, 0, '2025-06-12', 165, 0, 0, 0, 0, 1, 0),
(1969, 0, '2025-06-12', 166, 0, 0, 0, 0, 1, 0),
(1970, 0, '2025-06-12', 167, 0, 0, 0, 0, 1, 0),
(1971, 0, '2025-06-12', 168, 0, 0, 0, 0, 1, 0),
(1972, 0, '2025-06-12', 169, 0, 0, 0, 0, 1, 0),
(1973, 0, '2025-06-12', 170, 0, 0, 0, 0, 1, 0),
(1974, 0, '2025-06-12', 276, 0, 0, 0, 0, 1, 0),
(1975, 0, '2025-06-12', 277, 0, 0, 0, 0, 1, 0),
(1976, 0, '2025-06-12', 278, 0, 0, 0, 0, 1, 0),
(1977, 0, '2025-06-12', 280, 0, 0, 0, 0, 1, 0),
(1978, 0, '2025-06-12', 281, 0, 0, 0, 0, 1, 0),
(1979, 0, '2025-06-12', 282, 0, 0, 0, 0, 1, 0),
(1980, 0, '2025-06-12', 283, 0, 0, 0, 0, 1, 0),
(1981, 0, '2025-06-12', 284, 0, 0, 0, 0, 1, 0),
(1982, 0, '2025-06-12', 2835, 0, 0, 0, 0, 1, 0),
(1983, 0, '2025-06-12', 2836, 0, 0, 0, 0, 1, 0),
(1984, 0, '2025-06-12', 2837, 0, 0, 0, 0, 1, 0),
(1985, 0, '2025-06-12', 2838, 0, 0, 0, 0, 1, 0),
(1986, 0, '2025-06-12', 2839, 0, 0, 0, 0, 1, 0),
(1987, 0, '2025-06-12', 2840, 0, 0, 0, 0, 1, 0),
(1988, 0, '2025-06-12', 2841, 0, 0, 0, 0, 1, 0),
(1989, 0, '2025-06-12', 2842, 0, 0, 0, 0, 1, 0),
(1990, 0, '2025-06-12', 2843, 0, 0, 0, 0, 1, 0),
(1991, 0, '2025-06-12', 2844, 0, 0, 0, 0, 1, 0),
(1992, 0, '2025-06-12', 2845, 0, 0, 0, 0, 1, 0),
(1993, 0, '2025-06-12', 2846, 0, 0, 0, 0, 1, 0),
(1994, 0, '2025-06-12', 2847, 0, 0, 0, 0, 1, 0),
(1995, 0, '2025-06-12', 2848, 0, 0, 0, 0, 1, 0),
(1996, 0, '2025-06-12', 2850, 0, 0, 0, 0, 1, 0),
(1997, 0, '2025-06-12', 2854, 0, 0, 0, 0, 1, 0),
(1998, 0, '2025-06-12', 2855, 0, 0, 0, 0, 1, 0),
(1999, 0, '2025-06-12', 2856, 0, 0, 0, 0, 1, 0),
(2000, 0, '2025-06-12', 2857, 0, 0, 0, 0, 1, 0),
(2001, 0, '2025-06-12', 2858, 0, 0, 0, 0, 1, 0),
(2002, 0, '2025-06-12', 2859, 0, 0, 0, 0, 1, 0),
(2003, 0, '2025-06-12', 2860, 0, 0, 0, 0, 1, 0),
(2004, 0, '2025-06-12', 2861, 0, 0, 0, 0, 1, 0),
(2005, 0, '2025-06-12', 2862, 0, 0, 0, 0, 1, 0),
(2006, 0, '2025-06-12', 2863, 0, 0, 0, 0, 1, 0),
(2007, 0, '2025-06-12', 2864, 0, 0, 0, 0, 1, 0),
(2008, 0, '2025-06-12', 2865, 0, 0, 0, 0, 1, 0),
(2009, 0, '2025-06-12', 2866, 0, 0, 0, 0, 1, 0),
(2010, 0, '2025-06-12', 2867, 0, 0, 0, 0, 1, 0),
(2011, 0, '2025-06-12', 2868, 0, 0, 0, 0, 1, 0),
(2012, 0, '2025-06-12', 2869, 0, 0, 0, 0, 1, 0),
(2013, 0, '2025-06-12', 2870, 0, 0, 0, 0, 1, 0),
(2014, 0, '2025-06-12', 2871, 0, 0, 0, 0, 1, 0),
(2078, 1, '2025-06-12', 2871, 0, 0, 0, 1, 1, 0),
(2079, 0, '2025-06-24', 8, 0, 0, 0, 0, 1, 0),
(2080, 0, '2025-06-24', 11, 0, 0, 0, 0, 1, 0),
(2081, 0, '2025-06-24', 12, 0, 0, 0, 0, 1, 0),
(2082, 0, '2025-06-24', 13, 0, 0, 0, 0, 1, 0),
(2083, 0, '2025-06-24', 14, 0, 0, 0, 0, 1, 0),
(2084, 0, '2025-06-24', 56, 0, 0, 0, 0, 1, 0),
(2085, 0, '2025-06-24', 111, 0, 0, 0, 0, 1, 0),
(2086, 0, '2025-06-24', 112, 0, 0, 0, 0, 1, 0),
(2087, 0, '2025-06-24', 113, 0, 0, 0, 0, 1, 0),
(2088, 0, '2025-06-24', 114, 0, 0, 0, 0, 1, 0),
(2089, 0, '2025-06-24', 115, 0, 0, 0, 0, 1, 0),
(2090, 0, '2025-06-24', 116, 0, 0, 0, 0, 1, 0),
(2091, 0, '2025-06-24', 117, 0, 0, 0, 0, 1, 0),
(2092, 0, '2025-06-24', 118, 0, 0, 0, 0, 1, 0),
(2093, 0, '2025-06-24', 119, 0, 0, 0, 0, 1, 0),
(2094, 0, '2025-06-24', 136, 0, 0, 0, 0, 1, 0),
(2095, 0, '2025-06-24', 137, 0, 0, 0, 0, 1, 0),
(2096, 0, '2025-06-24', 165, 0, 0, 0, 0, 1, 0),
(2097, 0, '2025-06-24', 166, 0, 0, 0, 0, 1, 0),
(2098, 0, '2025-06-24', 167, 0, 0, 0, 0, 1, 0),
(2099, 0, '2025-06-24', 168, 0, 0, 0, 0, 1, 0),
(2100, 0, '2025-06-24', 169, 0, 0, 0, 0, 1, 0),
(2101, 0, '2025-06-24', 170, 0, 0, 0, 0, 1, 0),
(2102, 0, '2025-06-24', 276, 0, 0, 0, 0, 1, 0),
(2103, 0, '2025-06-24', 277, 0, 0, 0, 0, 1, 0),
(2104, 0, '2025-06-24', 278, 0, 0, 0, 0, 1, 0),
(2105, 0, '2025-06-24', 280, 0, 0, 0, 0, 1, 0),
(2106, 0, '2025-06-24', 281, 0, 0, 0, 0, 1, 0),
(2107, 0, '2025-06-24', 282, 0, 0, 0, 0, 1, 0),
(2108, 0, '2025-06-24', 283, 0, 0, 0, 0, 1, 0),
(2109, 0, '2025-06-24', 284, 0, 0, 0, 0, 1, 0),
(2110, 0, '2025-06-24', 2835, 0, 0, 0, 0, 1, 0),
(2111, 0, '2025-06-24', 2836, 0, 0, 0, 0, 1, 0),
(2112, 0, '2025-06-24', 2837, 0, 0, 0, 0, 1, 0),
(2113, 0, '2025-06-24', 2838, 0, 0, 0, 0, 1, 0),
(2114, 0, '2025-06-24', 2839, 0, 0, 0, 0, 1, 0),
(2115, 0, '2025-06-24', 2840, 0, 0, 0, 0, 1, 0),
(2116, 0, '2025-06-24', 2841, 0, 0, 0, 0, 1, 0),
(2117, 0, '2025-06-24', 2842, 0, 0, 0, 0, 1, 0),
(2118, 0, '2025-06-24', 2843, 0, 0, 0, 0, 1, 0),
(2119, 0, '2025-06-24', 2844, 0, 0, 0, 0, 1, 0),
(2120, 0, '2025-06-24', 2845, 0, 0, 0, 0, 1, 0),
(2121, 0, '2025-06-24', 2846, 0, 0, 0, 0, 1, 0),
(2122, 0, '2025-06-24', 2847, 0, 0, 0, 0, 1, 0),
(2123, 0, '2025-06-24', 2848, 0, 0, 0, 0, 1, 0),
(2124, 0, '2025-06-24', 2850, 0, 0, 0, 0, 1, 0),
(2125, 0, '2025-06-24', 2854, 0, 0, 0, 0, 1, 0),
(2126, 0, '2025-06-24', 2855, 0, 0, 0, 0, 1, 0),
(2127, 0, '2025-06-24', 2856, 0, 0, 0, 0, 1, 0),
(2128, 0, '2025-06-24', 2857, 0, 0, 0, 0, 1, 0),
(2129, 0, '2025-06-24', 2858, 0, 0, 0, 0, 1, 0),
(2130, 0, '2025-06-24', 2859, 0, 0, 0, 0, 1, 0),
(2131, 0, '2025-06-24', 2860, 0, 0, 0, 0, 1, 0),
(2132, 0, '2025-06-24', 2861, 0, 0, 0, 0, 1, 0),
(2133, 0, '2025-06-24', 2862, 0, 0, 0, 0, 1, 0),
(2134, 0, '2025-06-24', 2863, 0, 0, 0, 0, 1, 0),
(2135, 0, '2025-06-24', 2864, 0, 0, 0, 0, 1, 0),
(2136, 0, '2025-06-24', 2865, 0, 0, 0, 0, 1, 0),
(2137, 0, '2025-06-24', 2866, 0, 0, 0, 0, 1, 0),
(2138, 0, '2025-06-24', 2867, 0, 0, 0, 0, 1, 0),
(2139, 0, '2025-06-24', 2868, 0, 0, 0, 0, 1, 0),
(2140, 0, '2025-06-24', 2869, 0, 0, 0, 0, 1, 0),
(2141, 0, '2025-06-24', 2870, 0, 0, 0, 0, 1, 0),
(2142, 0, '2025-06-24', 2871, 0, 0, 0, 0, 1, 0),
(2143, 0, '2025-06-24', 2872, 0, 0, 0, 0, 1, 0),
(2206, 1, '2025-06-24', 2872, 0, 0, 0, 1, 1, 0),
(2207, 0, '2025-06-24', 8, 0, 0, 0, 0, 1, 0),
(2208, 0, '2025-06-24', 11, 0, 0, 0, 0, 1, 0),
(2209, 0, '2025-06-24', 12, 0, 0, 0, 0, 1, 0),
(2210, 0, '2025-06-24', 13, 0, 0, 0, 0, 1, 0),
(2211, 0, '2025-06-24', 14, 0, 0, 0, 0, 1, 0),
(2212, 0, '2025-06-24', 56, 0, 0, 0, 0, 1, 0),
(2213, 0, '2025-06-24', 111, 0, 0, 0, 0, 1, 0),
(2214, 0, '2025-06-24', 112, 0, 0, 0, 0, 1, 0),
(2215, 0, '2025-06-24', 113, 0, 0, 0, 0, 1, 0),
(2216, 0, '2025-06-24', 114, 0, 0, 0, 0, 1, 0),
(2217, 0, '2025-06-24', 115, 0, 0, 0, 0, 1, 0),
(2218, 0, '2025-06-24', 116, 0, 0, 0, 0, 1, 0),
(2219, 0, '2025-06-24', 117, 0, 0, 0, 0, 1, 0),
(2220, 0, '2025-06-24', 118, 0, 0, 0, 0, 1, 0),
(2221, 0, '2025-06-24', 119, 0, 0, 0, 0, 1, 0),
(2222, 0, '2025-06-24', 136, 0, 0, 0, 0, 1, 0),
(2223, 0, '2025-06-24', 137, 0, 0, 0, 0, 1, 0),
(2224, 0, '2025-06-24', 165, 0, 0, 0, 0, 1, 0),
(2225, 0, '2025-06-24', 166, 0, 0, 0, 0, 1, 0),
(2226, 0, '2025-06-24', 167, 0, 0, 0, 0, 1, 0),
(2227, 0, '2025-06-24', 168, 0, 0, 0, 0, 1, 0),
(2228, 0, '2025-06-24', 169, 0, 0, 0, 0, 1, 0),
(2229, 0, '2025-06-24', 170, 0, 0, 0, 0, 1, 0),
(2230, 0, '2025-06-24', 276, 0, 0, 0, 0, 1, 0),
(2231, 0, '2025-06-24', 277, 0, 0, 0, 0, 1, 0),
(2232, 0, '2025-06-24', 278, 0, 0, 0, 0, 1, 0),
(2233, 0, '2025-06-24', 280, 0, 0, 0, 0, 1, 0),
(2234, 0, '2025-06-24', 281, 0, 0, 0, 0, 1, 0),
(2235, 0, '2025-06-24', 282, 0, 0, 0, 0, 1, 0),
(2236, 0, '2025-06-24', 283, 0, 0, 0, 0, 1, 0),
(2237, 0, '2025-06-24', 284, 0, 0, 0, 0, 1, 0),
(2238, 0, '2025-06-24', 2835, 0, 0, 0, 0, 1, 0),
(2239, 0, '2025-06-24', 2836, 0, 0, 0, 0, 1, 0),
(2240, 0, '2025-06-24', 2837, 0, 0, 0, 0, 1, 0),
(2241, 0, '2025-06-24', 2838, 0, 0, 0, 0, 1, 0),
(2242, 0, '2025-06-24', 2839, 0, 0, 0, 0, 1, 0),
(2243, 0, '2025-06-24', 2840, 0, 0, 0, 0, 1, 0),
(2244, 0, '2025-06-24', 2841, 0, 0, 0, 0, 1, 0),
(2245, 0, '2025-06-24', 2842, 0, 0, 0, 0, 1, 0),
(2246, 0, '2025-06-24', 2843, 0, 0, 0, 0, 1, 0),
(2247, 0, '2025-06-24', 2844, 0, 0, 0, 0, 1, 0),
(2248, 0, '2025-06-24', 2845, 0, 0, 0, 0, 1, 0),
(2249, 0, '2025-06-24', 2846, 0, 0, 0, 0, 1, 0),
(2250, 0, '2025-06-24', 2847, 0, 0, 0, 0, 1, 0),
(2251, 0, '2025-06-24', 2848, 0, 0, 0, 0, 1, 0),
(2252, 0, '2025-06-24', 2850, 0, 0, 0, 0, 1, 0),
(2253, 0, '2025-06-24', 2854, 0, 0, 0, 0, 1, 0),
(2254, 0, '2025-06-24', 2855, 0, 0, 0, 0, 1, 0),
(2255, 0, '2025-06-24', 2856, 0, 0, 0, 0, 1, 0),
(2256, 0, '2025-06-24', 2857, 0, 0, 0, 0, 1, 0),
(2257, 0, '2025-06-24', 2858, 0, 0, 0, 0, 1, 0),
(2258, 0, '2025-06-24', 2859, 0, 0, 0, 0, 1, 0),
(2259, 0, '2025-06-24', 2860, 0, 0, 0, 0, 1, 0),
(2260, 0, '2025-06-24', 2861, 0, 0, 0, 0, 1, 0),
(2261, 0, '2025-06-24', 2862, 0, 0, 0, 0, 1, 0),
(2262, 0, '2025-06-24', 2863, 0, 0, 0, 0, 1, 0),
(2263, 0, '2025-06-24', 2864, 0, 0, 0, 0, 1, 0),
(2264, 0, '2025-06-24', 2865, 0, 0, 0, 0, 1, 0),
(2265, 0, '2025-06-24', 2866, 0, 0, 0, 0, 1, 0),
(2266, 0, '2025-06-24', 2867, 0, 0, 0, 0, 1, 0),
(2267, 0, '2025-06-24', 2868, 0, 0, 0, 0, 1, 0),
(2268, 0, '2025-06-24', 2869, 0, 0, 0, 0, 1, 0),
(2269, 0, '2025-06-24', 2870, 0, 0, 0, 0, 1, 0),
(2270, 0, '2025-06-24', 2871, 0, 0, 0, 0, 1, 0),
(2271, 0, '2025-06-24', 2872, 0, 0, 0, 0, 1, 0),
(2272, 0, '2025-06-24', 2873, 0, 0, 0, 0, 1, 0),
(2334, 1, '2025-06-24', 2873, 0, 0, 0, 1, 1, 0),
(2335, 0, '2025-07-31', 8, 0, 0, 0, 0, 1, 0),
(2336, 0, '2025-07-31', 11, 0, 0, 0, 0, 1, 0),
(2337, 0, '2025-07-31', 12, 0, 0, 0, 0, 1, 0),
(2338, 0, '2025-07-31', 13, 0, 0, 0, 0, 1, 0),
(2339, 0, '2025-07-31', 14, 0, 0, 0, 0, 1, 0),
(2340, 0, '2025-07-31', 56, 0, 0, 0, 0, 1, 0),
(2341, 0, '2025-07-31', 111, 0, 0, 0, 0, 1, 0),
(2342, 0, '2025-07-31', 112, 0, 0, 0, 0, 1, 0),
(2343, 0, '2025-07-31', 113, 0, 0, 0, 0, 1, 0),
(2344, 0, '2025-07-31', 114, 0, 0, 0, 0, 1, 0),
(2345, 0, '2025-07-31', 115, 0, 0, 0, 0, 1, 0),
(2346, 0, '2025-07-31', 116, 0, 0, 0, 0, 1, 0),
(2347, 0, '2025-07-31', 117, 0, 0, 0, 0, 1, 0),
(2348, 0, '2025-07-31', 118, 0, 0, 0, 0, 1, 0),
(2349, 0, '2025-07-31', 119, 0, 0, 0, 0, 1, 0),
(2350, 0, '2025-07-31', 136, 0, 0, 0, 0, 1, 0),
(2351, 0, '2025-07-31', 137, 0, 0, 0, 0, 1, 0),
(2352, 0, '2025-07-31', 165, 0, 0, 0, 0, 1, 0),
(2353, 0, '2025-07-31', 166, 0, 0, 0, 0, 1, 0),
(2354, 0, '2025-07-31', 167, 0, 0, 0, 0, 1, 0),
(2355, 0, '2025-07-31', 168, 0, 0, 0, 0, 1, 0),
(2356, 0, '2025-07-31', 169, 0, 0, 0, 0, 1, 0),
(2357, 0, '2025-07-31', 170, 0, 0, 0, 0, 1, 0),
(2358, 0, '2025-07-31', 276, 0, 0, 0, 0, 1, 0),
(2359, 0, '2025-07-31', 277, 0, 0, 0, 0, 1, 0),
(2360, 0, '2025-07-31', 278, 0, 0, 0, 0, 1, 0),
(2361, 0, '2025-07-31', 280, 0, 0, 0, 0, 1, 0),
(2362, 0, '2025-07-31', 281, 0, 0, 0, 0, 1, 0),
(2363, 0, '2025-07-31', 282, 0, 0, 0, 0, 1, 0),
(2364, 0, '2025-07-31', 283, 0, 0, 0, 0, 1, 0),
(2365, 0, '2025-07-31', 284, 0, 0, 0, 0, 1, 0),
(2366, 0, '2025-07-31', 2835, 0, 0, 0, 0, 1, 0),
(2367, 0, '2025-07-31', 2836, 0, 0, 0, 0, 1, 0),
(2368, 0, '2025-07-31', 2837, 0, 0, 0, 0, 1, 0),
(2369, 0, '2025-07-31', 2838, 0, 0, 0, 0, 1, 0),
(2370, 0, '2025-07-31', 2839, 0, 0, 0, 0, 1, 0),
(2371, 0, '2025-07-31', 2840, 0, 0, 0, 0, 1, 0),
(2372, 0, '2025-07-31', 2841, 0, 0, 0, 0, 1, 0),
(2373, 0, '2025-07-31', 2842, 0, 0, 0, 0, 1, 0),
(2374, 0, '2025-07-31', 2843, 0, 0, 0, 0, 1, 0),
(2375, 0, '2025-07-31', 2844, 0, 0, 0, 0, 1, 0),
(2376, 0, '2025-07-31', 2845, 0, 0, 0, 0, 1, 0),
(2377, 0, '2025-07-31', 2846, 0, 0, 0, 0, 1, 0),
(2378, 0, '2025-07-31', 2847, 0, 0, 0, 0, 1, 0),
(2379, 0, '2025-07-31', 2848, 0, 0, 0, 0, 1, 0),
(2380, 0, '2025-07-31', 2850, 0, 0, 0, 0, 1, 0),
(2381, 0, '2025-07-31', 2854, 0, 0, 0, 0, 1, 0),
(2382, 0, '2025-07-31', 2855, 0, 0, 0, 0, 1, 0),
(2383, 0, '2025-07-31', 2856, 0, 0, 0, 0, 1, 0),
(2384, 0, '2025-07-31', 2857, 0, 0, 0, 0, 1, 0),
(2385, 0, '2025-07-31', 2858, 0, 0, 0, 0, 1, 0),
(2386, 0, '2025-07-31', 2859, 0, 0, 0, 0, 1, 0),
(2387, 0, '2025-07-31', 2860, 0, 0, 0, 0, 1, 0),
(2388, 0, '2025-07-31', 2861, 0, 0, 0, 0, 1, 0),
(2389, 0, '2025-07-31', 2862, 0, 0, 0, 0, 1, 0),
(2390, 0, '2025-07-31', 2863, 0, 0, 0, 0, 1, 0),
(2391, 0, '2025-07-31', 2864, 0, 0, 0, 0, 1, 0),
(2392, 0, '2025-07-31', 2865, 0, 0, 0, 0, 1, 0),
(2393, 0, '2025-07-31', 2866, 0, 0, 0, 0, 1, 0),
(2394, 0, '2025-07-31', 2867, 0, 0, 0, 0, 1, 0),
(2395, 0, '2025-07-31', 2868, 0, 0, 0, 0, 1, 0),
(2396, 0, '2025-07-31', 2869, 0, 0, 0, 0, 1, 0),
(2397, 0, '2025-07-31', 2870, 0, 0, 0, 0, 1, 0),
(2398, 0, '2025-07-31', 2871, 0, 0, 0, 0, 1, 0),
(2399, 0, '2025-07-31', 2872, 0, 0, 0, 0, 1, 0),
(2400, 0, '2025-07-31', 2873, 0, 0, 0, 0, 1, 0),
(2401, 0, '2025-07-31', 2874, 0, 0, 0, 0, 1, 0),
(2402, 0, '2025-07-31', 2875, 0, 0, 0, 0, 1, 0),
(2462, 1, '2025-07-31', 2874, 0, 0, 0, 1, 1, 0),
(2463, 1, '2025-07-31', 2875, 0, 0, 0, 1, 1, 0),
(2465, 0, '2025-08-01', 8, 0, 0, 0, 0, 1, 0),
(2466, 0, '2025-08-01', 11, 0, 0, 0, 0, 1, 0),
(2467, 0, '2025-08-01', 12, 0, 0, 0, 0, 1, 0),
(2468, 0, '2025-08-01', 13, 0, 0, 0, 0, 1, 0),
(2469, 0, '2025-08-01', 14, 0, 0, 0, 0, 1, 0),
(2470, 0, '2025-08-01', 56, 0, 0, 0, 0, 1, 0),
(2471, 0, '2025-08-01', 111, 0, 0, 0, 0, 1, 0),
(2472, 0, '2025-08-01', 112, 0, 0, 0, 0, 1, 0),
(2473, 0, '2025-08-01', 113, 0, 0, 0, 0, 1, 0),
(2474, 0, '2025-08-01', 114, 0, 0, 0, 0, 1, 0),
(2475, 0, '2025-08-01', 115, 0, 0, 0, 0, 1, 0),
(2476, 0, '2025-08-01', 116, 0, 0, 0, 0, 1, 0),
(2477, 0, '2025-08-01', 117, 0, 0, 0, 0, 1, 0),
(2478, 0, '2025-08-01', 118, 0, 0, 0, 0, 1, 0),
(2479, 0, '2025-08-01', 119, 0, 0, 0, 0, 1, 0),
(2480, 0, '2025-08-01', 136, 0, 0, 0, 0, 1, 0),
(2481, 0, '2025-08-01', 137, 0, 0, 0, 0, 1, 0),
(2482, 0, '2025-08-01', 165, 0, 0, 0, 0, 1, 0);
INSERT INTO `user_privilages` (`UpId`, `RoleId`, `Date`, `PageId`, `DInsert`, `DEdit`, `DDelete`, `DView`, `UserId`, `IsDeleted`) VALUES
(2483, 0, '2025-08-01', 166, 0, 0, 0, 0, 1, 0),
(2484, 0, '2025-08-01', 167, 0, 0, 0, 0, 1, 0),
(2485, 0, '2025-08-01', 168, 0, 0, 0, 0, 1, 0),
(2486, 0, '2025-08-01', 169, 0, 0, 0, 0, 1, 0),
(2487, 0, '2025-08-01', 170, 0, 0, 0, 0, 1, 0),
(2488, 0, '2025-08-01', 276, 0, 0, 0, 0, 1, 0),
(2489, 0, '2025-08-01', 277, 0, 0, 0, 0, 1, 0),
(2490, 0, '2025-08-01', 278, 0, 0, 0, 0, 1, 0),
(2491, 0, '2025-08-01', 280, 0, 0, 0, 0, 1, 0),
(2492, 0, '2025-08-01', 281, 0, 0, 0, 0, 1, 0),
(2493, 0, '2025-08-01', 282, 0, 0, 0, 0, 1, 0),
(2494, 0, '2025-08-01', 283, 0, 0, 0, 0, 1, 0),
(2495, 0, '2025-08-01', 284, 0, 0, 0, 0, 1, 0),
(2496, 0, '2025-08-01', 2835, 0, 0, 0, 0, 1, 0),
(2497, 0, '2025-08-01', 2836, 0, 0, 0, 0, 1, 0),
(2498, 0, '2025-08-01', 2837, 0, 0, 0, 0, 1, 0),
(2499, 0, '2025-08-01', 2838, 0, 0, 0, 0, 1, 0),
(2500, 0, '2025-08-01', 2839, 0, 0, 0, 0, 1, 0),
(2501, 0, '2025-08-01', 2840, 0, 0, 0, 0, 1, 0),
(2502, 0, '2025-08-01', 2841, 0, 0, 0, 0, 1, 0),
(2503, 0, '2025-08-01', 2842, 0, 0, 0, 0, 1, 0),
(2504, 0, '2025-08-01', 2843, 0, 0, 0, 0, 1, 0),
(2505, 0, '2025-08-01', 2844, 0, 0, 0, 0, 1, 0),
(2506, 0, '2025-08-01', 2845, 0, 0, 0, 0, 1, 0),
(2507, 0, '2025-08-01', 2846, 0, 0, 0, 0, 1, 0),
(2508, 0, '2025-08-01', 2847, 0, 0, 0, 0, 1, 0),
(2509, 0, '2025-08-01', 2848, 0, 0, 0, 0, 1, 0),
(2510, 0, '2025-08-01', 2850, 0, 0, 0, 0, 1, 0),
(2511, 0, '2025-08-01', 2854, 0, 0, 0, 0, 1, 0),
(2512, 0, '2025-08-01', 2855, 0, 0, 0, 0, 1, 0),
(2513, 0, '2025-08-01', 2856, 0, 0, 0, 0, 1, 0),
(2514, 0, '2025-08-01', 2857, 0, 0, 0, 0, 1, 0),
(2515, 0, '2025-08-01', 2858, 0, 0, 0, 0, 1, 0),
(2516, 0, '2025-08-01', 2859, 0, 0, 0, 0, 1, 0),
(2517, 0, '2025-08-01', 2860, 0, 0, 0, 0, 1, 0),
(2518, 0, '2025-08-01', 2861, 0, 0, 0, 0, 1, 0),
(2519, 0, '2025-08-01', 2862, 0, 0, 0, 0, 1, 0),
(2520, 0, '2025-08-01', 2863, 0, 0, 0, 0, 1, 0),
(2521, 0, '2025-08-01', 2864, 0, 0, 0, 0, 1, 0),
(2522, 0, '2025-08-01', 2865, 0, 0, 0, 0, 1, 0),
(2523, 0, '2025-08-01', 2866, 0, 0, 0, 0, 1, 0),
(2524, 0, '2025-08-01', 2867, 0, 0, 0, 0, 1, 0),
(2525, 0, '2025-08-01', 2868, 0, 0, 0, 0, 1, 0),
(2526, 0, '2025-08-01', 2869, 0, 0, 0, 0, 1, 0),
(2527, 0, '2025-08-01', 2870, 0, 0, 0, 0, 1, 0),
(2528, 0, '2025-08-01', 2871, 0, 0, 0, 0, 1, 0),
(2529, 0, '2025-08-01', 2872, 0, 0, 0, 0, 1, 0),
(2530, 0, '2025-08-01', 2873, 0, 0, 0, 0, 1, 0),
(2531, 0, '2025-08-01', 2874, 0, 0, 0, 0, 1, 0),
(2532, 0, '2025-08-01', 2875, 0, 0, 0, 0, 1, 0),
(2533, 0, '2025-08-01', 2876, 0, 0, 0, 0, 1, 0),
(2592, 1, '2025-08-01', 2876, 0, 0, 0, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_privilages_temp`
--

CREATE TABLE `user_privilages_temp` (
  `UpId` int(3) NOT NULL,
  `RoleId` int(3) NOT NULL,
  `Date` date NOT NULL,
  `PageId` int(3) NOT NULL,
  `DInsert` tinyint(1) NOT NULL DEFAULT 0,
  `DEdit` tinyint(1) NOT NULL DEFAULT 0,
  `DDelete` tinyint(1) NOT NULL DEFAULT 0,
  `DView` tinyint(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coe_master_additional_credentials`
--
ALTER TABLE `coe_master_additional_credentials`
  ADD PRIMARY KEY (`ACID`);

--
-- Indexes for table `coe_master_batch`
--
ALTER TABLE `coe_master_batch`
  ADD PRIMARY KEY (`BID`);

--
-- Indexes for table `coe_master_branch`
--
ALTER TABLE `coe_master_branch`
  ADD PRIMARY KEY (`BRID`);

--
-- Indexes for table `coe_master_course_type`
--
ALTER TABLE `coe_master_course_type`
  ADD PRIMARY KEY (`CTID`);

--
-- Indexes for table `coe_master_degree`
--
ALTER TABLE `coe_master_degree`
  ADD PRIMARY KEY (`DID`);

--
-- Indexes for table `coe_master_degree_branch`
--
ALTER TABLE `coe_master_degree_branch`
  ADD PRIMARY KEY (`DBID`);

--
-- Indexes for table `designation`
--
ALTER TABLE `designation`
  ADD PRIMARY KEY (`DesignationId`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `error_tracking`
--
ALTER TABLE `error_tracking`
  ADD PRIMARY KEY (`ErrorId`);

--
-- Indexes for table `invt_account_year`
--
ALTER TABLE `invt_account_year`
  ADD PRIMARY KEY (`AYID`);

--
-- Indexes for table `lead_generation`
--
ALTER TABLE `lead_generation`
  ADD PRIMARY KEY (`LeadID`);

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`PageId`),
  ADD KEY `fk_PageUser` (`UserId`),
  ADD KEY `fk_page_page_sidemenu` (`PSMID`);

--
-- Indexes for table `page_sidemenu_main_heading`
--
ALTER TABLE `page_sidemenu_main_heading`
  ADD PRIMARY KEY (`PSMID`),
  ADD KEY `fk_page_sidemenu_role` (`RoleId`);

--
-- Indexes for table `pdn_error_log`
--
ALTER TABLE `pdn_error_log`
  ADD PRIMARY KEY (`EID`);

--
-- Indexes for table `pdn_manager_incharge_setup`
--
ALTER TABLE `pdn_manager_incharge_setup`
  ADD PRIMARY KEY (`MISID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`RoleId`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`STID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserId`),
  ADD KEY `user_ibfk_1` (`RoleId`),
  ADD KEY `fk_UserDesignation` (`DesignationId`);

--
-- Indexes for table `user_privilages`
--
ALTER TABLE `user_privilages`
  ADD PRIMARY KEY (`UpId`),
  ADD KEY `fk_user_privilages_userrole` (`RoleId`),
  ADD KEY `fk_PrivilageUser` (`UserId`),
  ADD KEY `fk_user_privilages_page_privilages` (`PageId`);

--
-- Indexes for table `user_privilages_temp`
--
ALTER TABLE `user_privilages_temp`
  ADD PRIMARY KEY (`UpId`),
  ADD KEY `fk_user_privilages_userrole` (`RoleId`),
  ADD KEY `fk_user_page__privilages` (`PageId`),
  ADD KEY `fk_PrivilageUser` (`UserId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coe_master_additional_credentials`
--
ALTER TABLE `coe_master_additional_credentials`
  MODIFY `ACID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coe_master_batch`
--
ALTER TABLE `coe_master_batch`
  MODIFY `BID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `coe_master_branch`
--
ALTER TABLE `coe_master_branch`
  MODIFY `BRID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coe_master_course_type`
--
ALTER TABLE `coe_master_course_type`
  MODIFY `CTID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coe_master_degree`
--
ALTER TABLE `coe_master_degree`
  MODIFY `DID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `coe_master_degree_branch`
--
ALTER TABLE `coe_master_degree_branch`
  MODIFY `DBID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `designation`
--
ALTER TABLE `designation`
  MODIFY `DesignationId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `error_tracking`
--
ALTER TABLE `error_tracking`
  MODIFY `ErrorId` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invt_account_year`
--
ALTER TABLE `invt_account_year`
  MODIFY `AYID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lead_generation`
--
ALTER TABLE `lead_generation`
  MODIFY `LeadID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `page`
--
ALTER TABLE `page`
  MODIFY `PageId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2877;

--
-- AUTO_INCREMENT for table `page_sidemenu_main_heading`
--
ALTER TABLE `page_sidemenu_main_heading`
  MODIFY `PSMID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pdn_error_log`
--
ALTER TABLE `pdn_error_log`
  MODIFY `EID` bigint(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pdn_manager_incharge_setup`
--
ALTER TABLE `pdn_manager_incharge_setup`
  MODIFY `MISID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `RoleId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `STID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_privilages`
--
ALTER TABLE `user_privilages`
  MODIFY `UpId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2593;

--
-- AUTO_INCREMENT for table `user_privilages_temp`
--
ALTER TABLE `user_privilages_temp`
  MODIFY `UpId` int(3) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `designation`
--
ALTER TABLE `designation`
  ADD CONSTRAINT `designation_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `user` (`UserId`);

--
-- Constraints for table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `fk_PageUser` FOREIGN KEY (`UserId`) REFERENCES `user` (`UserId`),
  ADD CONSTRAINT `fk_page_page_sidemenu` FOREIGN KEY (`PSMID`) REFERENCES `page_sidemenu_main_heading` (`PSMID`) ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_UserDesignation` FOREIGN KEY (`DesignationId`) REFERENCES `designation` (`DesignationId`),
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`RoleId`) REFERENCES `role` (`RoleId`) ON UPDATE CASCADE;

--
-- Constraints for table `user_privilages`
--
ALTER TABLE `user_privilages`
  ADD CONSTRAINT `fk_PrivilageUser` FOREIGN KEY (`UserId`) REFERENCES `user` (`UserId`),
  ADD CONSTRAINT `fk_user_privilages_page_privilages` FOREIGN KEY (`PageId`) REFERENCES `page` (`PageId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
