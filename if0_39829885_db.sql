-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Oct 04, 2025 at 10:10 AM
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
(3, NULL, 9, '615.00', 'PHP', 'cod', 'pending', NULL, NULL);

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

--
-- Dumping data for table `PRICE`
--

INSERT INTO `PRICE` (`PRICE_ID`, `PRODUCER_ID`, `TYPE`, `PRICE`, `PER`) VALUES
(1, 1, 'Standard Eggs', '210.00', 'per tray'),
(2, 1, 'Jumbo Eggs', '240.00', 'per tray'),
(3, 2, 'Native Eggs', '280.00', 'per tray'),
(4, 2, 'Free-Range Eggs', '300.00', 'per tray'),
(5, 3, 'Golden Yolks', '220.00', 'per tray'),
(6, 4, 'Fresh Brown Eggs', '215.00', 'per tray'),
(7, 4, 'Pidan/Century Eggs', '350.00', 'per tray'),
(8, 5, 'Pasture-Raised Eggs', '320.00', 'per tray'),
(9, 6, 'White Eggs (Medium)', '190.00', 'per tray'),
(10, 6, 'White Eggs (Large)', '205.00', 'per tray'),
(11, 7, 'Salted Eggs', '250.00', 'per tray'),
(12, 8, 'Itik/Ducks Eggs', '290.00', 'per tray');

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
  `status` enum('pending','paid','cancelled','failed') NOT NULL DEFAULT 'pending',
  `shipping_address_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_orders`
--

INSERT INTO `product_orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `shipping_address_id`, `payment_id`, `created_at`, `updated_at`, `paypal_order_id`, `cancellation_reason`) VALUES
(1, 8, '2025-10-04 02:22:36', '205.00', 'cancelled', 1, NULL, '2025-10-04 09:22:36', '2025-10-04 10:05:31', '6', NULL),
(2, 8, '2025-10-04 02:34:40', '205.00', 'cancelled', 1, NULL, '2025-10-04 09:34:40', '2025-10-04 10:05:29', '7', NULL),
(3, 8, '2025-10-04 02:39:50', '205.00', 'cancelled', 1, NULL, '2025-10-04 09:39:50', '2025-10-04 10:05:27', '6', NULL),
(4, 8, '2025-10-04 02:44:35', '205.00', 'cancelled', 1, NULL, '2025-10-04 09:44:35', '2025-10-04 10:05:25', '9', NULL),
(5, 8, '2025-10-04 03:43:54', '205.00', 'cancelled', 1, NULL, '2025-10-04 10:43:54', '2025-10-04 10:54:55', '85', NULL),
(6, 8, '2025-10-04 03:54:48', '2665.00', 'cancelled', 1, NULL, '2025-10-04 10:54:48', '2025-10-04 10:54:57', '57', NULL),
(7, 8, '2025-10-04 05:31:13', '205.00', 'pending', 1, 1, '2025-10-04 12:31:13', '2025-10-04 12:31:13', NULL, NULL),
(8, 8, '2025-10-04 05:36:22', '205.00', 'cancelled', 1, 2, '2025-10-04 12:36:22', '2025-10-04 12:36:51', '1UB14343G42883930', NULL),
(9, 8, '2025-10-04 05:37:55', '615.00', 'pending', 1, 3, '2025-10-04 12:37:55', '2025-10-04 12:37:55', NULL, NULL);

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
  `price_per_item` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_order_items`
--

INSERT INTO `product_order_items` (`order_item_id`, `order_id`, `producer_id`, `product_type`, `quantity`, `price_per_item`) VALUES
(1, 1, 6, 'White Eggs (Large)', 1, '205.00'),
(2, 2, 6, 'White Eggs (Large)', 1, '205.00'),
(3, 3, 6, 'White Eggs (Large)', 1, '205.00'),
(4, 4, 6, 'White Eggs (Large)', 1, '205.00'),
(5, 5, 6, 'White Eggs (Large)', 1, '205.00'),
(6, 6, 6, 'White Eggs (Large)', 13, '205.00'),
(7, 7, 6, 'White Eggs (Large)', 1, '205.00'),
(8, 8, 6, 'White Eggs (Large)', 1, '205.00'),
(9, 9, 6, 'White Eggs (Large)', 3, '205.00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `CREATED_AT` timestamp NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `USER`
--

INSERT INTO `USER` (`USER_ID`, `FIRST_NAME`, `MIDDLE_NAME`, `LAST_NAME`, `EMAIL`, `PHONE`, `PASSWORD`, `ROLE`, `CREATED_AT`, `UPDATED_AT`) VALUES
(4, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi@gmail.com', '', '$2y$10$befzVXETI53dfpYMhMBvUeXykYXzrIK96lTix/3QtQFUoLCEZ4zpW', 'customer', '2025-09-22 12:09:06', '2025-09-22 12:09:06'),
(7, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.com', '09956336238', '$2y$10$jIOLZFqQCAl24js9hYZtle8RcGhKU1ZKApqspK1PxStgb2FoHdRpW', 'customer', '2025-10-03 17:45:48', '2025-10-03 17:45:48'),
(5, 'Crack', 'Nemiada', 'Cart', 'crackcart.auth@gmail.com', '0995 633 6238', '$2y$10$IwU5AvKjNBEncZ8OeXyYTu67a/fKEKo2eYWEuIdRY.HsBhIuSrpFa', 'customer', '2025-09-25 08:02:11', '2025-09-25 08:02:11'),
(6, 'q3rq', 'qr3rq', 'qr3rq', 'qkramirez04@tip.edu.ph', '125135135', '$2y$10$ukVSWkly5c4s9UKZZNMPMekPgv.jNIDiSBirIJ5g48KTGElahPAB6', 'customer', '2025-10-03 08:19:37', '2025-10-03 08:19:37'),
(8, 'Eduard Simo', 'Nemiada', 'Miana', 'qesnmiana@tip.edu.ph', '09956336238', '$2y$10$E0I0s1y/JPV4j7pb6ZNk1ObKdmc99Don4U7bbHhoDKxY5KJxe2P1u', 'customer', '2025-10-03 19:12:17', '2025-10-04 05:23:47');

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
(1, 8, '9 Mapalad', '', 'Quezon City', 'Metro Manila', '1104', 'Philippines', 'shipping', 0);

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
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `product_order_items`
--
ALTER TABLE `product_order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `producer_id` (`producer_id`);

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
-- AUTO_INCREMENT for dumped tables
--

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
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `PRICE`
--
ALTER TABLE `PRICE`
  MODIFY `PRICE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `PRODUCER`
--
ALTER TABLE `PRODUCER`
  MODIFY `PRODUCER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_order_items`
--
ALTER TABLE `product_order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Vehicle`
--
ALTER TABLE `Vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
