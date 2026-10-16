-- MAKE IN INDIA CAPITAL PMS — Hostinger import
-- Generated: 2026-10-14 20:29:38
-- Import into your Hostinger MySQL database via phpMyAdmin → Import
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------
-- Table structure: `pms_audit_log`
DROP TABLE IF EXISTS `pms_audit_log`;
CREATE TABLE `pms_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_audit_log`
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('1', 'system', 'Seeded demo portfolio data', 'system', '0', '', 'Initial PMS portal tables populated', '2026-09-21 00:04:42');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('2', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '1', '', 'OTP sent', '2026-09-21 00:10:15');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('3', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-21 00:10:24');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('4', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '2', '', 'OTP sent', '2026-09-21 00:24:51');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('5', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-21 00:24:59');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('6', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '3', '', 'OTP sent', '2026-09-22 13:01:30');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('7', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-22 13:01:38');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('8', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '4', '', 'OTP sent', '2026-09-25 17:46:55');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('9', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-25 17:47:10');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('10', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '5', '', 'OTP sent', '2026-09-25 18:23:06');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('11', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-25 18:23:16');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('12', 'sudhirhoshing@gmail.com', 'Requested OTP', 'otp', '6', '', 'OTP sent', '2026-09-25 19:08:42');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('13', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'OTP verified', '2026-09-25 19:08:48');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('14', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'Password login', '2026-10-13 23:18:18');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('15', 'sudhirhoshing@gmail.com', 'Client login', 'client', '1', '', 'Password login', '2026-10-14 23:24:39');
INSERT INTO `pms_audit_log` (`id`, `actor`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `created_at`) VALUES ('16', 'sudhirhoshing@gmail.com', 'Changed login password', 'client', '1', 'Pms@1234', 'Ravi@4368', '2026-10-14 23:25:29');

-- --------------------------------------------------------
-- Table structure: `pms_clients`
DROP TABLE IF EXISTS `pms_clients`;
CREATE TABLE `pms_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `login_password` varchar(100) NOT NULL DEFAULT 'Pms@1234',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(80) NOT NULL DEFAULT 'Active & Verified',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pms_client_email` (`email`),
  UNIQUE KEY `unique_pms_client_mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_clients`
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('1', 'Sudhir Rao Hoshing', 'sudhirhoshing@gmail.com', '8828040322', 'Ravi@4368', '0', 'Active & Verified', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('2', 'Aparna Hoshing', 'aparna.hoshing@example.com', '9876543211', 'Pms@1234', '1', 'Active & Verified', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('3', 'Sudhir Hoshing HUF (A1)', 'sudhir.hufa1@makeindiapms.local', '9876543212', 'Pms@1234', '1', 'Active & Verified', '1', '2026-09-25 18:12:30');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('4', 'Sudhir Hoshing (A3)', 'sudhir.a3@makeindiapms.local', '9876543213', 'Pms@1234', '1', 'Active & Verified', '1', '2026-09-25 18:12:30');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('5', 'Sudhir Hoshing HUF (A4)', 'sudhir.hufa4@makeindiapms.local', '9876543214', 'Pms@1234', '1', 'Active & Verified', '1', '2026-09-25 18:12:30');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('6', 'Junaid Shaikh', 'shaikh.naved79@gmail.com', '9137483417', 'Pms@1234', '1', 'Active & Verified', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('7', 'Fatema Tarawala', 'hftarawala@hotmail.com', '9619500185', 'Pms@1234', '1', 'Active & Verified', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('8', 'Vinita Abhishek Gupta', 'vinigupta@gmail.com', '8104548180', 'Pms@1234', '1', 'Active & Verified', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('9', 'Keki Jal Guard', 'kjguard2010@gmail.com', '9821410759', 'Pms@1234', '1', 'Active & Verified', '1', '2026-10-14 23:50:43');
INSERT INTO `pms_clients` (`id`, `name`, `email`, `mobile`, `login_password`, `must_change_password`, `status`, `is_active`, `created_at`) VALUES ('10', 'Gautam G Mallapur', 'drgphoto@gmail.com', '9820427170', 'Pms@1234', '1', 'Active & Verified', '1', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_entries`
DROP TABLE IF EXISTS `pms_entries`;
CREATE TABLE `pms_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(150) NOT NULL,
  `pms_no` varchar(50) NOT NULL,
  `principal_investment` decimal(18,2) NOT NULL DEFAULT 0.00,
  `monthly_payout` decimal(18,2) NOT NULL DEFAULT 0.00,
  `current_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pms_entries`
INSERT INTO `pms_entries` (`id`, `client_name`, `pms_no`, `principal_investment`, `monthly_payout`, `current_value`, `created_at`) VALUES ('1', 'Ravi ', '22222', '1000000.00', '40000.00', '600000.00', '2026-09-25 17:48:39');

-- --------------------------------------------------------
-- Table structure: `pms_folio_access`
DROP TABLE IF EXISTS `pms_folio_access`;
CREATE TABLE `pms_folio_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pms_folio_access` (`client_id`,`folio_id`),
  KEY `idx_pms_access_client` (`client_id`),
  KEY `idx_pms_access_folio` (`folio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- (no rows in `pms_folio_access`)

-- --------------------------------------------------------
-- Table structure: `pms_folios`
DROP TABLE IF EXISTS `pms_folios`;
CREATE TABLE `pms_folios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `folio_number` varchar(50) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_type` varchar(80) NOT NULL DEFAULT 'Individual',
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  `investment_start_date` date DEFAULT NULL,
  `performance_remark` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pms_folio_number` (`folio_number`),
  KEY `idx_pms_folio_client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_folios`
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('1', '3', '39182049/11', 'Sudhir Hoshing HUF (A1)', 'HUF', 'Active', '2025-01-10', 'Strong Compounding', '2026-09-21 00:04:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('2', '1', '50192834/88', 'Sudhir Hoshing (A2)', 'Individual', 'Active', '2025-03-18', 'Excellent Growth', '2026-09-21 00:04:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('3', '4', '77281903/05', 'Sudhir Hoshing (A3)', 'Individual', 'Active', '2025-04-02', 'Strong Alpha', '2026-09-21 00:04:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('4', '5', '88391024/90', 'Sudhir Hoshing HUF (A4)', 'HUF', 'Active', '2025-02-14', 'High Risk / Rebalancing', '2026-09-21 00:04:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('5', '2', '10482910/42', 'Aparna Hoshing', 'Individual', 'Active', '2025-06-01', 'Stable & Healthy', '2026-09-21 00:04:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('6', '6', 'JUNA9137/35', 'Junaid Shaikh', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('7', '7', 'FATE9619/20', 'Fatema Tarawala', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('8', '8', 'VINI8104/01', 'Vinita Abhishek Gupta (F1)', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('9', '8', 'VINI8104/02', 'Vinita Abhishek Gupta (F2)', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('10', '8', 'VINI8104/03', 'Vinita Abhishek Gupta (F3)', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:42');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('11', '9', 'KEKI9821/18', 'Keki Jal Guard', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:43');
INSERT INTO `pms_folios` (`id`, `client_id`, `folio_number`, `account_name`, `account_type`, `status`, `investment_start_date`, `performance_remark`, `created_at`) VALUES ('12', '10', 'GAUT9820/20', 'Gautam G Mallapur', 'Individual', 'Active', '2026-04-01', 'Valuation as per latest mark-to-market provided by the investment team.', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_holdings`
DROP TABLE IF EXISTS `pms_holdings`;
CREATE TABLE `pms_holdings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `asset_name` varchar(150) NOT NULL,
  `quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `average_cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `current_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `current_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `valuation_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pms_hold_client` (`client_id`),
  KEY `idx_pms_hold_folio` (`folio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_holdings`
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('1', '1', '2', 'Large Cap Equity Portfolio', '10000.0000', '4500.00', '4500.00', '45000000.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('2', '1', '2', 'Mid Cap Equity Portfolio', '5000.0000', '4000.00', '4000.00', '20000000.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('3', '1', '2', 'Debt / Liquid Allocation', '6365.2130', '1000.00', '1000.00', '6365213.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('4', '3', '1', 'Managed Equity Portfolio', '1074.2890', '10000.00', '10000.00', '10742890.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('5', '4', '3', 'Managed Equity Portfolio', '4387.1205', '10000.00', '10000.00', '43871205.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('6', '5', '4', 'Managed Equity Portfolio', '2228.9814', '10000.00', '10000.00', '22289814.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('7', '2', '5', 'Managed Equity Portfolio', '1029.8201', '10000.00', '10000.00', '10298201.00', '2026-09-20', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('8', '6', '6', 'Managed Equity Portfolio', '350.0000', '10000.00', '10000.00', '3500000.00', '2026-09-30', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('9', '7', '7', 'Managed Equity Portfolio', '200.0000', '10000.00', '10000.00', '2000000.00', '2026-09-30', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('10', '8', '8', 'Managed Equity Portfolio', '125.0000', '10000.00', '10000.00', '1250000.00', '2026-09-30', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('11', '8', '9', 'Managed Equity Portfolio', '20.0000', '10000.00', '10000.00', '200000.00', '2026-09-30', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('12', '8', '10', 'Managed Equity Portfolio', '160.0000', '10000.00', '10000.00', '1600000.00', '2026-09-30', '1', '2026-10-14 23:50:43');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('13', '9', '11', 'Managed Equity Portfolio', '180.0000', '10000.00', '10000.00', '1800000.00', '2026-09-30', '1', '2026-10-14 23:50:43');
INSERT INTO `pms_holdings` (`id`, `client_id`, `folio_id`, `asset_name`, `quantity`, `average_cost`, `current_price`, `current_value`, `valuation_date`, `is_active`, `created_at`) VALUES ('14', '10', '12', 'Managed Equity Portfolio', '200.0000', '10000.00', '10000.00', '2000000.00', '2026-09-30', '1', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_otp`
DROP TABLE IF EXISTS `pms_otp`;
CREATE TABLE `pms_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `identifier` varchar(150) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pms_otp_client` (`client_id`),
  KEY `idx_pms_otp_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_otp`
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('1', '1', 'sudhirhoshing@gmail.com', '$2y$10$bHciUO3NbHILAZTet9fXCuh8KjOGhibzMJpU.fn2ipdJR1a3WioZm', '2026-09-21 00:15:13', '0', '1', '127.0.0.1', '2026-09-21 00:10:13');
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('2', '1', 'sudhirhoshing@gmail.com', '$2y$10$q5T3rr4Pp78c6NPWHlYuh.mSs//eFVrHCSW1Ain.LBhWa/gI9OyEy', '2026-09-21 00:29:49', '0', '1', '127.0.0.1', '2026-09-21 00:24:49');
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('3', '1', 'sudhirhoshing@gmail.com', '$2y$10$Vv5ucahO3hcqo.U9qV2zAeHPvbZxqQ9uYV0aYJ0usUFsTvS0QDn3e', '2026-09-22 13:06:28', '0', '1', '127.0.0.1', '2026-09-22 13:01:28');
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('4', '1', 'sudhirhoshing@gmail.com', '$2y$10$jo4J8qluBpbAhcfKhmOHyeDCyfg0b12HlWSo7puYCkGbXId9U8pCS', '2026-09-25 17:51:53', '0', '1', '127.0.0.1', '2026-09-25 17:46:53');
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('5', '1', 'sudhirhoshing@gmail.com', '$2y$10$lXBG23sdTDMKU8WGfJdonOoqPNfm7wZZBLNEapInm5BPoLAjGokfa', '2026-09-25 18:28:04', '0', '1', '127.0.0.1', '2026-09-25 18:23:04');
INSERT INTO `pms_otp` (`id`, `client_id`, `identifier`, `otp_hash`, `expires_at`, `attempts`, `is_used`, `ip_address`, `created_at`) VALUES ('6', '1', 'sudhirhoshing@gmail.com', '$2y$10$m1BveY0C2pBGFkuNEZ24z.hUWm0IdOLB/IAcvXjftWqeYuSDVVsti', '2026-09-25 19:13:40', '0', '1', '127.0.0.1', '2026-09-25 19:08:40');

-- --------------------------------------------------------
-- Table structure: `pms_payout_rules`
DROP TABLE IF EXISTS `pms_payout_rules`;
CREATE TABLE `pms_payout_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio_id` int(11) DEFAULT NULL,
  `payout_percent` decimal(8,4) NOT NULL DEFAULT 2.0000,
  `payout_label` varchar(100) NOT NULL DEFAULT 'Monthly Payout',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pms_rule_folio` (`folio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_payout_rules`
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('1', NULL, '2.0000', 'Monthly Payout', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('2', '2', '2.0000', 'Monthly Payout', '1', '2026-09-21 00:04:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('3', '6', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('4', '7', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('5', '8', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('6', '9', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:42');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('7', '10', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:43');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('8', '11', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:43');
INSERT INTO `pms_payout_rules` (`id`, `folio_id`, `payout_percent`, `payout_label`, `is_active`, `created_at`) VALUES ('9', '12', '2.0000', 'Monthly Payout', '1', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_payouts`
DROP TABLE IF EXISTS `pms_payouts`;
CREATE TABLE `pms_payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `payout_month` varchar(7) NOT NULL,
  `payout_amount` decimal(18,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Paid',
  `payment_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pms_pay_client` (`client_id`),
  KEY `idx_pms_pay_folio` (`folio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_payouts`
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('1', '1', '2', NULL, '2026-08', '1260000.00', 'Paid', '2026-08-31', 'Monthly payout as per configured rule', '2026-09-21 00:04:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('5', '5', '4', NULL, '2026-08', '3360000.00', 'Paid', '2026-08-31', 'Monthly payout as per configured rule', '2026-09-21 00:04:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('6', '2', '5', NULL, '2026-08', '300000.00', 'Paid', '2026-08-31', 'Monthly payout as per configured rule', '2026-09-21 00:04:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('7', '1', '2', '6', '2026-08', '1260000.00', 'Paid', '2026-08-31', 'Automated 2% Monthly Payout', '2026-09-25 18:31:11');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('8', '6', '6', '16', '2026-09', '70000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('9', '7', '7', '18', '2026-09', '40000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('10', '8', '8', '20', '2026-09', '25000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('11', '8', '9', '22', '2026-09', '4000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('12', '8', '10', '24', '2026-09', '32000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:42');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('13', '9', '11', '26', '2026-09', '36000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:43');
INSERT INTO `pms_payouts` (`id`, `client_id`, `folio_id`, `transaction_id`, `payout_month`, `payout_amount`, `status`, `payment_date`, `remarks`, `created_at`) VALUES ('14', '10', '12', '28', '2026-09', '40000.00', 'Paid', '2026-09-30', 'Monthly payout 2% as per mandate', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_transactions`
DROP TABLE IF EXISTS `pms_transactions`;
CREATE TABLE `pms_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `folio_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Completed',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pms_txn_client` (`client_id`),
  KEY `idx_pms_txn_folio` (`folio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `pms_transactions`
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('1', '1', '2', 'Investment', '57300000.00', '2025-03-18', 'Initial Capital Deployment (Make in India PMS)', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('3', '1', '2', 'Withdrawal', '4500000.00', '2026-01-15', 'Requested Liquidity Payout', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('6', '1', '2', 'Monthly Payout', '1260000.00', '2026-08-31', 'Automated 2% Monthly Payout', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('8', '3', '1', 'Investment', '10300000.00', '2025-01-10', 'Initial Capital Deployment', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('9', '4', '3', 'Investment', '40000000.00', '2025-04-02', 'Initial Capital Deployment', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('10', '4', '3', 'Withdrawal', '3200000.00', '2026-03-12', 'Requested Liquidity Payout', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('11', '5', '4', 'Investment', '25000000.00', '2025-02-14', 'Initial Capital Deployment', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('12', '5', '4', 'Monthly Payout', '3360000.00', '2026-08-31', 'Cumulative monthly payouts', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('13', '2', '5', 'Investment', '10000000.00', '2025-06-01', 'Initial Capital Deployment', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('14', '2', '5', 'Monthly Payout', '300000.00', '2026-08-31', 'Monthly payout as per configured rule', 'Completed', 'system', '2026-09-21 00:04:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('15', '6', '6', 'Investment', '3500000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('16', '6', '6', 'Monthly Payout', '70000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('17', '7', '7', 'Investment', '2000000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('18', '7', '7', 'Monthly Payout', '40000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('19', '8', '8', 'Investment', '1250000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('20', '8', '8', 'Monthly Payout', '25000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('21', '8', '9', 'Investment', '200000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('22', '8', '9', 'Monthly Payout', '4000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('23', '8', '10', 'Investment', '1600000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('24', '8', '10', 'Monthly Payout', '32000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:42');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('25', '9', '11', 'Investment', '1800000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:43');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('26', '9', '11', 'Monthly Payout', '36000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:43');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('27', '10', '12', 'Investment', '2000000.00', '2026-04-01', 'Initial Capital Deployment', 'Completed', 'admin', '2026-10-14 23:50:43');
INSERT INTO `pms_transactions` (`id`, `client_id`, `folio_id`, `transaction_type`, `amount`, `transaction_date`, `remarks`, `status`, `created_by`, `created_at`) VALUES ('28', '10', '12', 'Monthly Payout', '40000.00', '2026-09-30', 'Monthly payout 2% as per mandate', 'Completed', 'admin', '2026-10-14 23:50:43');

-- --------------------------------------------------------
-- Table structure: `pms_users`
DROP TABLE IF EXISTS `pms_users`;
CREATE TABLE `pms_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `pms_number` varchar(50) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no rows in `pms_users`)

-- --------------------------------------------------------
-- Table structure: `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `username`, `password`) VALUES ('1', 'admin', '0192023a7bbd73250516f069df18b500');

SET FOREIGN_KEY_CHECKS=1;
-- Done
