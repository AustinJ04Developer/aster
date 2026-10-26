-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2023 at 09:12 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ns-mart`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `designation`
--

INSERT INTO `designation` (`DesignationId`, `Designation`, `Description`, `UserId`, `IsDeleted`) VALUES
(1, 'Admin', 'Admin', 1, 0),
(2, 'Managing Director', 'Managing Director', 1, 0);

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `invt_account_year`
--

INSERT INTO `invt_account_year` (`AYID`, `AccountYear`, `UserId`, `IsDeleted`, `IsDefault`, `AccountStartDate`, `AccountEndDate`) VALUES
(1, '2023-2024', 1, 0, 1, '2023-04-01', '2024-03-31');

-- --------------------------------------------------------

--
-- Table structure for table `invt_branch`
--

CREATE TABLE `invt_branch` (
  `BID` int(10) NOT NULL,
  `BranchID` int(3) NOT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `invt_branch`
--

INSERT INTO `invt_branch` (`BID`, `BranchID`, `Branch`, `IsDeleted`, `UserId`) VALUES
(1, 1, 'Mangalakuntu', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `invt_brand`
--

CREATE TABLE `invt_brand` (
  `BrandId` int(10) NOT NULL,
  `Brand` varchar(100) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL,
  `CategoryId` int(10) NOT NULL DEFAULT 0,
  `ITypeId` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_category`
--

