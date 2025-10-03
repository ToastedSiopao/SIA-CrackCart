-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Oct 03, 2025 at 04:06 PM
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
-- Table structure for table `Address`
--

CREATE TABLE `Address` (
  `address_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `Driver`
--

CREATE TABLE `Driver` (
  `driver_id` int(11) NOT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(6, NULL, 'Happy Holidays from CrackCart! We\'re offering special holiday discounts on all our services.', 0, '2025-09-23 03:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_address_id` int(11) NOT NULL,
  `delivery_address_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `pickup_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `tray_quantity` int(11) NOT NULL DEFAULT 1,
  `tray_size` decimal(10,2) NOT NULL COMMENT 'Size in dimensions or volume',
  `distance_km` decimal(8,2) NOT NULL,
  `quoted_amount` decimal(10,2) NOT NULL,
  `final_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','assigned','picked_up','in_transit','delivered','cancelled') DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `priority` enum('standard','express','urgent') DEFAULT 'standard',
  `estimated_delivery_time` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Payment`
--

CREATE TABLE `Payment` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `paid_at` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PRICE`
--

CREATE TABLE `PRICE` (
  `PRICE_ID` int(11) NOT NULL,
  `PRODUCER_ID` int(11) DEFAULT NULL,
  `TYPE` varchar(255) DEFAULT NULL,
  `PRICE` varchar(255) DEFAULT NULL,
  `PER` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
-- Table structure for table `Tracking_Event`
--

CREATE TABLE `Tracking_Event` (
  `event_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `PASSWORD` varchar(255) NOT NULL,
  `ROLE` enum('driver','admin','customer') DEFAULT 'customer',
  `HOUSE_NO` varchar(20) DEFAULT NULL,
  `STREET_NAME` varchar(100) DEFAULT NULL,
  `BARANGAY` varchar(100) DEFAULT NULL,
  `CITY` varchar(100) DEFAULT NULL,
  `CREATED_AT` timestamp NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `USER`
--

INSERT INTO `USER` (`USER_ID`, `FIRST_NAME`, `MIDDLE_NAME`, `LAST_NAME`, `EMAIL`, `PHONE`, `PASSWORD`, `ROLE`, `HOUSE_NO`, `STREET_NAME`, `BARANGAY`, `CITY`, `CREATED_AT`, `UPDATED_AT`) VALUES
(4, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi@gmail.com', '', '$2y$10$befzVXETI53dfpYMhMBvUeXykYXzrIK96lTix/3QtQFUoLCEZ4zpW', 'customer', '138 A', 'Malumanay', 'UP Village', 'QC', '2025-09-22 12:09:06', '2025-09-22 12:09:06'),
(7, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.com', '09956336238', '$2y$10$jIOLZFqQCAl24js9hYZtle8RcGhKU1ZKApqspK1PxStgb2FoHdRpW', 'customer', '9', 'Mapalad', 'Mariblo', 'Quezon City', '2025-10-03 17:45:48', '2025-10-03 17:45:48'),
(5, 'Crack', 'Nemiada', 'Cart', 'crackcart.auth@gmail.com', '0995 633 6238', '$2y$10$IwU5AvKjNBEncZ8OeXyYTu67a/fKEKo2eYWEuIdRY.HsBhIuSrpFa', 'customer', '9', 'mapalad', 'Mariblo', 'Quezon City', '2025-09-25 08:02:11', '2025-09-25 08:02:11'),
(6, 'q3rq', 'qr3rq', 'qr3rq', 'qkramirez04@tip.edu.ph', '125135135', '$2y$10$ukVSWkly5c4s9UKZZNMPMekPgv.jNIDiSBirIJ5g48KTGElahPAB6', 'customer', '51351', '5135feas', '151fae', '154fdfg', '2025-10-03 08:19:37', '2025-10-03 08:19:37'),
(8, 'Eduard Simon', 'Nemiada', 'Miana', 'qesnmiana@tip.edu.ph', '09956336238', '$2y$10$E0I0s1y/JPV4j7pb6ZNk1ObKdmc99Don4U7bbHhoDKxY5KJxe2P1u', 'customer', '9', 'Mapalad', 'Mariblo', 'simonmiana@gmail.com', '2025-10-03 19:12:17', '2025-10-03 19:12:17');

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
-- Indexes for dumped tables
--

--
-- Indexes for table `Address`
--
ALTER TABLE `Address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `Delivery_Assignment`
--
ALTER TABLE `Delivery_Assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `Driver`
--
ALTER TABLE `Driver`
  ADD PRIMARY KEY (`driver_id`);

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
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `pickup_address_id` (`pickup_address_id`),
  ADD KEY `delivery_address_id` (`delivery_address_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_driver_id` (`driver_id`),
  ADD KEY `idx_order_date` (`order_date`);

--
-- Indexes for table `Payment`
--
ALTER TABLE `Payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

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
-- Indexes for table `Rate_Card`
--
ALTER TABLE `Rate_Card`
  ADD PRIMARY KEY (`rate_id`);

--
-- Indexes for table `Tracking_Event`
--
ALTER TABLE `Tracking_Event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `USER`
--
ALTER TABLE `USER`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- Indexes for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD PRIMARY KEY (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Address`
--
ALTER TABLE `Address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Booking`
--
ALTER TABLE `Booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Delivery_Assignment`
--
ALTER TABLE `Delivery_Assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Driver`
--
ALTER TABLE `Driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `NOTIFICATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Payment`
--
ALTER TABLE `Payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `PRICE`
--
ALTER TABLE `PRICE`
  MODIFY `PRICE_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `PRODUCER`
--
ALTER TABLE `PRODUCER`
  MODIFY `PRODUCER_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Rate_Card`
--
ALTER TABLE `Rate_Card`
  MODIFY `rate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Tracking_Event`
--
ALTER TABLE `Tracking_Event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `USER`
--
ALTER TABLE `USER`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Vehicle`
--
ALTER TABLE `Vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
