-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql101.infinityfree.com
-- Generation Time: Oct 07, 2025 at 04:39 PM
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
(14, 'SaiSai', 'Middle', 'Aniam', 'saisaianiam@gmail.com', '09956336238', 'default_avatar.png', '$2y$10$K/xn08C7ARa.r2xuoluayufQvb95ckRzsAasLdeGXLoZgd1Csjfaa', 'customer', 'ACTIVE', NULL, '2025-10-06 10:39:50', '2025-10-06 10:39:50', NULL);

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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(45, 16, 'Your order #54 status has been updated to \'delivered\'.', 0, '2025-10-07 10:46:50'),
(46, 15, 'Your order #58 status has been updated to \'delivered\'.', 1, '2025-10-07 12:35:43'),
(47, 11, 'Your order #59 status has been updated to \'delivered\'.', 1, '2025-10-07 17:21:00'),
(48, 15, 'Your order #63 status has been updated to \'shipped\'.', 1, '2025-10-07 20:31:45'),
(49, 15, 'Your order #63 status has been updated to \'delivered\'.', 1, '2025-10-07 20:33:10'),
(50, 15, 'Your return request for order #63 has been updated to \'approved\'.', 1, '2025-10-07 20:34:03');

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
(46, NULL, 64, '470.00', 'PHP', 'paypal', 'completed', NULL, '7XF940754L019954S');

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
(1, 1, 'Standard Eggs', '210.00', 'per tray', 'active', 22710, 30, '2025-10-06 11:22:18'),
(2, 1, 'Jumbo Eggs', '240.00', 'per tray', 'active', 22110, 30, '2025-10-06 11:22:18'),
(3, 2, 'Native Eggs', '280.00', 'per tray', 'active', 1080, 30, '2025-10-06 11:22:18'),
(4, 2, 'Free-Range Eggs', '300.00', 'per tray', 'active', 2550, 30, '2025-10-06 11:22:18'),
(5, 3, 'Golden Yolks', '220.00', 'per tray', 'active', 2040, 30, '2025-10-06 11:22:18'),
(6, 4, 'Fresh Brown Eggs', '215.00', 'per tray', 'active', 2040, 30, '2025-10-06 11:22:18'),
(7, 4, 'Pidan/Century Eggs', '350.00', 'per tray', 'active', 9390, 30, '2025-10-06 11:22:18'),
(8, 5, 'Pasture-Raised Eggs', '320.00', 'per tray', 'active', 1680, 30, '2025-10-06 11:22:18'),
(9, 6, 'White Eggs (Medium)', '190.00', 'per tray', 'active', 3450, 30, '2025-10-06 11:22:18'),
(10, 6, 'White Eggs (Large)', '205', 'per tray', 'active', 3030, 30, '2025-10-06 11:22:18'),
(11, 7, 'Salted Eggs', '250', 'per tray', 'active', 330, 30, '2025-10-06 11:22:18'),
(12, 8, 'Itik/Ducks Eggs', '290', 'per tray', 'active', 3300, 30, '2025-10-06 11:22:18'),
(13, 5, 'Test Egg', '210', 'per tray', 'active', 2850, 30, '2025-10-06 11:22:18'),
(14, 1, 'Test Egg2211', '6', 'per egg', 'active', 3000, 30, '2025-10-06 11:22:18'),
(16, 8, 'testegg4', '134', 'tray', 'active', 84, 12, '2025-10-07 12:04:34');

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
  `status` enum('pending','paid','processing','shipped','delivered','cancelled','failed','refunded') NOT NULL DEFAULT 'pending',
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
(22, 11, '2025-10-05 06:44:28', '210.00', '0.00', 'refunded', 5, 'card', 9, '2025-10-05 13:44:28', '2025-10-06 18:18:06', '9BG16867AY442874F', NULL, NULL, NULL, NULL, NULL),
(23, 9, '2025-10-05 08:42:14', '210.00', '0.00', 'cancelled', 6, 'card', 10, '2025-10-05 15:42:14', '2025-10-06 12:16:47', '5M619307HM508061X', NULL, NULL, NULL, NULL, NULL),
(24, 9, '2025-10-05 23:09:46', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:09:46', '2025-10-06 12:16:50', NULL, NULL, NULL, NULL, NULL, NULL),
(25, 9, '2025-10-05 23:15:10', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:15:10', '2025-10-06 12:16:52', NULL, NULL, 'Motorcycle', 4, NULL, NULL),
(26, 9, '2025-10-05 23:15:56', '210.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 06:15:56', '2025-10-06 12:16:55', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(27, 9, '2025-10-05 23:17:00', '420.00', '0.00', 'cancelled', 6, 'card', 11, '2025-10-06 06:17:00', '2025-10-06 12:17:01', '84D63581W5041242E', NULL, NULL, NULL, NULL, NULL),
(28, 9, '2025-10-05 23:17:49', '210.00', '0.00', 'cancelled', 6, 'card', 12, '2025-10-06 06:17:49', '2025-10-07 07:19:19', '2TM72684J1992790V', NULL, NULL, 5, NULL, NULL),
(29, 9, '2025-10-05 23:20:04', '2460.00', '0.00', 'delivered', 6, 'cod', NULL, '2025-10-06 06:20:04', '2025-10-07 08:39:25', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(30, 9, '2025-10-05 23:22:59', '6.00', '0.00', 'refunded', 6, 'card', 13, '2025-10-06 06:22:59', '2025-10-06 18:18:03', '78K33151N9884040P', NULL, NULL, NULL, NULL, NULL),
(31, 9, '2025-10-05 23:23:29', '6.00', '0.00', 'refunded', 6, 'cod', NULL, '2025-10-06 06:23:29', '2025-10-06 18:17:47', NULL, NULL, 'Car', 8, NULL, NULL),
(32, 9, '2025-10-06 04:56:50', '320.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 11:56:50', '2025-10-06 18:08:28', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(33, 9, '2025-10-06 05:16:06', '320.00', '0.00', 'cancelled', 6, 'cod', NULL, '2025-10-06 12:16:06', '2025-10-07 08:28:18', NULL, NULL, 'Motorcycle', 3, NULL, NULL),
(34, 11, '2025-10-06 05:21:34', '320.00', '0.00', 'cancelled', 5, 'cod', NULL, '2025-10-06 12:21:34', '2025-10-06 12:23:21', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(35, 11, '2025-10-06 05:21:57', '190.00', '0.00', 'cancelled', 5, 'cod', NULL, '2025-10-06 12:21:57', '2025-10-06 12:23:19', NULL, NULL, 'Car', NULL, NULL, NULL),
(36, 11, '2025-10-06 05:23:10', '2640.00', '0.00', 'cancelled', 5, 'card', 14, '2025-10-06 12:23:10', '2025-10-06 12:23:16', '9DV19535J95772624', NULL, NULL, NULL, NULL, NULL),
(37, 11, '2025-10-06 05:52:16', '320.00', '0.00', 'delivered', 5, 'cod', NULL, '2025-10-06 12:52:16', '2025-10-06 12:53:06', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(38, 11, '2025-10-06 06:01:41', '320.00', '0.00', 'refunded', 5, 'cod', NULL, '2025-10-06 13:01:41', '2025-10-06 18:17:34', NULL, NULL, 'Motorcycle', 5, NULL, NULL),
(39, 11, '2025-10-06 06:25:53', '6720.00', '0.00', 'delivered', 5, 'card', 15, '2025-10-06 13:25:53', '2025-10-06 13:28:07', '9XU17950L4524253S', NULL, NULL, 6, NULL, NULL),
(40, 11, '2025-10-06 07:48:10', '470.00', '150.00', 'shipped', 5, 'card', 17, '2025-10-06 14:48:10', '2025-10-07 08:37:22', '4BG90957EV100262Y', NULL, 'Car', NULL, NULL, NULL),
(41, 11, '2025-10-06 07:51:19', '360.00', '150.00', 'delivered', 5, 'card', 19, '2025-10-06 14:51:19', '2025-10-07 08:37:10', NULL, NULL, 'Car', 10, NULL, NULL),
(42, 11, '2025-10-06 07:54:31', '360.00', '150.00', 'refunded', 5, 'card', 20, '2025-10-06 14:54:31', '2025-10-06 18:35:38', NULL, NULL, 'Car', 6, NULL, NULL),
(43, 11, '2025-10-06 12:22:30', '420.00', '100.00', 'delivered', 5, 'card', 21, '2025-10-06 19:22:30', '2025-10-06 19:23:46', NULL, NULL, 'Motorcycle', 1, NULL, NULL),
(44, 15, '2025-10-07 01:17:06', '420.00', '100.00', 'processing', 10, 'card', 22, '2025-10-07 08:17:06', '2025-10-07 10:40:09', NULL, NULL, 'Motorcycle', 5, NULL, NULL),
(45, 16, '2025-10-07 01:36:17', '3300.00', '100.00', 'delivered', 11, 'card', 23, '2025-10-07 08:36:17', '2025-10-07 08:42:18', '7SH29966Y8093845U', NULL, 'Motorcycle', 2, NULL, NULL),
(46, 16, '2025-10-07 01:49:25', '420.00', '100.00', 'cancelled', 11, 'card', 24, '2025-10-07 08:49:25', '2025-10-07 08:49:35', '3MA500343D109353L', NULL, 'Motorcycle', NULL, NULL, NULL),
(47, 16, '2025-10-07 01:51:39', '290.00', '100.00', 'delivered', 11, 'card', 25, '2025-10-07 08:51:39', '2025-10-07 09:04:43', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(48, 15, '2025-10-07 01:51:45', '310.00', '100.00', 'shipped', 10, 'card', 26, '2025-10-07 08:51:45', '2025-10-07 08:59:54', NULL, NULL, 'Motorcycle', NULL, NULL, NULL),
(49, 16, '2025-10-07 02:19:01', '3300.00', '100.00', 'delivered', 11, 'card', 27, '2025-10-07 09:19:01', '2025-10-07 09:19:47', '151970546B292060Y', NULL, 'Motorcycle', NULL, NULL, NULL),
(50, 16, '2025-10-07 02:28:29', '63200.00', '200.00', 'cancelled', 11, 'card', 28, '2025-10-07 09:28:29', '2025-10-07 09:40:51', '92N23835SR736011H', NULL, 'Truck', NULL, NULL, NULL),
(51, 16, '2025-10-07 03:13:12', '3300.00', '100.00', 'refunded', 11, 'card', 29, '2025-10-07 10:13:12', '2025-10-07 10:34:23', '9NG02769X7065625W', NULL, 'Motorcycle', NULL, NULL, NULL),
(52, 16, '2025-10-07 03:36:27', '1125.00', '100.00', 'refunded', 11, 'card', 30, '2025-10-07 10:36:27', '2025-10-07 10:37:09', '8S837271MA081681R', NULL, 'Motorcycle', NULL, NULL, NULL),
(53, 15, '2025-10-07 03:41:00', '420.00', '100.00', 'delivered', 10, 'card', 31, '2025-10-07 10:41:00', '2025-10-07 10:42:01', NULL, NULL, 'Motorcycle', 4, NULL, NULL),
(54, 16, '2025-10-07 03:43:57', '186.00', '150.00', 'delivered', 11, 'card', 32, '2025-10-07 10:43:57', '2025-10-07 10:46:50', '09070277VL093634R', NULL, 'Car', 6, NULL, NULL),
(55, 16, '2025-10-07 03:49:19', '162.00', '150.00', 'refunded', 11, 'card', 33, '2025-10-07 10:49:19', '2025-10-07 10:55:17', '8U211686AT916030W', NULL, 'Car', 6, NULL, NULL),
(56, 16, '2025-10-07 03:56:40', '360.00', '150.00', 'paid', 11, 'card', 34, '2025-10-07 10:56:40', '2025-10-07 10:56:40', '4XE87685J6316111X', NULL, 'Car', NULL, NULL, NULL),
(57, 16, '2025-10-07 03:57:53', '360.00', '150.00', 'paid', 11, 'card', 35, '2025-10-07 10:57:53', '2025-10-07 10:57:53', '31E75124EV231600R', NULL, 'Car', NULL, NULL, NULL),
(58, 15, '2025-10-07 05:21:19', '284.00', '150.00', 'delivered', 10, 'card', 36, '2025-10-07 12:21:19', '2025-10-07 12:35:43', NULL, NULL, 'Car', NULL, NULL, NULL),
(59, 11, '2025-10-07 10:20:49', '560.00', '150.00', 'delivered', 5, 'card', 37, '2025-10-07 17:20:49', '2025-10-07 17:21:00', NULL, NULL, 'Car', NULL, NULL, NULL),
(60, 15, '2025-10-07 11:00:36', '470.00', '150.00', 'pending', 10, 'card', 38, '2025-10-07 18:00:36', '2025-10-07 18:00:36', NULL, NULL, 'Car', NULL, NULL, NULL),
(61, 15, '2025-10-07 11:01:33', '470.00', '150.00', 'pending', 10, 'card', 39, '2025-10-07 18:01:33', '2025-10-07 18:01:33', NULL, NULL, 'Car', NULL, NULL, NULL),
(62, 19, '2025-10-07 11:10:39', '790.00', '150.00', 'pending', 14, 'card', 40, '2025-10-07 18:10:39', '2025-10-07 18:10:39', NULL, NULL, 'Car', NULL, NULL, NULL),
(63, 15, '2025-10-07 12:58:13', '284.00', '150.00', 'delivered', 10, 'card', 43, '2025-10-07 19:58:13', '2025-10-07 20:33:10', NULL, NULL, 'Car', 9, NULL, ''),
(64, 15, '2025-10-07 13:25:33', '470.00', '150.00', 'paid', 10, 'card', 46, '2025-10-07 20:25:33', '2025-10-07 20:25:33', '33C50007GE084053Y', NULL, 'Car', NULL, NULL, '');

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
(63, 64, 5, 'Pasture-Raised Eggs', 1, '320.00', 0, 30);

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
(6, 16, 47, 'White Eggs (Medium)', 4, 'GOOD SH**', '2025-10-07 09:05:16', 46);

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
(13, 63, 62, 15, 16, 'Damaged in transit', 'approved', '2025-10-07 20:33:35', '2025-10-07 20:34:03', 'uploads/412007e1a4027efed7d275ec84a84448.jpg', '2025-10-07 13:34:03', 0);

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
(4, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi@gmail.com', '', 'default_avatar.png', '$2y$10$befzVXETI53dfpYMhMBvUeXykYXzrIK96lTix/3QtQFUoLCEZ4zpW', 'customer', 'ACTIVE', NULL, '2025-09-22 12:09:06', '2025-10-07 09:42:52', NULL),
(11, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.com', '09956336238', 'uploads/profile_pictures/user_11_68e2844c3376f2.15203154.jpg', '$2y$10$QDBf.AQkpUKMQgTu6r4D6OvmdkqzvIkzl0RKeOG092Ff7ILa/uNIu', 'customer', 'ACTIVE', NULL, '2025-10-04 15:18:40', '2025-10-07 17:09:54', '2025-10-07 17:09:54'),
(9, 'Admin', NULL, 'User', 'admin@crackcart.com', '123-456-7890', 'uploads/profile_pictures/user_9_68e38e5c29f866.87247820.jpg', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'ACTIVE', NULL, '2025-10-04 14:27:06', '2025-10-07 08:12:37', NULL),
(19, 'Rasheed Malachi', 'Ramirez', 'Salamat', 'rasheedmalachi.salamat@gmail.com', '09284943131', 'default_avatar.png', '$2y$10$uVORnrTrxCyRn3Feu1IT5eTQYvo638mT.5dlErduZPaC2VWTB6TYi', 'customer', 'ACTIVE', NULL, '2025-10-07 17:50:57', '2025-10-07 17:51:50', '2025-10-07 17:51:50'),
(15, 'Eduard Simon', 'Nemiada', 'Miana', 'qesnmiana@tip.edu.ph', '09956336238', 'uploads/profile_pictures/0c9b5f4b46035ce4a65af0c8c80f0cdb.jpg', '$2y$10$McsvdTCFkZxxE3r5bc.KIumyhHGwJV6eKWAVCtWMs2aFAFpnyY/Qa', 'customer', 'ACTIVE', NULL, '2025-10-06 19:31:59', '2025-10-07 19:48:49', '2025-10-07 19:25:21'),
(16, 'Kenneth Angelo', '', 'Sarmiento', 'sken4165@gmail.com', '09273848395', 'default_avatar.png', '$2y$10$KCNrCJStJ4XPynwSOfDo.OWtzxF7QVyfrDTxEC4ak1V8pfgC.1Ypy', 'customer', 'ACTIVE', NULL, '2025-10-07 07:15:16', '2025-10-07 10:10:25', '2025-10-07 10:10:25'),
(10, 'Eduard Simon', 'Nemiada', 'Miana', 'simonmiana@gmail.co', '09956336238', 'default_avatar.png', '$2y$10$Zzi2FrqhC90ptvMdxXzPVuHriuSOqReJ/6wAZNt3L0b56eCsnZKga', 'customer', 'ACTIVE', NULL, '2025-10-04 15:18:01', '2025-10-07 09:41:37', NULL);

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
(14, 19, '138 A Malumanay', 'UP Village', 'Quezon City', 'Quezon City', '', 'Philippines', 'shipping', 1);

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
(2, 'MC2398', 'Motorcycle', 15, 'in-transit', NULL),
(3, 'MC5874', 'Motorcycle', 8, 'in-transit', NULL),
(4, 'MC4862', 'Motorcycle', 12, 'in-transit', NULL),
(5, 'MC3321', 'Motorcycle', 14, 'in-transit', NULL),
(6, 'CAR7781', 'Car', 50, 'in-transit', NULL),
(7, 'CAR1212', 'Car', 45, 'available', NULL),
(8, 'CAR3434', 'Car', 55, 'available', NULL),
(9, 'CAR9087', 'Car', 40, 'available', NULL),
(10, 'CAR5656', 'Car', 48, 'in-transit', NULL),
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
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `NOTIFICATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `Payment`
--
ALTER TABLE `Payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `PRICE`
--
ALTER TABLE `PRICE`
  MODIFY `PRICE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `PRODUCER`
--
ALTER TABLE `PRODUCER`
  MODIFY `PRODUCER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `product_order_items`
--
ALTER TABLE `product_order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `product_returns`
--
ALTER TABLE `product_returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Rate_Card`
--
ALTER TABLE `Rate_Card`
  MODIFY `rate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `USER`
--
ALTER TABLE `USER`
  MODIFY `USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
