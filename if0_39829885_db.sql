-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Oct 09, 2025 at 09:35 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39829885_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `archived_users`
--

CREATE TABLE `archived_users` (
  `USER_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) NOT NULL,
  `MIDDLE_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PHONE` varchar(20) DEFAULT NULL,
  `PROFILE_PICTURE` varchar(255) DEFAULT 'default_avatar.png',
  `PASSWORD` varchar(255) NOT NULL,
  `ROLE` enum('driver','admin','customer') DEFAULT 'customer',
  `ACCOUNT_STATUS` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `LOCK_EXPIRES_AT` datetime DEFAULT NULL,
  `CREATED_AT` timestamp NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `archived_users`
--

INSERT INTO `archived_users` (`USER_ID`, `FIRST_NAME`, `MIDDLE_NAME`, `LAST_NAME`, `EMAIL`, `PHONE`, `PROFILE_PICTURE`, `PASSWORD`, `ROLE`, `ACCOUNT_STATUS`, `LOCK_EXPIRES_AT`, `CREATED_AT`, `UPDATED_AT`, `last_login_at`) VALUES
(6, 'q3rq', 'qr3rq', 'qr3rq', 'qkramirez04@tip.edu.ph', '125135135', 'default_avatar.png', '$2y$10$ukVSWkly5c4s9UKZZNMPMekPgv.jNIDiSBirIJ5g48KTGElahPAB6', 'customer', 'ACTIVE', NULL, '2025-10-03 08:19:37', '2025-10-03 08:19:37', NULL),
(5, 'Crack', 'Nemiada', 'Cart', 'crackcart.auth@gmail.com', '0995 633 6238', 'default_avatar.png', '$2y$10$IwU5AvKjNBEncZ8OeXyYTu67a/fKEKo2eYWEuIdRY.HsBhIuSrpFa', 'customer', 'ACTIVE', NULL, '2025-09-25 08:02:11', '2025-09-25 08:02:11', NULL),
(4, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi@gmail.com', '', 'default_avatar.png', '$2y$10$befzVXETI53dfpYMhMBvUeXykYXzrIK96lTix/3QtQFUoLCEZ4zpW', 'customer', 'ACTIVE', NULL, '2025-09-22 12:09:06', '2025-10-09 03:35:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Booking`
--

CREATE TABLE `Booking` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pick_up_address` int(11) DEFAULT NULL,
  `drop_off_address` int(11) DEFAULT NULL,
  `rate_id` int(11) DEFAULT NULL,
  `tray_quantity` int(11) DEFAULT NULL,
  `tray_size` int(11) DEFAULT NULL,
  `distance_km` decimal(10,2) DEFAULT NULL,
  `quoted_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `scheduled_at` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `coupon_code` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `coupon_code`, `user_id`, `discount_value`, `is_used`, `created_at`, `expiry_date`) VALUES
(1, 'RETURN-68E57E7740A6B', 15, '320.00', 1, '2025-10-07 20:56:23', '2025-11-06'),
(2, 'RETURN-68E5EBE323B54', 15, '480.00', 1, '2025-10-08 04:43:15', '2025-11-07'),
(3, 'RETURN-68E5F279C17C1', 19, '210.00', 1, '2025-10-08 05:11:21', '2025-11-07'),
(4, 'RETURN-68E5F3524937C', 15, '320.00', 0, '2025-10-08 05:14:58', '2025-11-07'),
(5, 'RETURN-68E5F35281E74', 15, '320.00', 0, '2025-10-08 05:14:58', '2025-11-07'),
(6, 'RETURN-68E60154D32E7', 20, '8200.00', 0, '2025-10-08 06:14:44', '2025-11-07'),
(7, 'RETURN-68E78EC822FAE', 9, '320.00', 0, '2025-10-09 10:30:32', '2025-11-08');

-- --------------------------------------------------------

--
-- Table structure for table `Delivery_Assignment`
--