CREATE TABLE `invt_category` (
  `CategoryId` int(10) NOT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_common_discounts`
--

CREATE TABLE `invt_common_discounts` (
  `CDId` int(10) NOT NULL,
  `FromDate` date DEFAULT NULL,
  `ToDate` date DEFAULT NULL,
  `Festival` varchar(100) DEFAULT NULL,
  `IsDefault` int(1) DEFAULT 0,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) DEFAULT 0,
  `IsDeleted` int(1) DEFAULT 0,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_customers`
--

CREATE TABLE `invt_customers` (
  `CustomerId` int(10) NOT NULL,
  `CustomerType` int(1) NOT NULL DEFAULT 0,
  `PrimaryContact` varchar(100) DEFAULT NULL,
  `CompanyName` varchar(100) DEFAULT NULL,
  `CustomerEmail` varchar(100) DEFAULT NULL,
  `CustomerPhone` varchar(15) DEFAULT NULL,
  `CustomerMobile` varchar(15) DEFAULT NULL,
  `Website` varchar(100) DEFAULT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `CustomerGST` varchar(50) DEFAULT NULL,
  `TaxPreference` int(1) NOT NULL DEFAULT 0,
  `OutstandingAmt` decimal(15,2) NOT NULL DEFAULT 0.00,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `CredatedDate` date DEFAULT NULL,
  `CreatedBy` int(3) NOT NULL DEFAULT 0,
  `Remarks` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_gst`
--

CREATE TABLE `invt_gst` (
  `GSTId` int(10) NOT NULL,
  `GST` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_item_details`
--

CREATE TABLE `invt_item_details` (
  `IDID` int(10) NOT NULL,
  `ICode` int(11) NOT NULL,
  `Date` date NOT NULL,
  `BranchId` int(10) NOT NULL DEFAULT 0,
  `GST` decimal(5,2) NOT NULL,
  `PurchasePrice` decimal(15,2) NOT NULL,
  `MRP` decimal(15,2) NOT NULL,
  `RetailPrice` decimal(15,2) NOT NULL,
  `WholeSalePrice` decimal(15,2) NOT NULL,
  `MinQty` int(3) NOT NULL DEFAULT 0,
  `DiscountType` int(1) NOT NULL DEFAULT 0,
  `DiscountAmount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Qty` int(10) NOT NULL DEFAULT 0,
  `BatchNumber` varchar(50) DEFAULT NULL,
  `ExpiryDate` date DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `IsOpeningStock` int(1) NOT NULL DEFAULT 0,
  `AddedPersToRetail` decimal(5,2) NOT NULL DEFAULT 0.00,
  `AddedPersToWholeSale` decimal(5,2) NOT NULL DEFAULT 0.00,
  `HSNCode` varchar(25) DEFAULT NULL,
  `DealersMinQty` int(3) NOT NULL DEFAULT 0,
  `AddedPersToDealers` decimal(5,2) NOT NULL DEFAULT 0.00,
  `DealersPrice` decimal(15,2) NOT NULL DEFAULT 0.00,
  `PDID` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_item_master`
--

CREATE TABLE `invt_item_master` (
  `ICode` int(10) NOT NULL,
  `Date` date DEFAULT NULL,
  `CategoryId` int(10) NOT NULL,
  `ITypeId` int(10) NOT NULL,
  `BrandId` int(10) NOT NULL,
  `UnitId` int(10) NOT NULL,
  `ItemCode` varchar(15) DEFAULT NULL,
  `ItemName` varchar(200) DEFAULT NULL,
  `IsReturnable` int(1) NOT NULL DEFAULT 0,
  `ItemStatus` int(1) NOT NULL DEFAULT 1,
  `Remarks` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `ROL` int(5) NOT NULL DEFAULT 0,
  `DiscountMethod` int(1) NOT NULL DEFAULT 0,
  `Discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `BranchID` int(3) NOT NULL DEFAULT 0,
  `RetailPrice` decimal(15,2) NOT NULL DEFAULT 0.00,
  `WholeSalePrice` decimal(15,2) NOT NULL DEFAULT 0.00,
  `MinQty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `AddedPersToRetail` decimal(5,2) NOT NULL DEFAULT 0.00,
  `AddedPersToWholeSale` decimal(5,2) NOT NULL DEFAULT 0.00,
  `HSNCode` varchar(25) DEFAULT NULL,
  `DealersMinQty` int(3) NOT NULL DEFAULT 0,
  `AddedPersToDealers` decimal(5,2) NOT NULL DEFAULT 0.00,
  `DealersPrice` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_item_type`
--

CREATE TABLE `invt_item_type` (
  `ITypeId` int(10) NOT NULL,
  `ItemType` varchar(100) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL,
  `CategoryId` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_purchase_details`
--

CREATE TABLE `invt_purchase_details` (
  `PDID` int(11) NOT NULL,
  `PMID` int(10) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL,
  `ICode` int(10) NOT NULL DEFAULT 0,
  `Quantity` decimal(10,2) NOT NULL,
  `Price` decimal(15,2) NOT NULL,
  `Total` decimal(15,2) NOT NULL,
  `DiscountPercentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `TaxPercentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Tax` decimal(5,2) NOT NULL DEFAULT 0.00,
  `Amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_purchase_master`
--

CREATE TABLE `invt_purchase_master` (
  `PMID` int(10) NOT NULL,
  `VendorsId` int(10) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL,
  `SlNo` int(10) NOT NULL DEFAULT 0,
  `PurchaseBillNo` varchar(25) NOT NULL DEFAULT '0',
  `TotalAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `PaidAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `PaymentType` int(3) NOT NULL DEFAULT 0,
  `BalanceAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `BranchId` int(3) NOT NULL DEFAULT 0,
  `Remarks` varchar(500) DEFAULT NULL,
  `Reference` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_stock`
--

CREATE TABLE `invt_stock` (
  `SID` int(10) NOT NULL,
  `ICode` int(10) NOT NULL,
  `BranchId` int(10) NOT NULL,
  `Qty` int(10) NOT NULL,
  `ROL` int(5) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_transaction`
--

CREATE TABLE `invt_transaction` (
  `TID` int(11) NOT NULL,
  `BranchId` int(10) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL,
  `ICode` int(10) NOT NULL DEFAULT 0,
  `Amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `VoucherNo` varchar(50) DEFAULT NULL,
  `AYID` int(3) NOT NULL DEFAULT 0,
  `ACode` int(3) NOT NULL DEFAULT 0,
  `ASubCode` int(3) NOT NULL DEFAULT 0,
  `TransType` int(3) NOT NULL DEFAULT 0,
  `TransMethod` int(3) NOT NULL DEFAULT 0,
  `IsCancelled` int(1) NOT NULL DEFAULT 0,
  `Remarks` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `IDID` int(10) NOT NULL DEFAULT 0,
  `PMID` int(10) NOT NULL DEFAULT 0,
  `PDID` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_unit`
--

CREATE TABLE `invt_unit` (
  `UnitId` int(10) NOT NULL,
  `Unit` varchar(100) DEFAULT NULL,
  `UnitShortName` varchar(10) DEFAULT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_vendors`
--

CREATE TABLE `invt_vendors` (
  `VendorsId` int(10) NOT NULL,
  `PrimaryContact` varchar(100) DEFAULT NULL,
  `CompanyName` varchar(100) DEFAULT NULL,
  `VendorEmail` varchar(100) DEFAULT NULL,
  `VendorPhone` varchar(15) DEFAULT NULL,
  `VendorMobile` varchar(15) DEFAULT NULL,
  `Website` varchar(100) DEFAULT NULL,
  `Address` varchar(500) DEFAULT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `CredatedDate` date DEFAULT NULL,
  `CreatedBy` int(3) NOT NULL DEFAULT 0,
  `Remarks` varchar(500) DEFAULT NULL,
  `VendorGST` int(3) NOT NULL DEFAULT 0,
  `VendorBankDetails` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invt_vendor_payment`
--

CREATE TABLE `invt_vendor_payment` (
  `VPID` int(10) NOT NULL,
  `VendorsId` int(3) NOT NULL DEFAULT 0,
  `AmountPaid` decimal(15,2) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL DEFAULT 0,
  `PurchaseAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `logged`
--

CREATE TABLE `logged` (
  `loginid` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `logged`
--

INSERT INTO `logged` (`loginid`) VALUES
(1),
(5);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
(279, 'Customers', 'invt-customers.php', '', 1, 3, 'glyphicon glyphicon-user'),
(280, 'GST', 'invt-gst.php', '', 1, 2, 'glyphicon glyphicon-check'),
(281, 'Festival Offer', 'invt-common-discounts.php', '', 1, 2, 'glyphicon glyphicon-check'),
(282, 'Brand', 'invt-brand.php', '', 1, 6, ' glyphicon glyphicon-check'),
(283, 'Item', 'invt-item.php', '', 1, 6, 'glyphicon glyphicon-check'),
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
(2847, 'Edit Purchase Bill', 'invt-purchase-bill-edit.php', '', 1, 9, 'glyphicon glyphicon glyphicon glyphicon-check');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `page_sidemenu_main_heading`
--

INSERT INTO `page_sidemenu_main_heading` (`PSMID`, `MainHeading`, `RoleId`, `UserId`, `IconName`) VALUES
(1, 'User Settings', 1, 1, 'glyphicon-object-align-vertical'),
(2, 'General Settings', 1, 1, 'glyphicon glyphicon-lock'),
(3, 'Customers & Vendors', 1, 1, 'glyphicon glyphicon-user'),
(4, 'Brand', 1, 1, 'glyphicon glyphicon-equalizer'),
(5, 'Category', 1, 1, 'fa fa-inr'),
(6, 'Item', 1, 1, 'fa fa-send'),
(9, 'Purchase', 1, 1, 'glyphicon glyphicon-object-align-vertical'),
(10, 'Duplicate Bill', 1, 1, 'glyphicon glyphicon-file'),
(11, 'Recurring Deposit Loan', 1, 1, 'fa fa-signal'),
(12, 'Fixed Deposit Loan', 1, 1, 'fa fa-certificate'),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`RoleId`, `RoleName`, `Description`, `Date`, `UserId`, `IsDeleted`) VALUES
(1, 'Admin', 'Administrator', '2016-02-26', 0, 0),
(2, 'Managing Director', 'Managing Director', '2021-09-29', 1, 0),
(3, 'Manager', '', '2023-05-01', 1, 0),
(4, 'Operator', '', '2023-05-01', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `STID` int(3) NOT NULL,
  `State` varchar(100) NOT NULL,
  `IsDeleted` int(1) NOT NULL DEFAULT 0,
  `UserId` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserId`, `Date`, `Name`, `UserName`, `Password`, `RoleId`, `StaffCodeNumber`, `DesignationId`, `DivisionId`, `EntityId`, `EmailId`, `MobileNumber`, `IsBlocked`, `IsApproved`, `PhoneNumber`, `IsFirstLogin`, `ApprovedBy`, `Signature`, `IsDeleted`, `Branch`) VALUES
(1, '2016-06-16', 'Admin', 'admin', '202cb962ac59075b964b07152d234b70', 1, '1', 1, 1, 1, 'mrkesavaprfffasad@gmail.com', '454554545445', 0, 1, '9999999999994', 1, 0, 'admin.png', 0, 1),
(2, '2021-09-29', 'Managing Director', 'director', '202cb962ac59075b964b07152d234b70', 2, 'Robin Hospital', 2, 0, 0, 'robin@gmail.com', '', 0, 1, '', 0, 1, 'reeba-', 0, 1),
(3, '2023-04-10', 'tsdfasd', 'asdf', '202cb962ac59075b964b07152d234b70', 2, 'asdfasdf', 2, 0, 0, 'test@test.com', '34543534', 0, 1, '345345345', 0, 1, 'asdf-', 0, 1),
(4, '2023-05-03', 'Manager', 'manager', '202cb962ac59075b964b07152d234b70', 3, 'Karungal', 2, 0, 0, 'manager@abc.com', '111111111', 0, 1, '123', 0, 1, 'manager-', 0, 1),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
(77, 3, '2019-01-05', 8, 1, 1, 1, 1, 1, 0),
(78, 3, '2019-01-05', 11, 0, 1, 0, 1, 1, 0),
(79, 3, '2019-01-05', 12, 0, 0, 0, 1, 1, 0),
(80, 3, '2019-01-05', 13, 0, 0, 0, 1, 1, 0),
(81, 3, '2019-01-05', 14, 0, 0, 0, 0, 1, 0),
(82, 3, '2019-01-05', 56, 0, 0, 0, 1, 1, 0),
(83, 3, '2019-01-05', 111, 1, 1, 0, 1, 1, 0),
(84, 3, '2019-01-05', 112, 0, 0, 0, 1, 1, 0),
(85, 3, '2019-01-05', 113, 0, 0, 0, 1, 1, 0),
(86, 3, '2019-01-05', 114, 0, 0, 0, 1, 1, 0),
(87, 1, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(88, 1, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(90, 3, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(91, 3, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(93, 4, '2019-01-23', 115, 0, 0, 0, 0, 1, 0),
(94, 4, '2019-01-23', 116, 0, 0, 0, 0, 1, 0),
(95, 4, '2019-01-23', 117, 0, 0, 0, 0, 1, 0),
(96, 4, '2019-01-23', 118, 0, 0, 0, 0, 1, 0),
(100, 3, '2019-01-23', 117, 0, 0, 0, 0, 1, 0),
(101, 3, '2019-01-23', 118, 0, 0, 0, 0, 1, 0),
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
(1179, 1, '2023-04-10', 276, 0, 0, 0, 1, 1, 0),
(1180, 1, '2023-04-10', 277, 0, 0, 0, 1, 1, 0),
(1181, 1, '2023-04-10', 282, 0, 0, 0, 1, 1, 0),
(1182, 1, '2023-04-11', 279, 0, 0, 0, 1, 1, 0),
(1183, 1, '2023-04-11', 280, 0, 0, 0, 1, 1, 0),
(1184, 1, '2023-04-11', 281, 0, 0, 0, 1, 1, 0),
(1185, 1, '2023-04-15', 283, 0, 0, 0, 1, 1, 0),
(1186, 1, '2023-04-15', 278, 0, 0, 0, 1, 1, 0),
(1187, 1, '2023-04-19', 2835, 0, 0, 0, 0, 1, 0),
(1188, 1, '2023-04-19', 2836, 0, 0, 0, 1, 1, 0),
(1189, 1, '2023-04-19', 2837, 0, 0, 0, 1, 1, 0),
(1190, 1, '2023-04-19', 2838, 0, 0, 0, 1, 1, 0),
(1191, 1, '2023-04-19', 2839, 0, 0, 0, 1, 1, 0),
(1194, 1, '2023-04-19', 2840, 0, 0, 0, 1, 1, 0),
(1195, 1, '2023-04-19', 2841, 0, 0, 0, 1, 1, 0),
(1196, 1, '2023-04-19', 2842, 0, 0, 0, 1, 1, 0),
(1197, 1, '2023-04-23', 2843, 0, 0, 0, 1, 1, 0),
(1198, 1, '2023-04-23', 2844, 0, 0, 0, 1, 1, 0),
(1199, 1, '2023-04-24', 2845, 0, 0, 0, 1, 1, 0),
(1200, 1, '2023-05-02', 2846, 0, 0, 0, 1, 1, 0),
(1201, 2, '2023-05-03', 276, 0, 0, 0, 1, 1, 0),
(1202, 2, '2023-05-03', 277, 0, 0, 0, 0, 1, 0),
(1203, 2, '2023-05-03', 278, 0, 0, 0, 0, 1, 0),
(1204, 2, '2023-05-03', 279, 0, 0, 0, 0, 1, 0),
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
(1235, 3, '2023-05-03', 279, 0, 0, 0, 1, 1, 0),
(1236, 3, '2023-05-03', 280, 0, 0, 0, 0, 1, 0),
(1237, 3, '2023-05-03', 281, 0, 0, 0, 0, 1, 0),
(1238, 3, '2023-05-03', 282, 0, 0, 0, 1, 1, 0),
(1239, 3, '2023-05-03', 283, 0, 0, 0, 1, 1, 0),
(1240, 3, '2023-05-03', 2835, 0, 0, 0, 0, 1, 0),
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
(1264, 4, '2023-05-03', 277, 0, 0, 0, 1, 1, 0),
(1265, 4, '2023-05-03', 278, 0, 0, 0, 1, 1, 0),
(1266, 4, '2023-05-03', 279, 0, 0, 0, 0, 1, 0),
(1267, 4, '2023-05-03', 280, 0, 0, 0, 0, 1, 0),
(1268, 4, '2023-05-03', 281, 0, 0, 0, 0, 1, 0),
(1269, 4, '2023-05-03', 282, 0, 0, 0, 1, 1, 0),
(1270, 4, '2023-05-03', 283, 0, 0, 0, 1, 1, 0),
(1271, 4, '2023-05-03', 2835, 0, 0, 0, 0, 1, 0),
(1272, 4, '2023-05-03', 2836, 0, 0, 0, 1, 1, 0),
(1273, 4, '2023-05-03', 2837, 0, 0, 0, 1, 1, 0),
(1274, 4, '2023-05-03', 2838, 0, 0, 0, 1, 1, 0),
(1275, 4, '2023-05-03', 2839, 0, 0, 0, 1, 1, 0),
(1276, 4, '2023-05-03', 2840, 0, 0, 0, 0, 1, 0),
(1277, 4, '2023-05-03', 2841, 0, 0, 0, 0, 1, 0),
(1278, 4, '2023-05-03', 2842, 0, 0, 0, 0, 1, 0),
(1279, 4, '2023-05-03', 2843, 0, 0, 0, 1, 1, 0),
(1280, 4, '2023-05-03', 2844, 0, 0, 0, 1, 1, 0),
(1281, 4, '2023-05-03', 2845, 0, 0, 0, 1, 1, 0),
(1282, 4, '2023-05-03', 2846, 0, 0, 0, 1, 1, 0),
(1294, 1, '2023-05-08', 2847, 0, 0, 0, 1, 1, 0),
(1295, 2, '2023-05-08', 2847, 0, 0, 0, 0, 1, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `invt_branch`
--
ALTER TABLE `invt_branch`
  ADD PRIMARY KEY (`BranchID`),
  ADD UNIQUE KEY `BID` (`BID`);

--
-- Indexes for table `invt_brand`
--
ALTER TABLE `invt_brand`
  ADD PRIMARY KEY (`BrandId`);

--
-- Indexes for table `invt_category`
--
ALTER TABLE `invt_category`
  ADD PRIMARY KEY (`CategoryId`);

--
-- Indexes for table `invt_common_discounts`
--
ALTER TABLE `invt_common_discounts`
  ADD PRIMARY KEY (`CDId`);

--
-- Indexes for table `invt_customers`
--
ALTER TABLE `invt_customers`
  ADD PRIMARY KEY (`CustomerId`);

--
-- Indexes for table `invt_gst`
--
ALTER TABLE `invt_gst`
  ADD PRIMARY KEY (`GSTId`);

--
-- Indexes for table `invt_item_details`
--
ALTER TABLE `invt_item_details`
  ADD PRIMARY KEY (`IDID`);

--
-- Indexes for table `invt_item_master`
--
ALTER TABLE `invt_item_master`
  ADD PRIMARY KEY (`ICode`);

--
-- Indexes for table `invt_item_type`
--
ALTER TABLE `invt_item_type`
  ADD PRIMARY KEY (`ITypeId`);

--
-- Indexes for table `invt_purchase_details`
--
ALTER TABLE `invt_purchase_details`
  ADD PRIMARY KEY (`PDID`);

--
-- Indexes for table `invt_purchase_master`
--
ALTER TABLE `invt_purchase_master`
  ADD PRIMARY KEY (`PMID`);

--
-- Indexes for table `invt_stock`
--
ALTER TABLE `invt_stock`
  ADD PRIMARY KEY (`SID`);

--
-- Indexes for table `invt_transaction`
--
ALTER TABLE `invt_transaction`
  ADD PRIMARY KEY (`TID`);

--
-- Indexes for table `invt_unit`
--
ALTER TABLE `invt_unit`
  ADD PRIMARY KEY (`UnitId`);

--
-- Indexes for table `invt_vendors`
--
ALTER TABLE `invt_vendors`
  ADD PRIMARY KEY (`VendorsId`);

--
-- Indexes for table `invt_vendor_payment`
--
ALTER TABLE `invt_vendor_payment`
  ADD PRIMARY KEY (`VPID`);

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
-- AUTO_INCREMENT for table `designation`
--
ALTER TABLE `designation`
  MODIFY `DesignationId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `error_tracking`
--
ALTER TABLE `error_tracking`
  MODIFY `ErrorId` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invt_account_year`
--
ALTER TABLE `invt_account_year`
  MODIFY `AYID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invt_branch`
--
ALTER TABLE `invt_branch`
  MODIFY `BID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invt_brand`
--
ALTER TABLE `invt_brand`
  MODIFY `BrandId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `invt_category`
--
ALTER TABLE `invt_category`
  MODIFY `CategoryId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invt_common_discounts`
--
ALTER TABLE `invt_common_discounts`
  MODIFY `CDId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invt_customers`
--
ALTER TABLE `invt_customers`
  MODIFY `CustomerId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invt_gst`
--
ALTER TABLE `invt_gst`
  MODIFY `GSTId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invt_item_details`
--
ALTER TABLE `invt_item_details`
  MODIFY `IDID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `invt_item_master`
--
ALTER TABLE `invt_item_master`
  MODIFY `ICode` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `invt_item_type`
--
ALTER TABLE `invt_item_type`
  MODIFY `ITypeId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invt_purchase_details`
--
ALTER TABLE `invt_purchase_details`
  MODIFY `PDID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `invt_purchase_master`
--
ALTER TABLE `invt_purchase_master`
  MODIFY `PMID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `invt_stock`
--
ALTER TABLE `invt_stock`
  MODIFY `SID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `invt_transaction`
--
ALTER TABLE `invt_transaction`
  MODIFY `TID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `invt_unit`
--
ALTER TABLE `invt_unit`
  MODIFY `UnitId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invt_vendors`
--
ALTER TABLE `invt_vendors`
  MODIFY `VendorsId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invt_vendor_payment`
--
ALTER TABLE `invt_vendor_payment`
  MODIFY `VPID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `page`
--
ALTER TABLE `page`
  MODIFY `PageId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2848;

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
  MODIFY `UserId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_privilages`
--
ALTER TABLE `user_privilages`
  MODIFY `UpId` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1296;

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