CREATE TABLE `Delivery_Assignment` (
  `assignment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `assigned_at` date DEFAULT NULL,
  `accepted_at` date DEFAULT NULL,
  `picked_up_at` date DEFAULT NULL,
  `delivered_at` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `Delivery_Assignment`
--

INSERT INTO `Delivery_Assignment` (`assignment_id`, `booking_id`, `driver_id`, `vehicle_id`, `assigned_at`, `accepted_at`, `picked_up_at`, `delivered_at`, `status`) VALUES
(1, 92, 14, 1, '2025-10-09', NULL, NULL, NULL, 'assigned'),
(2, 93, 14, 1, '2025-10-09', NULL, NULL, NULL, 'assigned'),
(3, 89, 14, 1, '2025-10-09', NULL, NULL, NULL, 'assigned'),
(4, 95, 14, 1, '2025-10-09', NULL, NULL, NULL, 'assigned');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_incidents`
--

CREATE TABLE `delivery_incidents` (
  `incident_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('reported','Pending User Action','Requested Replacement','Order Cancelled','Resolved','replace','cancel') NOT NULL DEFAULT 'reported',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Driver`
--

CREATE TABLE `Driver` (
  `driver_id` int(11) NOT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `Driver`
--

INSERT INTO `Driver` (`driver_id`, `phone_no`, `license_no`, `expiry`, `status`, `vehicle_id`) VALUES
(14, '09956336238', NULL, NULL, 'available', 1),
(19, '09284943131', NULL, NULL, 'available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Fleet_Maintenance`
--

CREATE TABLE `Fleet_Maintenance` (
  `maintenance_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Locations`
--

CREATE TABLE `Locations` (
  `location_id` int(11) NOT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `NOTIFICATION`
--

CREATE TABLE `NOTIFICATION` (
  `NOTIFICATION_ID` int(11) NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `MESSAGE` varchar(255) NOT NULL,
  `IS_READ` tinyint(1) DEFAULT 0,
  `CREATED_AT` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `NOTIFICATION`
--

INSERT INTO `NOTIFICATION` (`NOTIFICATION_ID`, `USER_ID`, `MESSAGE`, `IS_READ`, `CREATED_AT`) VALUES
(1, 3, 'This is a test notification', 1, '2025-09-23 03:48:31'),
(2, 3, 'Get 20% off your next delivery with code: CRACK20!', 1, '2025-09-23 03:51:10'),
(3, NULL, 'Big news! We now deliver to all major cities. Check our updated service area.', 0, '2025-09-23 03:53:35'),
(4, NULL, 'Limited time offer! Use code FREEDELIVERY for free delivery on your next order.', 0, '2025-09-23 03:54:14'),
(5, NULL, 'Introducing real-time tracking! Now you can see exactly where your delivery is.', 0, '2025-09-23 03:54:14'),
(6, NULL, 'Happy Holidays from CrackCart! We\'re offering special holiday discounts on all our services.', 0, '2025-09-23 03:54:14'),
(7, 9, 'Your return request for order #31 has been updated to \'rejected\'.', 1, '2025-10-06 09:07:39'),
(8, 9, 'Your order #29 status has been updated to \'shipped\'.', 1, '2025-10-06 09:08:10'),
(9, 11, 'Your account has been unlocked by an administrator.', 1, '2025-10-06 12:23:42'),
(10, 11, 'Your order #22 status has been updated to \'shipped\'.', 1, '2025-10-06 12:35:13'),
(11, 11, 'Your order #22 status has been updated to \'shipped\'.', 1, '2025-10-06 12:36:29'),
(12, 11, 'Your order #22 status has been updated to \'processing\'.', 1, '2025-10-06 12:39:14'),
(13, 11, 'Your order #22 status has been updated to \'delivered\'.', 1, '2025-10-06 12:39:52'),
(14, 11, 'Your order #37 status has been updated to \'delivered\'.', 1, '2025-10-06 12:53:06'),
(15, 11, 'Your order #38 status has been updated to \'delivered\'.', 1, '2025-10-06 13:02:06'),
(16, 11, 'Your return request for order #38 has been updated to \'approved\'.', 1, '2025-10-06 13:02:59'),
(17, 9, 'Your order #28 status has been updated to \'processing\'.', 0, '2025-10-06 13:13:24'),
(18, 11, 'Your order #38 status has been updated to \'shipped\'.', 1, '2025-10-06 13:19:21'),
(19, 11, 'Your order #39 status has been updated to \'processing\'.', 1, '2025-10-06 13:27:09'),
(20, 11, 'Your order #39 status has been updated to \'shipped\'.', 1, '2025-10-06 13:27:53'),
(21, 11, 'Your order #39 status has been updated to \'delivered\'.', 1, '2025-10-06 13:28:07'),
(22, 9, 'Your order #32 status has been updated to \'cancelled\'.', 0, '2025-10-06 18:08:28'),
(23, 0, 'Your return request for order #38 has been updated to \'approved\'.', 0, '2025-10-06 18:17:34'),
(24, 0, 'Your return request for order #38 has been updated to \'rejected\'.', 0, '2025-10-06 18:17:37'),
(25, 0, 'Your return request for order #38 has been updated to \'approved\'.', 0, '2025-10-06 18:17:40'),
(26, 0, 'Your return request for order #31 has been updated to \'approved\'.', 0, '2025-10-06 18:17:47'),
(27, 0, 'Your return request for order #31 has been updated to \'rejected\'.', 0, '2025-10-06 18:17:49'),
(28, 0, 'Your return request for order #31 has been updated to \'approved\'.', 0, '2025-10-06 18:17:52'),
(29, 0, 'Your return request for order #31 has been updated to \'approved\'.', 0, '2025-10-06 18:17:59'),
(30, 0, 'Your return request for order #30 has been updated to \'approved\'.', 0, '2025-10-06 18:18:03'),
(31, 0, 'Your return request for order #22 has been updated to \'approved\'.', 0, '2025-10-06 18:18:06'),
(32, 11, 'Your order #41 status has been updated to \'processing\'.', 1, '2025-10-06 18:33:07'),
(33, 11, 'Your order #42 status has been updated to \'delivered\'.', 1, '2025-10-06 18:33:33'),
(34, 0, 'Your return request for order #42 has been updated to \'approved\'.', 0, '2025-10-06 18:35:38'),
(35, 11, 'Your order #43 status has been updated to \'processing\'.', 1, '2025-10-06 19:23:19'),
(36, 11, 'Your order #43 status has been updated to \'shipped\'.', 1, '2025-10-06 19:23:41'),
(37, 11, 'Your order #43 status has been updated to \'delivered\'.', 1, '2025-10-06 19:23:46'),
(38, 15, 'Your order #48 status has been updated to \'delivered\'.', 1, '2025-10-07 08:58:30'),
(39, 15, 'Your order #48 status has been updated to \'shipped\'.', 1, '2025-10-07 08:59:54'),
(40, 0, 'Your return request for order #49 has been updated to \'rejected\'.', 0, '2025-10-07 09:33:21'),
(41, 4, 'Your account role has been updated to \'Admin\'.', 0, '2025-10-07 09:41:17'),
(42, 13, 'Your account role has been updated to \'Admin\'.', 0, '2025-10-07 09:41:28'),
(43, 13, 'Your account role has been updated to \'Customer\'.', 0, '2025-10-07 09:42:05'),
(44, 4, 'Your account role has been updated to \'Customer\'.', 0, '2025-10-07 09:42:52'),
(45, 16, 'Your order #54 status has been updated to \'delivered\'.', 1, '2025-10-07 10:46:50'),
(46, 15, 'Your order #58 status has been updated to \'delivered\'.', 1, '2025-10-07 12:35:43'),
(47, 11, 'Your order #59 status has been updated to \'delivered\'.', 1, '2025-10-07 17:21:00'),
(48, 15, 'Your order #63 status has been updated to \'shipped\'.', 1, '2025-10-07 20:31:45'),
(49, 15, 'Your order #63 status has been updated to \'delivered\'.', 1, '2025-10-07 20:33:10'),
(50, 15, 'Your return request for order #63 has been updated to \'approved\'.', 1, '2025-10-07 20:34:03'),
(51, 15, 'Your order #65 status has been updated to \'processing\'.', 1, '2025-10-07 20:48:00'),
(52, 15, 'Your order #65 status has been updated to \'delivered\'.', 1, '2025-10-07 20:48:20'),
(53, 15, 'Your return request for order #65 has been updated to \'approved\'.', 1, '2025-10-07 20:49:09'),
(54, 19, 'Your order #62 status has been updated to \'delivered\'.', 1, '2025-10-07 20:50:50'),
(55, 15, 'Your order #66 status has been updated to \'delivered\'.', 1, '2025-10-07 20:51:32'),
(56, 15, 'Your return for order #66 was approved. We have issued a coupon worth â‚±320.00 to your account for the inconvenience.', 1, '2025-10-07 20:56:23'),
(57, 10, 'Your account role has been updated to \'Admin\'.', 0, '2025-10-07 21:04:20'),
(58, 15, 'Your order #68 status has been updated to \'delivered\'.', 1, '2025-10-08 04:41:46'),
(59, 15, 'Your return for order #68 was approved. We have issued a coupon worth â‚±480.00 to your account for the inconvenience.', 1, '2025-10-08 04:43:15'),
(60, 19, 'Your order #70 status has been updated to \'shipped\'.', 1, '2025-10-08 04:50:06'),
(61, 19, 'Your order #70 status has been updated to \'delivered\'.', 1, '2025-10-08 04:50:12'),
(62, 19, 'Your return request for order #70 has been updated to \'rejected\'.', 1, '2025-10-08 04:52:37'),
(63, 15, 'Your order #71 status has been updated to \'delivered\'.', 1, '2025-10-08 05:06:53'),
(64, 19, 'Your return request for order #62 has been updated to \'approved\'.', 1, '2025-10-08 05:09:12'),
(65, 19, 'Your return request for order #62 has been updated to \'approved\'.', 1, '2025-10-08 05:09:12'),
(66, 19, 'Your return request for order #62 has been updated to \'approved\'.', 1, '2025-10-08 05:09:12'),
(67, 19, 'Your order #72 status has been updated to \'delivered\'.', 1, '2025-10-08 05:10:57'),
(68, 19, 'Your return for order #72 was approved. We have issued a coupon worth â‚±210.00 to your account for the inconvenience.', 1, '2025-10-08 05:11:21'),
(69, 15, 'Your order #69 status has been updated to \'delivered\'.', 1, '2025-10-08 05:14:17'),
(70, 15, 'Your return for order #71 was approved. We have issued a coupon worth â‚±320.00 to your account for the inconvenience.', 1, '2025-10-08 05:14:58'),
(71, 15, 'Your return for order #71 was approved. We have issued a coupon worth â‚±320.00 to your account for the inconvenience.', 1, '2025-10-08 05:14:58'),
(72, 19, 'Your order #76 status has been updated to \'processing\'.', 1, '2025-10-08 06:08:19'),
(73, 19, 'Your order #76 status has been updated to \'delivered\'.', 1, '2025-10-08 06:11:23'),
(74, 20, 'Your order #75 status has been updated to \'delivered\'.', 1, '2025-10-08 06:11:28'),
(75, 21, 'Your order #77 status has been updated to \'processing\'.', 1, '2025-10-08 06:12:05'),
(76, 19, 'Your order #73 status has been updated to \'processing\'.', 1, '2025-10-08 06:12:14'),
(77, 11, 'Your order #74 status has been updated to \'processing\'.', 1, '2025-10-08 06:12:54'),
(78, 21, 'Your order #77 status has been updated to \'delivered\'.', 0, '2025-10-08 06:13:12'),
(79, 11, 'Your order #74 status has been updated to \'delivered\'.', 1, '2025-10-08 06:13:28'),
(80, 19, 'Your order #73 status has been updated to \'delivered\'.', 1, '2025-10-08 06:13:36'),
(81, 20, 'Your return for order #75 was approved. We have issued a coupon worth â‚±8,200.00 to your account for the inconvenience.', 1, '2025-10-08 06:14:44'),
(82, 15, 'Your order #78 status has been updated to \'delivered\'.', 0, '2025-10-08 07:53:59'),
(83, 16, 'Your order #79 status has been updated to \'shipped\'.', 1, '2025-10-08 08:48:39'),
(84, 16, 'Your return request for order #79 has been updated to \'rejected\'.', 1, '2025-10-08 08:52:01'),
(85, 16, 'Your order #81 status has been updated to \'processing\'.', 0, '2025-10-08 10:29:04'),
(86, 16, 'Your order #81 status has been updated to \'delivered\'.', 0, '2025-10-08 10:29:23'),
(87, 4, 'Your account role has been updated to \'Driver\'.', 0, '2025-10-09 03:16:33'),
(88, 4, 'Your account role has been updated to \'Customer\'.', 0, '2025-10-09 03:35:08'),
(89, 14, 'Your account role has been updated to \'Driver\'.', 0, '2025-10-09 05:49:30'),
(90, 14, 'Your account role has been updated to \'Customer\'.', 0, '2025-10-09 05:56:17'),
(91, 14, 'Your account role has been updated to \'Driver\'.', 0, '2025-10-09 05:56:25'),
(92, 14, 'Your account role has been updated to \'Customer\'.', 0, '2025-10-09 05:56:56'),
(93, 14, 'Your account role has been updated to \'Driver\'.', 0, '2025-10-09 05:57:04'),
(94, 11, 'Your order #83 status has been updated to \'processing\'.', 1, '2025-10-09 07:22:34'),
(95, 11, 'Your order #83 status has been updated to \'cancelled\'.', 1, '2025-10-09 07:26:03'),
(96, 11, 'Your order #83 status has been updated to \'pending\'.', 1, '2025-10-09 07:26:11'),
(97, 11, 'Your order #83 status has been updated to \'delivered\'.', 1, '2025-10-09 07:26:19'),
(98, 11, 'Your order #83 status has been updated to \'processing\'.', 1, '2025-10-09 07:26:39'),
(99, 11, 'Your order #84 status has been updated to \'processing\'.', 1, '2025-10-09 07:27:39'),
(100, 19, 'Your account role has been updated to \'Customer\'.', 1, '2025-10-09 07:59:40'),
(101, 19, 'Your account role has been updated to \'Driver\'.', 1, '2025-10-09 07:59:45'),
(102, 11, 'There was an incident with your order #84. Please review and decide on the next step.', 1, '2025-10-09 08:34:55'),
(103, 11, 'There was an incident with your order #84. Please review and decide on the next step.', 1, '2025-10-09 08:37:18'),
(104, 11, 'There was an incident with your order #84. Please review and decide on the next step.', 1, '2025-10-09 08:47:50'),
(105, 11, 'There was an incident with your order #84. Please review and decide on the next step.', 1, '2025-10-09 08:49:26'),
(106, 11, 'There was an incident with your order #84. Please review and decide on the next step.', 1, '2025-10-09 09:05:03'),
(107, 9, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:44:52'),
(108, 10, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:44:52'),
(109, 9, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:45:54'),
(110, 10, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:45:54'),
(111, 9, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:47:59'),
(112, 10, 'User #11 has responded to an incident. They have requested a replacement for order #84.', 0, '2025-10-09 09:47:59'),
(113, 9, 'Your order #85 status has been updated to \'processing\'.', 0, '2025-10-09 09:58:27'),
(114, 16, 'Your order #87 status has been updated to \'delivered\'.', 0, '2025-10-09 10:10:54'),
(115, 9, 'Your order #86 status has been updated to \'delivered\'.', 0, '2025-10-09 10:11:04'),
(116, 9, 'Your order #85 status has been updated to \'delivered\'.', 0, '2025-10-09 10:12:35'),
(117, 9, 'Your order #88 status has been updated to \'processing\'.', 0, '2025-10-09 10:14:08'),
(118, 9, 'Your return for order #89 was approved. We have issued a coupon worth â‚±320.00 to your account for the inconvenience.', 0, '2025-10-09 10:30:32'),
(119, 9, 'Your return request for order #90 has been updated to \'approved\'.', 0, '2025-10-09 10:33:07'),
(120, 11, 'Your order #91 status has been updated to \'processing\'.', 1, '2025-10-09 11:51:31'),
(121, 9, 'User #11 has responded to an incident. They have requested a replacement for order #91.', 0, '2025-10-09 12:10:11'),
(122, 10, 'User #11 has responded to an incident. They have requested a replacement for order #91.', 0, '2025-10-09 12:10:11'),
(123, 11, 'Your order #91 status has been updated to \'cancelled\'.', 1, '2025-10-09 12:31:52'),
(124, 11, 'Your order #92 status has been updated to \'processing\'.', 1, '2025-10-09 12:32:01'),
(125, 9, 'User #11 has responded to an incident. They have requested a replacement for order #92.', 0, '2025-10-09 12:34:18'),
(126, 10, 'User #11 has responded to an incident. They have requested a replacement for order #92.', 0, '2025-10-09 12:34:18'),
(127, 11, 'Your order #93 status has been updated to \'processing\'.', 1, '2025-10-09 12:40:25'),
(128, 11, 'Your order #92 status has been updated to \'delivered\'.', 1, '2025-10-09 12:40:45'),
(129, 9, 'User #11 has responded to an incident. They have requested a replacement for order #93.', 0, '2025-10-09 12:49:20'),
(130, 10, 'User #11 has responded to an incident. They have requested a replacement for order #93.', 0, '2025-10-09 12:49:20'),
(131, 9, 'Your order #89 status has been updated to \'delivered\'.', 0, '2025-10-09 12:53:07'),
(132, 11, 'There was an incident with your order #93. Please review and decide on the next step.', 1, '2025-10-09 12:53:24'),
(133, 11, 'Your order #94 status has been updated to \'pending\'.', 1, '2025-10-09 13:13:37'),
(134, 11, 'Your order #94 status has been updated to \'processing\'.', 1, '2025-10-09 13:13:42'),
(135, 11, 'Your order #94 status has been updated to \'shipped\'.', 1, '2025-10-09 13:13:47'),
(136, 11, 'Your order #94 status has been updated to \'paid\'.', 1, '2025-10-09 13:13:53'),
(137, 11, 'Your order #94 status has been updated to \'delivered\'.', 1, '2025-10-09 13:13:59'),
(138, 11, 'Your order #94 status has been updated to \'processing\'.', 1, '2025-10-09 13:14:04'),
(139, 11, 'Your order #95 status has been updated to \'processing\'.', 1, '2025-10-09 13:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `Payment`
--

CREATE TABLE `Payment` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `paid_at` date DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `Payment`
--

INSERT INTO `Payment` (`payment_id`, `booking_id`, `order_id`, `amount`, `currency`, `method`, `status`, `paid_at`, `transaction_id`) VALUES
(1, NULL, 7, '205.00', 'PHP', 'cod', 'pending', NULL, NULL),
(2, NULL, 8, '205.00', 'PHP', 'paypal', 'completed', NULL, '82G15045GP0068238'),
(3, NULL, 9, '615.00', 'PHP', 'cod', 'pending', NULL, NULL),
(4, NULL, 10, '2310.00', 'PHP', 'cod', 'pending', NULL, NULL),
(5, NULL, 11, '205.00', 'PHP', 'cod', 'pending', NULL, NULL),
(6, NULL, 12, '205.00', 'PHP', 'cod', 'pending', NULL, NULL),
(7, NULL, 13, '205.00', 'PHP', 'cod', 'pending', NULL, NULL),
(8, NULL, 14, '205.00', 'PHP', 'paypal', 'completed', NULL, '5PP07558NW4621240'),
(9, NULL, 22, '210.00', 'PHP', 'paypal', 'completed', NULL, '76U764920M824035D'),
(10, NULL, 23, '210.00', 'PHP', 'paypal', 'completed', NULL, '1AP80039RL494342L'),
(11, NULL, 27, '420.00', 'PHP', 'paypal', 'completed', NULL, '4CJ98852WH5379356'),
(12, NULL, 28, '210.00', 'PHP', 'paypal', 'completed', NULL, '1BU05010GY789624A'),
(13, NULL, 30, '6.00', 'PHP', 'paypal', 'completed', NULL, '00X55316DU909961A'),
(14, NULL, 36, '2640.00', 'PHP', 'paypal', 'completed', NULL, '7E9862868A322804U'),
(15, NULL, 39, '6720.00', 'PHP', 'paypal', 'completed', NULL, '28D76966EE848362C'),
(16, NULL, NULL, '470.00', 'PHP', 'paypal', 'completed', NULL, '9FX91469EE764404K'),
(17, NULL, 40, '470.00', 'PHP', 'paypal', 'completed', NULL, '7AS26800VR2138726'),
(18, NULL, NULL, '360.00', 'PHP', 'cod', 'pending', NULL, NULL),
(19, NULL, 41, '360.00', 'PHP', 'cod', 'pending', NULL, NULL),
(20, NULL, 42, '360.00', 'PHP', 'cod', 'pending', NULL, NULL),
(21, NULL, 43, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(22, NULL, 44, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(23, NULL, 45, '3300.00', 'PHP', 'paypal', 'completed', NULL, '39K40447KW191435L'),
(24, NULL, 46, '420.00', 'PHP', 'paypal', 'completed', NULL, '4F331906TW943532U'),
(25, NULL, 47, '290.00', 'PHP', 'cod', 'pending', NULL, NULL),
(26, NULL, 48, '310.00', 'PHP', 'cod', 'pending', NULL, NULL),
(27, NULL, 49, '3300.00', 'PHP', 'paypal', 'completed', NULL, '49S92400AX179531C'),
(28, NULL, 50, '63200.00', 'PHP', 'paypal', 'completed', NULL, '7C516343LS477543V'),
(29, NULL, 51, '3300.00', 'PHP', 'paypal', 'completed', NULL, '38B74711DS4398800'),
(30, NULL, 52, '1125.00', 'PHP', 'paypal', 'completed', NULL, '5BG7306474313881R'),
(31, NULL, 53, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(32, NULL, 54, '186.00', 'PHP', 'paypal', 'completed', NULL, '0V828765XU578580U'),
(33, NULL, 55, '162.00', 'PHP', 'paypal', 'completed', NULL, '5TM94795LF565500T'),
(34, NULL, 56, '360.00', 'PHP', 'paypal', 'completed', NULL, '01C911417E5304329'),
(35, NULL, 57, '360.00', 'PHP', 'paypal', 'completed', NULL, '55873954F7932110N'),
(36, NULL, 58, '284.00', 'PHP', 'cod', 'pending', NULL, NULL),
(37, NULL, 59, '560.00', 'PHP', 'cod', 'pending', NULL, NULL),
(38, NULL, 60, '470.00', 'PHP', 'cod', 'pending', NULL, NULL),
(39, NULL, 61, '470.00', 'PHP', 'cod', 'pending', NULL, NULL),
(40, NULL, 62, '790.00', 'PHP', 'cod', 'pending', NULL, NULL),
(41, NULL, NULL, '284.00', 'PHP', 'cod', 'pending', NULL, NULL),
(42, NULL, NULL, '284.00', 'PHP', 'paypal', 'completed', NULL, '01H17976SP7778417'),
(43, NULL, 63, '284.00', 'PHP', 'cod', 'pending', NULL, NULL),
(44, NULL, NULL, '320.00', 'PHP', 'paypal', 'completed', NULL, '3HN72759NX592272W'),
(45, NULL, NULL, '470.00', 'PHP', 'paypal', 'completed', NULL, '4XV15195R94761402'),
(46, NULL, 64, '470.00', 'PHP', 'paypal', 'completed', NULL, '7XF940754L019954S'),
(47, NULL, 65, '790.00', 'PHP', 'cod', 'pending', NULL, NULL),
(48, NULL, 66, '470.00', 'PHP', 'cod', 'pending', NULL, NULL),
(49, NULL, 67, '150.00', 'PHP', 'paypal', 'completed', NULL, '9GD86862ME2382207'),
(50, NULL, 68, '580.00', 'PHP', 'cod', 'pending', NULL, NULL),
(51, NULL, 69, '1540.00', 'PHP', 'cod', 'pending', NULL, NULL),
(52, NULL, 70, '1060.00', 'PHP', 'cod', 'pending', NULL, NULL),
(53, NULL, 71, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(54, NULL, 72, '310.00', 'PHP', 'cod', 'pending', NULL, NULL),
(55, NULL, 73, '210.00', 'PHP', 'cod', 'pending', NULL, NULL),
(56, NULL, 74, '220.00', 'PHP', 'paypal', 'completed', NULL, '8NV499021M480881U'),
(57, NULL, 75, '8350.00', 'PHP', 'cod', 'pending', NULL, NULL),
(58, NULL, 76, '14690.00', 'PHP', 'cod', 'pending', NULL, NULL),
(59, NULL, 77, '730.00', 'PHP', 'cod', 'pending', NULL, NULL),
(60, NULL, 78, '630.00', 'PHP', 'paypal', 'completed', NULL, '3D170800RD561170A'),
(61, NULL, 79, '310.00', 'PHP', 'cod', 'pending', NULL, NULL),
(62, NULL, 80, '470.00', 'PHP', 'paypal', 'completed', NULL, '5L201290H40460108'),
(63, NULL, 81, '290.00', 'PHP', 'cod', 'pending', NULL, NULL),
(64, NULL, 82, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(65, NULL, 83, '220.00', 'PHP', 'cod', 'pending', NULL, NULL),
(66, NULL, 84, '220.00', 'PHP', 'cod', 'pending', NULL, NULL),
(67, NULL, 85, '1200.00', 'PHP', 'cod', 'pending', NULL, NULL),
(68, NULL, 86, '1380.00', 'PHP', 'cod', 'pending', NULL, NULL),
(69, NULL, 87, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(70, NULL, 88, '310.00', 'PHP', 'cod', 'pending', NULL, NULL),
(71, NULL, 89, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(72, NULL, 90, '1060.00', 'PHP', 'cod', 'pending', NULL, NULL),
(73, NULL, 91, '220.00', 'PHP', 'cod', 'pending', NULL, NULL),
(74, NULL, 92, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(75, NULL, 93, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(76, NULL, 94, '420.00', 'PHP', 'cod', 'pending', NULL, NULL),
(77, NULL, 95, '420.00', 'PHP', 'cod', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `PRICE`
--

CREATE TABLE `PRICE` (
  `PRICE_ID` int(11) NOT NULL,
  `PRODUCER_ID` int(11) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `PRICE` varchar(255) DEFAULT NULL,
  `PER` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) NOT NULL DEFAULT 'active',
  `STOCK` int(11) NOT NULL DEFAULT 0,
  `TRAY_SIZE` int(11) NOT NULL DEFAULT 30,
  `DATE_CREATED` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `PRICE`
--

INSERT INTO `PRICE` (`PRICE_ID`, `PRODUCER_ID`, `TYPE`, `PRICE`, `PER`, `STATUS`, `STOCK`, `TRAY_SIZE`, `DATE_CREATED`) VALUES
(1, 1, 'Standard Eggs', '210.00', 'per tray', 'active', 20640, 30, '2025-10-06 11:22:18'),
(2, 1, 'Jumbo Eggs', '240.00', 'per tray', 'active', 22110, 30, '2025-10-06 11:22:18'),
(3, 2, 'Native Eggs', '280.00', 'per tray', 'active', 1080, 30, '2025-10-06 11:22:18'),
(4, 2, 'Free-Range Eggs', '300.00', 'per tray', 'active', 2550, 30, '2025-10-06 11:22:18'),
(5, 3, 'Golden Yolks', '220.00', 'per tray', 'active', 2010, 30, '2025-10-06 11:22:18'),
(6, 4, 'Fresh Brown Eggs', '215', 'per tray', 'active', 816, 12, '2025-10-06 11:22:18'),
(7, 4, 'Pidan/Century Eggs', '350.00', 'per tray', 'active', 9390, 30, '2025-10-06 11:22:18'),
(8, 5, 'Pasture-Raised Eggs', '320.00', 'per tray', 'active', 1110, 30, '2025-10-06 11:22:18'),
(9, 6, 'White Eggs (Medium)', '190.00', 'per tray', 'active', 3420, 30, '2025-10-06 11:22:18'),
(10, 6, 'White Eggs (Large)', '205', 'per tray', 'active', 2970, 30, '2025-10-06 11:22:18'),
(11, 7, 'Salted Eggs', '250', 'per tray', 'active', 330, 30, '2025-10-06 11:22:18'),
(12, 8, 'Itik/Ducks Eggs', '290', 'per tray', 'active', 3300, 30, '2025-10-06 11:22:18'),
(13, 5, 'Test Egg', '210', 'per tray', 'active', 2670, 30, '2025-10-06 11:22:18'),
(14, 1, 'Test Egg2211', '6', 'per egg', 'active', 3000, 30, '2025-10-06 11:22:18'),
(16, 8, 'testegg4', '134', 'tray', 'active', 96, 12, '2025-10-07 12:04:34'),
(17, 5, 'TestEgg Ver 6.1', '120', 'Tray', 'active', 1058, 12, '2025-10-07 21:14:33'),
(18, 7, 'Test Egg 7', '121', 'Per Tray', 'active', 1440, 12, '2025-10-08 08:10:13');

-- --------------------------------------------------------

--
-- Table structure for table `PRODUCER`
--

CREATE TABLE `PRODUCER` (
  `PRODUCER_ID` int(11) NOT NULL,
  `NAME` varchar(255) NOT NULL,
  `LOCATION` varchar(255) DEFAULT NULL,
  `LOGO` varchar(1024) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `PRODUCER`
--

INSERT INTO `PRODUCER` (`PRODUCER_ID`, `NAME`, `LOCATION`, `LOGO`, `URL`) VALUES
(1, 'San Miguel Egg Farm', 'Bulacan, Philippines', 'https://scontent.fmnl3-2.fna.fbcdn.net/v/t39.30808-6/309197041_397533832570608_2852504124934330080_n.jpg', 'https://www.facebook.com/sanmiguelgamefarm'),
(2, 'Kota Paradiso Agricultural Farm', 'Laguna, Philippines', 'https://i.imgur.com/example.png', 'https://kotaparadisofarm.ph/products/native-egg'),
(3, 'Golden Yolks Farm', 'Laguna, Philippines', 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/448766456_122153540054220120_2192821754871017603_n.jpg', 'https://www.facebook.com'),
(4, 'FreshNest Poultry', 'Pampanga, Philippines', 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/536278500_771779282457560_1891929734049571049_n.jpg', 'https://www.facebook.com/FreshNestFarmPH'),
(5, 'Happy Hen Farms', 'Quezon, Philippines', 'https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/326506021_494812559393671_6721513954783849887_n.jpg', 'https://www.facebook.com/happyhenphilippines'),
(6, 'Eggcellent Layers', 'Cavite, Philippines', 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/275662495_153406203790612_5611612134323118829_n.jpg', 'https://www.facebook.com/EGGCELLENTBUSINESS'),
(7, 'SunnySide Egg Farm', 'Pasig, Philippines', 'https://scontent.fmnl37-1.fna.fbcdn.net/v/t39.30808-6/244206017_101518792307481_6975821136247613608_n.jpg', 'https://www.facebook.com/profile.php?id=100082500728747'),
(8, 'FST Egg Store', 'Iloilo, Philippines', 'https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/333611225_578136657594857_8081151375127004928_n.jpg', 'https://www.facebook.com/fst.eggstore');

-- --------------------------------------------------------

--
-- Table structure for table `product_orders`
--

CREATE TABLE `product_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled','failed','completed','Awaiting Replacement','Replacement Issued') NOT NULL DEFAULT 'pending',
  `shipping_address_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'card',
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_orders`
--

INSERT INTO `product_orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `delivery_fee`, `status`, `shipping_address_id`, `payment_method`, `payment_id`, `created_at`, `updated_at`, `paypal_order_id`, `cancellation_reason`, `vehicle_type`, `vehicle_id`, `coupon_code`, `notes`) VALUES
(1, 8, '2025-10-04 02:22:36', '205.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 09:22:36', '2025-10-04 10:05:31', '6', NULL, NULL, NULL, NULL, NULL),
(2, 8, '2025-10-04 02:34:40', '205.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 09:34:40', '2025-10-04 10:05:29', '7', NULL, NULL, NULL, NULL, NULL),
(3, 8, '2025-10-04 02:39:50', '205.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 09:39:50', '2025-10-04 10:05:27', '6', NULL, NULL, NULL, NULL, NULL),
(4, 8, '2025-10-04 02:44:35', '205.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 09:44:35', '2025-10-04 10:05:25', '9', NULL, NULL, NULL, NULL, NULL),
(5, 8, '2025-10-04 03:43:54', '205.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 10:43:54', '2025-10-04 10:54:55', '85', NULL, NULL, NULL, NULL, NULL),
(6, 8, '2025-10-04 03:54:48', '2665.00', '0.00', 'cancelled', 1, 'card', NULL, '2025-10-04 10:54:48', '2025-10-04 10:54:57', '57', NULL, NULL, NULL, NULL, NULL),
(7, 8, '2025-10-04 05:31:13', '205.00', '0.00', 'cancelled', 1, 'card', 1, '2025-10-04 12:31:13', '2025-10-04 15:15:17', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 8, '2025-10-04 05:36:22', '205.00', '0.00', 'cancelled', 1, 'card', 2, '2025-10-04 12:36:22', '2025-10-04 12:36:51', '1UB14343G42883930', NULL, NULL, NULL, NULL, NULL),
(9, 8, '2025-10-04 05:37:55', '615.00', '0.00', 'cancelled', 1, 'card', 3, '2025-10-04 12:37:55', '2025-10-04 15:15:12', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 7, '2025-10-04 07:44:11', '2310.00', '0.00', 'cancelled', 3, 'card', 4, '2025-10-04 14:44:11', '2025-10-04 14:59:53', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 7, '2025-10-04 07:44:23', '205.00', '0.00', 'cancelled', 3, 'card', 5, '2025-10-04 14:44:23', '2025-10-04 14:59:50', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 7, '2025-10-04 07:44:34', '205.00', '0.00', 'cancelled', 3, 'card', 6, '2025-10-04 14:44:34', '2025-10-04 14:59:47', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 7, '2025-10-04 07:45:26', '205.00', '0.00', 'cancelled', 3, 'card', 7, '2025-10-04 14:45:26', '2025-10-04 14:59:44', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 7, '2025-10-04 08:02:59', '205.00', '0.00', 'cancelled', 3, 'card', 8, '2025-10-04 15:02:59', '2025-10-04 15:03:40', '3NC28549W2543104C', NULL, NULL, NULL, NULL, NULL),
(15, 7, '2025-10-04 08:03:13', '205.00', '0.00', '', 3, 'cod', NULL, '2025-10-04 15:03:13', '2025-10-04 15:03:13', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 7, '2025-10-04 08:03:24', '205.00', '0.00', '', 3, 'cod', NULL, '2025-10-04 15:03:24', '2025-10-04 15:03:24', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 7, '2025-10-04 08:03:34', '205.00', '0.00', '', 3, 'cod', NULL, '2025-10-04 15:03:34', '2025-10-04 15:03:34', NULL, NULL, NULL, NULL, NULL, NULL),
(18, 7, '2025-10-04 08:03:58', '205.00', '0.00', '', 3, 'cod', NULL, '2025-10-04 15:03:58', '2025-10-04 15:03:58', NULL, NULL, NULL, NULL, NULL, NULL),
(19, 8, '2025-10-04 08:14:11', '205.00', '0.00', '', 1, 'cod', NULL, '2025-10-04 15:14:11', '2025-10-04 15:14:11', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 8, '2025-10-04 08:14:31', '205.00', '0.00', '', 1, 'cod', NULL, '2025-10-04 15:14:31', '2025-10-04 15:14:31', NULL, NULL, NULL, NULL, NULL, NULL),
(21, 8, '2025-10-04 08:14:56', '205.00', '0.00', '', 1, 'cod', NULL, '2025-10-04 15:14:56', '2025-10-04 15:14:56', NULL, NULL, NULL, NULL, NULL, NULL),
(23, 9, '2025-10-05 08:42:14', '210.00', '0.00', 'cancelled', 6, 'card', 10, '2025-10-05 15:42:14', '2025-10-06 12:16:47', '5M619307HM508061X', NULL, NULL, NULL, NULL, NULL),
(24, 9, '2025-10-05 23:09:46', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:09:46', '2025-10-06 12:16:50', NULL, NULL, NULL, NULL, NULL, NULL),
(25, 9, '2025-10-05 23:15:10', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:15:10', '2025-10-06 12:16:52', NULL, NULL, 'Motorcycle', 4, NULL, NULL),
(26, 9, '2025-10-05 23:15:56', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:15:56', '2025-10-06 12:16:55', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(27, 9, '2025-10-05 23:17:00', '420.00', '0.00', 'cancelled', 6, 'card', 11, '2025-10-06 06:17:00', '2025-10-06 12:17:01', '84D63581W5041242E', NULL, NULL, NULL, NULL, NULL),
(28, 9, '2025-10-05 23:17:49', '210.00', '0.00', 'cancelled', 6, 'card', 12, '2025-10-06 06:17:49', '2025-10-07 07:19:19', '2TM72684J1992790V', NULL, NULL, 5, NULL, NULL),
(29, 9, '2025-10-05 23:20:04', '2460.00', '0.00', 'delivered', 6, 'cod', NULL, '2025-10-06 06:20:04', '2025-10-07 08:39:25', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(30, 9, '2025-10-05 23:22:59', '6.00', '0.00', '', 6, 'card', 13, '2025-10-06 06:22:59', '2025-10-06 18:18:03', '78K33151N9884040P', NULL, NULL, NULL, NULL, NULL),
(31, 9, '2025-10-05 23:23:29', '6.00', '0.00', '', 6, 'cod', NULL, '2025-10-06 06:23:29', '2025-10-06 18:17:47', NULL, NULL, 'Car', 8, NULL, NULL),
(32, 9, '2025-10-06 04:56:50', '320.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 11:56:50', '2025-10-06 18:08:28', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(33, 9, '2025-10-06 05:16:06', '320.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 12:16:06', '2025-10-07 08:28:18', NULL, NULL, 'Motorcycle', 3, NULL, NULL),
(70, 19, '2025-10-07 21:49:16', '1060.00', '100.00', 'delivered', 14, 'card', 52, '2025-10-08 04:49:16', '2025-10-08 04:50:12', NULL, NULL, 'Motorcycle', 5, NULL, ''),
(45, 16, '2025-10-07 01:36:17', '3300.00', '100.00', 'delivered', 11, 'card', 23, '2025-10-07 08:36:17', '2025-10-07 08:42:18', '7SH29966Y8093845U', NULL, 'Motorcycle', 2, NULL, NULL),
(46, 16, '2025-10-07 01:49:25', '420.00', '100.00', 'cancelled', 11, 'card', 24, '2025-10-07 08:49:25', '2025-10-07 08:49:35', '3MA500343D109353L', NULL, 'Motorcycle', NULL, NULL, NULL),
(47, 16, '2025-10-07 01:51:39', '290.00', '100.00', 'delivered', 11, 'card', 25, '2025-10-07 08:51:39', '2025-10-07 09:04:43', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(49, 16, '2025-10-07 02:19:01', '3300.00', '100.00', 'delivered', 11, 'card', 27, '2025-10-07 09:19:01', '2025-10-07 09:19:47', '151970546B292060Y', NULL, 'Motorcycle', NULL, NULL, NULL),
(50, 16, '2025-10-07 02:28:29', '63200.00', '200.00', 'cancelled', 11, 'card', 28, '2025-10-07 09:28:29', '2025-10-07 09:40:51', '92N23835SR736011H', NULL, 'Truck', NULL, NULL, NULL),
(51, 16, '2025-10-07 03:13:12', '3300.00', '100.00', '', 11, 'card', 29, '2025-10-07 10:13:12', '2025-10-07 10:34:23', '9NG02769X7065625W', NULL, 'Motorcycle', NULL, NULL, NULL),
(52, 16, '2025-10-07 03:36:27', '1125.00', '100.00', '', 11, 'card', 30, '2025-10-07 10:36:27', '2025-10-07 10:37:09', '8S837271MA081681R', NULL, 'Motorcycle', NULL, NULL, NULL),
(69, 15, '2025-10-07 21:44:35', '1540.00', '100.00', 'delivered', 10, 'card', 51, '2025-10-08 04:44:35', '2025-10-08 05:14:17', NULL, NULL, 'Motorcycle', NULL, 'RETURN-68E5EBE323B54', ''),
(54, 16, '2025-10-07 03:43:57', '186.00', '150.00', 'delivered', 11, 'card', 32, '2025-10-07 10:43:57', '2025-10-07 10:46:50', '09070277VL093634R', NULL, 'Car', 6, NULL, NULL),
(55, 16, '2025-10-07 03:49:19', '162.00', '150.00', '', 11, 'card', 33, '2025-10-07 10:49:19', '2025-10-07 10:55:17', '8U211686AT916030W', NULL, 'Car', 6, NULL, NULL),
(56, 16, '2025-10-07 03:56:40', '360.00', '150.00', 'cancelled', 11, 'card', 34, '2025-10-07 10:56:40', '2025-10-08 09:45:09', '4XE87685J6316111X', NULL, 'Car', NULL, NULL, NULL),
(57, 16, '2025-10-07 03:57:53', '360.00', '150.00', 'cancelled', 11, 'card', 35, '2025-10-07 10:57:53', '2025-10-08 09:45:06', '31E75124EV231600R', NULL, 'Car', NULL, NULL, NULL),
(68, 15, '2025-10-07 21:40:43', '580.00', '100.00', 'delivered', 10, 'card', 50, '2025-10-08 04:40:43', '2025-10-08 04:41:46', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(67, 15, '2025-10-07 14:12:26', '150.00', '150.00', 'cancelled', 10, 'card', 49, '2025-10-07 21:12:26', '2025-10-07 21:25:15', '8E172480C3755453A', NULL, 'Car', NULL, 'RETURN-68E57E7740A6B', ''),
(62, 19, '2025-10-07 11:10:39', '790.00', '150.00', 'delivered', 14, 'card', 40, '2025-10-07 18:10:39', '2025-10-07 20:50:50', NULL, NULL, 'Car', NULL, NULL, NULL),
(66, 15, '2025-10-07 13:51:22', '470.00', '150.00', 'delivered', 10, 'card', 48, '2025-10-07 20:51:22', '2025-10-07 20:51:32', NULL, NULL, 'Car', NULL, NULL, ''),
(65, 15, '2025-10-07 13:47:33', '790.00', '150.00', 'delivered', 10, 'card', 47, '2025-10-07 20:47:33', '2025-10-07 20:48:20', NULL, NULL, 'Car', 7, NULL, ''),
(71, 15, '2025-10-07 22:05:11', '420.00', '100.00', 'delivered', 10, 'card', 53, '2025-10-08 05:05:11', '2025-10-08 05:06:53', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(72, 19, '2025-10-07 22:10:52', '310.00', '100.00', 'delivered', 14, 'card', 54, '2025-10-08 05:10:52', '2025-10-08 05:10:57', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(73, 19, '2025-10-07 22:12:38', '210.00', '100.00', 'delivered', 14, 'card', 55, '2025-10-08 05:12:38', '2025-10-08 06:13:36', NULL, NULL, 'Motorcycle', NULL, 'RETURN-68E5F279C17C1', ''),
(75, 20, '2025-10-07 23:03:21', '8350.00', '150.00', 'delivered', 15, 'card', 57, '2025-10-08 06:03:21', '2025-10-08 06:11:28', NULL, NULL, 'Car', 6, NULL, 'WAG PO SI PATRICK CUEVAS UNG MAG DEDELIVERY HAGIS KO SAKANYA UNG ITLOG'),
(76, 19, '2025-10-07 23:07:14', '14690.00', '200.00', 'delivered', 14, 'card', 58, '2025-10-08 06:07:14', '2025-10-08 06:11:23', NULL, NULL, 'Truck', 11, NULL, ''),
(77, 21, '2025-10-07 23:09:28', '730.00', '100.00', 'delivered', 16, 'card', 59, '2025-10-08 06:09:28', '2025-10-08 06:13:12', NULL, NULL, 'Motorcycle', NULL, NULL, 'paki ingatan po itlog q ser'),
(94, 11, '2025-10-09 06:07:22', '420.00', '100.00', 'cancelled', 5, 'card', 76, '2025-10-09 13:07:22', '2025-10-09 13:32:08', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(78, 15, '2025-10-07 23:18:34', '630.00', '100.00', 'delivered', 10, 'card', 60, '2025-10-08 06:18:34', '2025-10-08 07:53:59', '53U79929FE9975010', NULL, 'Motorcycle', NULL, NULL, ''),
(79, 16, '2025-10-08 01:46:56', '310.00', '100.00', 'delivered', 11, 'card', 61, '2025-10-08 08:46:56', '2025-10-08 08:49:11', NULL, NULL, 'Motorcycle', 1, NULL, ''),
(80, 16, '2025-10-08 02:41:48', '470.00', '150.00', 'cancelled', 11, 'card', 62, '2025-10-08 09:41:48', '2025-10-08 09:44:56', '80S29799L8289501R', NULL, 'Car', NULL, NULL, ''),
(81, 16, '2025-10-08 02:46:33', '290.00', '100.00', 'delivered', 11, 'card', 63, '2025-10-08 09:46:33', '2025-10-08 10:29:23', NULL, NULL, 'Motorcycle', 1, NULL, 'fjaipfghpia'),
(82, 19, '2025-10-08 20:00:49', '420.00', '100.00', 'delivered', 14, 'card', 64, '2025-10-09 03:00:49', '2025-10-09 10:12:02', NULL, NULL, 'Motorcycle', 2, NULL, ''),
(92, 11, '2025-10-09 05:31:27', '420.00', '100.00', 'delivered', 5, 'card', 74, '2025-10-09 12:31:27', '2025-10-09 12:40:45', NULL, NULL, 'Motorcycle', 1, NULL, ''),
(91, 11, '2025-10-09 04:50:56', '220.00', '100.00', 'cancelled', 5, 'card', 73, '2025-10-09 11:50:56', '2025-10-09 12:31:52', NULL, NULL, 'Motorcycle', 1, NULL, ''),
(85, 9, '2025-10-09 02:56:42', '1200.00', '150.00', 'delivered', 6, 'card', 67, '2025-10-09 09:56:42', '2025-10-09 10:12:35', NULL, NULL, 'Car', NULL, NULL, ''),
(86, 9, '2025-10-09 03:00:14', '1380.00', '100.00', 'delivered', 6, 'card', 68, '2025-10-09 10:00:14', '2025-10-09 10:11:04', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(87, 16, '2025-10-09 03:05:51', '420.00', '100.00', 'delivered', 11, 'card', 69, '2025-10-09 10:05:51', '2025-10-09 10:10:54', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(88, 9, '2025-10-09 03:13:34', '310.00', '100.00', '', 6, 'card', 70, '2025-10-09 10:13:34', '2025-10-09 10:16:51', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(89, 9, '2025-10-09 03:28:37', '420.00', '100.00', 'delivered', 6, 'card', 71, '2025-10-09 10:28:37', '2025-10-09 12:53:07', NULL, NULL, 'Motorcycle', 1, NULL, ''),
(90, 9, '2025-10-09 03:32:03', '1060.00', '100.00', 'delivered', 6, 'card', 72, '2025-10-09 10:32:03', '2025-10-09 10:32:20', NULL, NULL, 'Motorcycle', NULL, NULL, ''),
(95, 11, '2025-10-09 06:28:21', '420.00', '100.00', 'shipped', 5, 'card', 77, '2025-10-09 13:28:21', '2025-10-09 13:32:26', NULL, NULL, 'Motorcycle', 1, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `product_order_items`
--

CREATE TABLE `product_order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `producer_id` int(11) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_item` decimal(10,2) NOT NULL,
  `is_reviewed` tinyint(1) NOT NULL DEFAULT 0,
  `tray_size` int(11) NOT NULL DEFAULT 30
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_order_items`
--

INSERT INTO `product_order_items` (`order_item_id`, `order_id`, `producer_id`, `product_type`, `quantity`, `price_per_item`, `is_reviewed`, `tray_size`) VALUES
(1, 1, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(2, 2, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(3, 3, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(4, 4, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(5, 5, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(6, 6, 6, 'White Eggs (Large)', 13, '205.00', 0, 30),
(7, 7, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(8, 8, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(9, 9, 6, 'White Eggs (Large)', 3, '205.00', 0, 30),
(10, 10, 5, 'Test Egg', 11, '210.00', 0, 30),
(11, 11, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(12, 12, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(13, 13, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(14, 14, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(15, 15, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(16, 16, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(17, 17, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(18, 18, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(19, 19, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(20, 20, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(21, 21, 6, 'White Eggs (Large)', 1, '205.00', 0, 30),
(22, 22, 5, 'Test Egg', 1, '210.00', 0, 30),
(23, 23, 5, 'Test Egg', 1, '210.00', 0, 30),
(24, 24, 5, 'Test Egg', 1, '210.00', 0, 30),
(25, 25, 5, 'Test Egg', 1, '210.00', 0, 30),
(26, 26, 5, 'Test Egg', 1, '210.00', 0, 30),
(27, 27, 5, 'Test Egg', 2, '210.00', 0, 30),
(28, 28, 5, 'Test Egg', 1, '210.00', 0, 30),
(29, 29, 6, 'White Eggs (Large)', 12, '205.00', 0, 30),
(30, 30, 1, 'Test Egg2', 1, '6.00', 0, 30),
(31, 31, 1, 'Test Egg2', 1, '6.00', 0, 30),
(32, 32, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(33, 33, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(34, 34, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(35, 35, 6, 'White Eggs (Medium)', 1, '190.00', 0, 30),
(36, 36, 1, 'Jumbo Eggs', 11, '240.00', 0, 30),
(37, 37, 5, 'Pasture-Raised Eggs', 1, '320.00', 1, 30),
(38, 38, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(39, 39, 5, 'Pasture-Raised Eggs', 21, '320.00', 1, 30),
(40, 40, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(41, 42, 5, 'Test Egg', 1, '210.00', 0, 30),
(42, 43, 5, 'Pasture-Raised Eggs', 1, '320.00', 1, 30),
(43, 44, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(44, 45, 5, 'Pasture-Raised Eggs', 10, '320.00', 1, 30),
(45, 46, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(46, 47, 6, 'White Eggs (Medium)', 1, '190.00', 1, 30),
(47, 48, 5, 'Test Egg', 1, '210.00', 0, 30),
(48, 49, 5, 'Pasture-Raised Eggs', 10, '320.00', 0, 30),
(49, 50, 1, 'Standard Eggs', 300, '210.00', 0, 30),
(50, 51, 5, 'Pasture-Raised Eggs', 10, '320.00', 0, 30),
(51, 52, 6, 'White Eggs (Large)', 5, '205.00', 0, 30),
(52, 53, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(53, 54, 1, 'Test Egg2211', 6, '6.00', 0, 30),
(54, 55, 1, 'Test Egg2211', 2, '6.00', 0, 30),
(55, 56, 5, 'Test Egg', 1, '210.00', 0, 30),
(56, 57, 5, 'Test Egg', 1, '210.00', 0, 30),
(57, 58, 8, 'testegg4', 2, '67.00', 0, 12),
(58, 59, 6, 'White Eggs (Large)', 2, '205.00', 0, 30),
(59, 60, 5, 'Pasture-Raised Eggs', 2, '160.00', 0, 12),
(60, 61, 5, 'Pasture-Raised Eggs', 2, '160.00', 0, 12),
(61, 62, 5, 'Pasture-Raised Eggs', 2, '320.00', 0, 30),
(62, 63, 8, 'testegg4', 1, '134.00', 0, 12),
(63, 64, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(64, 65, 5, 'Pasture-Raised Eggs', 2, '320.00', 0, 30),
(65, 66, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(66, 67, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(67, 68, 5, 'TestEgg Ver 6.1', 4, '120.00', 0, 12),
(68, 69, 5, 'Pasture-Raised Eggs', 6, '320.00', 0, 30),
(69, 70, 5, 'TestEgg Ver 6.1', 8, '120.00', 0, 12),
(70, 71, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(71, 72, 5, 'Test Egg', 1, '210.00', 0, 30),
(72, 73, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(73, 74, 5, 'TestEgg Ver 6.1', 1, '120.00', 1, 12),
(74, 75, 6, 'White Eggs (Large)', 40, '205.00', 0, 30),
(75, 76, 1, 'Standard Eggs', 69, '210.00', 1, 30),
(76, 77, 6, 'White Eggs (Large)', 2, '205.00', 0, 30),
(77, 77, 3, 'Golden Yolks', 1, '220.00', 0, 30),
(78, 78, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(79, 78, 5, 'Test Egg', 1, '210.00', 0, 30),
(80, 79, 5, 'Test Egg', 1, '210.00', 0, 30),
(81, 80, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(82, 81, 6, 'White Eggs (Medium)', 1, '190.00', 0, 30),
(83, 82, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(84, 83, 5, 'TestEgg Ver 6.1', 1, '120.00', 0, 12),
(85, 84, 5, 'TestEgg Ver 6.1', 1, '120.00', 0, 12),
(86, 85, 5, 'Test Egg', 5, '210.00', 0, 30),
(87, 86, 5, 'Pasture-Raised Eggs', 4, '320.00', 0, 30),
(88, 87, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(89, 88, 5, 'Test Egg', 1, '210.00', 0, 30),
(90, 89, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(91, 90, 5, 'Pasture-Raised Eggs', 3, '320.00', 0, 30),
(92, 91, 5, 'TestEgg Ver 6.1', 1, '120.00', 0, 12),
(93, 92, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(94, 93, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(95, 94, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30),
(96, 95, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30);

-- --------------------------------------------------------

--
-- Table structure for table `product_returns`
--

CREATE TABLE `product_returns` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `return_reason` text DEFAULT NULL,
  `return_status` enum('requested','approved','rejected','processing','completed') NOT NULL DEFAULT 'requested',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_item_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`review_id`, `user_id`, `order_id`, `product_type`, `rating`, `review_text`, `created_at`, `order_item_id`) VALUES
(1, 9, 23, 'Test Egg', 5, '', '2025-10-05 16:53:18', NULL),
(2, 11, 37, 'Pasture-Raised Eggs', 2, 'ass', '2025-10-06 13:00:54', 37),
(3, 11, 39, 'Pasture-Raised Eggs', 4, 'meh', '2025-10-06 13:28:37', 39),
(4, 11, 43, 'Pasture-Raised Eggs', 4, 'hehehaha', '2025-10-06 19:24:06', 42),
(5, 16, 45, 'Pasture-Raised Eggs', 5, 'GOOD QUALITY', '2025-10-07 08:44:26', 44),
(6, 16, 47, 'White Eggs (Medium)', 4, 'GOOD SH**', '2025-10-07 09:05:16', 46),
(7, 19, 76, 'Standard Eggs', 4, 'Is Aight', '2025-10-08 06:12:27', 75),
(8, 11, 74, 'TestEgg Ver 6.1', 5, 'Heeheehaahaa', '2025-10-08 06:14:23', 73);

-- --------------------------------------------------------

--
-- Table structure for table `Rate_Card`
--

CREATE TABLE `Rate_Card` (
  `rate_id` int(11) NOT NULL,
  `base_fee` decimal(10,2) DEFAULT NULL,
  `per_km_fee` decimal(10,2) DEFAULT NULL,
  `per_tray_fee` decimal(10,2) DEFAULT NULL,
  `surcharge_peak` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `restock_processed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `order_id`, `order_item_id`, `user_id`, `product_id`, `reason`, `status`, `requested_at`, `updated_at`, `image_path`, `approved_at`, `restock_processed`) VALUES
(1, 22, 22, 0, 0, 'dwadaw', 'approved', '2025-10-06 07:14:17', '2025-10-06 18:18:06', NULL, NULL, 0),
(2, 30, 30, 0, 0, 'test', 'approved', '2025-10-06 08:06:31', '2025-10-06 18:18:03', NULL, NULL, 0),
(3, 31, 31, 0, 0, 'test notif', 'approved', '2025-10-06 08:49:10', '2025-10-06 18:17:52', NULL, NULL, 0),
(4, 38, 38, 0, 0, 'broekn egguh', 'approved', '2025-10-06 13:02:31', '2025-10-06 18:17:40', NULL, NULL, 0),
(5, 42, 41, 0, 0, 'Bobo packaging', 'approved', '2025-10-06 18:33:53', '2025-10-06 18:35:38', NULL, NULL, 0),
(6, 49, 48, 0, 0, 'bulok na yung tatlo awit never again', 'rejected', '2025-10-07 09:20:18', '2025-10-07 09:33:21', NULL, NULL, 0),
(7, 51, 50, 0, 0, 'crack yung egg', 'approved', '2025-10-07 10:15:42', '2025-10-07 10:34:23', NULL, NULL, 0),
(8, 52, 51, 0, 0, 'meh', 'approved', '2025-10-07 10:37:01', '2025-10-07 10:37:09', NULL, NULL, 0),
(9, 53, 52, 0, 0, 'test nio redfund', 'rejected', '2025-10-07 10:42:23', '2025-10-07 10:46:38', NULL, NULL, 0),
(10, 55, 54, 0, 0, 'crack po yung dalawa', 'approved', '2025-10-07 10:50:58', '2025-10-07 10:55:17', NULL, NULL, 0),
(11, 58, 57, 15, 16, 'Damaged in transit', 'rejected', '2025-10-07 17:07:09', '2025-10-07 17:09:02', 'uploads/return_68e548bd103968.01356019_mambo.jpg', NULL, 0),
(12, 59, 58, 11, 10, 'Damaged in transit', 'rejected', '2025-10-07 17:21:46', '2025-10-07 17:22:17', 'uploads/return_68e54c2a86f375.22197803_Screenshot 2024-11-06 222649.png', NULL, 0),
(13, 63, 62, 15, 16, 'Damaged in transit', 'approved', '2025-10-07 20:33:35', '2025-10-09 01:31:43', 'uploads/412007e1a4027efed7d275ec84a84448.jpg', '2025-10-07 13:34:03', 1),
(14, 65, 64, 15, 8, 'Damaged in transit', 'approved', '2025-10-07 20:48:51', '2025-10-09 01:31:43', 'uploads/95fe722cf85d8f9cf7ed7a3694d3bf5d.jpg', '2025-10-07 13:49:09', 1),
(15, 66, 65, 15, 8, 'Damaged in transit', 'approved', '2025-10-07 20:56:11', '2025-10-09 01:31:43', 'uploads/77fabac44f71034e5a540094d5b6515c.jpg', '2025-10-07 13:56:23', 1),
(16, 68, 67, 15, 17, 'Damaged in transit', 'approved', '2025-10-08 04:42:58', '2025-10-09 05:26:01', 'uploads/7eaf1aadc3472a13daeabd8681860553.jpg', '2025-10-07 21:43:15', 1),
(17, 70, 69, 19, 17, 'Quality not as expected', 'rejected', '2025-10-08 04:50:54', '2025-10-08 04:52:37', NULL, NULL, 0),
(18, 62, 61, 19, 8, 'Item is expired', 'approved', '2025-10-08 05:08:53', '2025-10-09 05:26:01', NULL, '2025-10-07 22:09:12', 1),
(19, 72, 71, 19, 13, 'Item is expired', 'approved', '2025-10-08 05:11:10', '2025-10-09 05:26:01', NULL, '2025-10-07 22:11:21', 1),
(20, 71, 70, 15, 8, 'Item is expired', 'approved', '2025-10-08 05:14:35', '2025-10-09 05:26:01', NULL, '2025-10-07 22:14:58', 1),
(21, 75, 74, 20, 10, 'Item is expired', 'approved', '2025-10-08 06:12:18', '2025-10-09 06:44:37', NULL, '2025-10-07 23:14:44', 1),
(22, 79, 80, 16, 13, 'Item is expired', 'rejected', '2025-10-08 08:51:41', '2025-10-08 08:52:01', NULL, NULL, 0),
(23, 89, 90, 9, 8, 'Item is expired', 'approved', '2025-10-09 10:30:08', '2025-10-09 10:30:32', NULL, '2025-10-09 03:30:32', 0),
(24, 90, 91, 9, 8, 'Wrong item delivered', 'approved', '2025-10-09 10:32:50', '2025-10-09 10:33:07', NULL, '2025-10-09 03:33:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `USER`
--

CREATE TABLE `USER` (
  `USER_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) NOT NULL,
  `MIDDLE_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PHONE` varchar(20) DEFAULT NULL,
  `PROFILE_PICTURE` varchar(255) DEFAULT 'default_avatar.png',
  `PASSWORD` varchar(255) NOT NULL,
  `ROLE` enum('driver','admin','customer') DEFAULT 'customer',
  `ACCOUNT_STATUS` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `LOCK_EXPIRES_AT` datetime DEFAULT NULL,
  `CREATED_AT` timestamp NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `USER`
--

INSERT INTO `USER` (`USER_ID`, `FIRST_NAME`, `MIDDLE_NAME`, `LAST_NAME`, `EMAIL`, `PHONE`, `PROFILE_PICTURE`, `PASSWORD`, `ROLE`, `ACCOUNT_STATUS`, `LOCK_EXPIRES_AT`, `CREATED_AT`, `UPDATED_AT`, `last_login_at`) VALUES
(14, 'SaiSai', 'Middle', 'Aniam', 'saisaianiam@gmail.com', '09956336238', 'default_avatar.png', '$2y$10$K/xn08C7ARa.r2xuoluayufQvb95ckRzsAasLdeGXLoZgd1Csjfaa', 'driver', 'ACTIVE', NULL, '2025-10-06 10:39:50', '2025-10-09 05:57:04', NULL),
(20, 'Lawrence', 'A', 'Villaflor', 'qljavillaflor@tip.edu.ph', '09458830159', 'default_avatar.png', '$2y$10$J.5IJkAeyC1IhZ5PD7G9fudRn6FiGppnRCPZ..vCsKalkMzITC/fq', 'customer', 'ACTIVE', NULL, '2025-10-08 05:58:10', '2025-10-08 05:58:10', NULL),
(11, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.com', '09956336238', 'uploads/profile_pictures/user_11_68e2844c3376f2.15203154.jpg', '$2y$10$QDBf.AQkpUKMQgTu6r4D6OvmdkqzvIkzl0RKeOG092Ff7ILa/uNIu', 'customer', 'ACTIVE', NULL, '2025-10-04 15:18:40', '2025-10-07 17:09:54', '2025-10-07 17:09:54'),
(9, 'Admin', NULL, 'User', 'admin@crackcart.com', '123-456-7890', 'uploads/profile_pictures/user_9_68e38e5c29f866.87247820.jpg', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'ACTIVE', NULL, '2025-10-04 14:27:06', '2025-10-07 08:12:37', NULL),
(21, 'Patrick', 'Alaras', 'Cuevas', 'qplacuevas@tip.edu.ph', '09571788936', 'default_avatar.png', '$2y$10$bqi2.X3DT4f573L0tCUdYeAuSLLG.cYHUieGbAC7PjwYBZol/SlCy', 'customer', 'ACTIVE', NULL, '2025-10-08 06:00:08', '2025-10-08 06:00:08', NULL),
(19, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi.salamat@gmail.com', '09284943131', 'default_avatar.png', '$2y$10$uVORnrTrxCyRn3Feu1IT5eTQYvo638mT.5dlErduZPaC2VWTB6TYi', 'driver', 'ACTIVE', NULL, '2025-10-07 17:50:57', '2025-10-09 07:59:45', '2025-10-09 03:48:51'),
(15, 'Eduard Simon', 'Nemiada', 'Miana', 'qesnmiana@tip.edu.ph', '09956336238', 'uploads/profile_pictures/0c9b5f4b46035ce4a65af0c8c80f0cdb.jpg', '$2y$10$McsvdTCFkZxxE3r5bc.KIumyhHGwJV6eKWAVCtWMs2aFAFpnyY/Qa', 'customer', 'ACTIVE', NULL, '2025-10-06 19:31:59', '2025-10-07 19:48:49', '2025-10-07 19:25:21'),
(16, 'Kenneth Angelo', '', 'Sarmiento', 'sken4165@gmail.com', '09273848395', 'uploads/profile_pictures/e904aef644f105632a91dd8e1f8a8cfb.jpeg', '$2y$10$KCNrCJStJ4XPynwSOfDo.OWtzxF7QVyfrDTxEC4ak1V8pfgC.1Ypy', 'customer', 'ACTIVE', NULL, '2025-10-07 07:15:16', '2025-10-08 09:51:37', '2025-10-07 10:10:25'),
(22, 'Joseph', '', 'Nemiada', 'joseph.nemiada@gmail.com', '5878895297', 'default_avatar.png', '$2y$10$HNAPwCwp4CeIRSc.LKzCIOW1KwDIvxKljn2iiC1Jxw8onltD6GvUS', 'customer', 'ACTIVE', NULL, '2025-10-08 14:02:22', '2025-10-08 14:02:22', NULL),
(10, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.co', '09956336238', 'default_avatar.png', '$2y$10$Zzi2FrqhC90ptvMdxXzPVuHriuSOqReJ/6wAZNt3L0b56eCsnZKga', 'admin', 'ACTIVE', NULL, '2025-10-04 15:18:01', '2025-10-07 21:04:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `address_type` varchar(50) DEFAULT 'shipping',
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`address_id`, `user_id`, `address_line1`, `address_line2`, `city`, `state`, `zip_code`, `country`, `address_type`, `is_default`) VALUES
(1, 8, '9 Mapalad', '', 'Quezon City', 'Metro Manila', '1104', 'Philippines', 'shipping', 0),
(3, 7, '9 Mapalad', '', 'Quezon City', 'Metro Manila', '1104', 'Philippines', 'shipping', 0),
(4, 10, '9 Mapalad', 'Mariblo', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(5, 11, '9 Mapalad', 'Mariblo', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(6, 9, '9 Mapalad', '', 'Quezon City', 'Metro Manila', '1104', 'Philippines', 'shipping', 0),
(7, 12, 'test test', 'test', 'test', 'test', '', 'Philippines', 'shipping', 1),
(8, 13, '138 A Malumanay', 'UP Village', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(9, 14, '9 Mapalad', 'Mariblo', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(10, 15, '9 Mapalad', 'Mariblo', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(11, 16, '245 Purok Silangan', 'Dela Paz', 'Antipolo', 'Antipolo', '', 'Philippines', 'shipping', 1),
(12, 17, '12 Andres', 'Santolan', 'Pasig City', 'Pasig City', '', 'Philippines', 'shipping', 1),
(13, 18, '12 Santolan', 'Santolan', 'Pasig City', 'Pasig City', '', 'Philippines', 'shipping', 1),
(14, 19, '138 A Malumanay', 'UP Village', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1),
(15, 20, '12 Andres', 'Santolan', 'Pasig City', 'Pasig City', '', 'Philippines', 'shipping', 1),
(16, 21, '31 4th West crame', 'West crame', 'San juan city', 'San juan city', '', 'Philippines', 'shipping', 1),
(17, 22, '9 Mapalad St', 'Mariblo', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Vehicle`
--

CREATE TABLE `Vehicle` (
  `vehicle_id` int(11) NOT NULL,
  `plate_no` varchar(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `capacity_trays` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `last_maintenance` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `Vehicle`
--

INSERT INTO `Vehicle` (`vehicle_id`, `plate_no`, `type`, `capacity_trays`, `status`, `last_maintenance`) VALUES
(1, 'MC1021', 'Motorcycle', 10, 'in-transit', NULL),
(2, 'MC2398', 'Motorcycle', 15, 'available', NULL),
(3, 'MC5874', 'Motorcycle', 8, 'available', NULL),
(4, 'MC4862', 'Motorcycle', 12, 'available', NULL),
(5, 'MC3321', 'Motorcycle', 14, 'available', NULL),
(6, 'CAR7781', 'Car', 50, 'available', NULL),
(7, 'CAR1212', 'Car', 45, 'available', NULL),
(8, 'CAR3434', 'Car', 55, 'available', NULL),
(9, 'CAR9087', 'Car', 40, 'available', NULL),
(10, 'CAR5656', 'Car', 48, 'available', NULL),
(11, 'TRK4501', 'Truck', 200, 'available', NULL),
(12, 'TRK3320', 'Truck', 250, 'available', NULL),
(13, 'TRK8791', 'Truck', 300, 'available', NULL),
(14, 'TRK6055', 'Truck', 220, 'available', NULL),
(15, 'TRK1109', 'Truck', 150, 'available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_added` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `vehicle_types`
--

INSERT INTO `vehicle_types` (`type_id`, `type_name`, `delivery_fee`, `date_added`) VALUES
(1, 'Motorcycle', '100.00', '2025-10-06 13:40:07'),
(2, 'Car', '150.00', '2025-10-06 13:40:07'),
(3, 'Truck', '200.00', '2025-10-06 13:40:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archived_users`
--
ALTER TABLE `archived_users`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `Booking`
--
ALTER TABLE `Booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pick_up_address` (`pick_up_address`),
  ADD KEY `drop_off_address` (`drop_off_address`),
  ADD KEY `rate_id` (`rate_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`) USING HASH;

--
-- Indexes for table `Delivery_Assignment`
--
ALTER TABLE `Delivery_Assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `delivery_incidents`
--
ALTER TABLE `delivery_incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `Driver`
--
ALTER TABLE `Driver`
  ADD PRIMARY KEY (`driver_id`),
  ADD KEY `fk_driver_vehicle` (`vehicle_id`);

--
-- Indexes for table `Fleet_Maintenance`
--
ALTER TABLE `Fleet_Maintenance`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `Locations`
--
ALTER TABLE `Locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `NOTIFICATION`
--
ALTER TABLE `NOTIFICATION`
  ADD PRIMARY KEY (`NOTIFICATION_ID`),
  ADD KEY `USER_ID` (`USER_ID`);

--
-- Indexes for table `Payment`
--
ALTER TABLE `Payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `PRICE`
--
ALTER TABLE `PRICE`
  ADD PRIMARY KEY (`PRICE_ID`),
  ADD KEY `PRODUCER_ID` (`PRODUCER_ID`);

--
-- Indexes for table `PRODUCER`
--
ALTER TABLE `PRODUCER`
  ADD PRIMARY KEY (`PRODUCER_ID`);

--
-- Indexes for table `product_orders`
--
ALTER TABLE `product_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_vehicle_id` (`vehicle_id`);

--
-- Indexes for table `product_order_items`
--
ALTER TABLE `product_order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `producer_id` (`producer_id`);

--
-- Indexes for table `product_returns`
--
ALTER TABLE `product_returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `Rate_Card`
--
ALTER TABLE `Rate_Card`
  ADD PRIMARY KEY (`rate_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_returns_order_item` (`order_item_id`);

--
-- Indexes for table `USER`
--
ALTER TABLE `USER`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD PRIMARY KEY (`vehicle_id`);

--
-- Indexes for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archived_users`
--
ALTER TABLE `archived_users`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `Booking`
--
ALTER TABLE `Booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Delivery_Assignment`
--
ALTER TABLE `Delivery_Assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `delivery_incidents`
--
ALTER TABLE `delivery_incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Driver`
--
ALTER TABLE `Driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `Fleet_Maintenance`
--
ALTER TABLE `Fleet_Maintenance`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Locations`
--
ALTER TABLE `Locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `NOTIFICATION`
--
ALTER TABLE `NOTIFICATION`
  MODIFY `NOTIFICATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `Payment`
--
ALTER TABLE `Payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `PRICE`
--
ALTER TABLE `PRICE`
  MODIFY `PRICE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `PRODUCER`
--
ALTER TABLE `PRODUCER`
  MODIFY `PRODUCER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `product_order_items`
--
ALTER TABLE `product_order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `product_returns`
--
ALTER TABLE `product_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Rate_Card`
--
ALTER TABLE `Rate_Card`
  MODIFY `rate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `USER`
--
ALTER TABLE `USER`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Vehicle`
--
ALTER TABLE `Vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
