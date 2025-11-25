-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 04:22 PM
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
-- Database: `mechakeys`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `CartID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `VariationID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(8,2) NOT NULL,
  `DateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `MessageID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `MessageText` varchar(255) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `TimeSent` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Message` varchar(150) NOT NULL,
  `Type` varchar(20) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `TimeCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `CustomerID`, `Title`, `Message`, `Type`, `Status`, `TimeCreated`) VALUES
(167, 1, 'Order Placed Successfully', 'Your order #29 has been placed successfully. Total: ₱4,600.00 (Discount with coins: ₱1,000.00)', 'order', 'unread', '2025-11-16 16:16:53'),
(168, 1, 'Order Approved', 'Your order #29 has been approved and is now being processed.', 'order', 'unread', '2025-11-16 16:17:13'),
(169, 1, 'Order Status Updated', 'Your order #29 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-16 16:17:18'),
(170, 1, 'Delivery Status Updated', 'Your order #29 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-16 16:18:24'),
(171, 1, 'Order Placed Successfully', 'Your order #30 has been placed successfully. Total: ₱7,899.00 (Discount with coins: ₱500.00)', 'order', 'unread', '2025-11-16 16:27:28'),
(172, 1, 'Order Approved', 'Your order #30 has been approved and is now being processed.', 'order', 'unread', '2025-11-16 16:27:49'),
(173, 1, 'Order Status Updated', 'Your order #30 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-16 16:27:54'),
(174, 1, 'Delivery Status Updated', 'Your order #30 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-16 16:28:06'),
(175, 1, 'Order Placed Successfully', 'Your order #31 has been placed successfully. Total: ₱4,320.00 (Discount with coins: ₱320.00)', 'order', 'unread', '2025-11-16 20:46:11'),
(176, 1, 'Order Approved', 'Your order #31 has been approved and is now being processed.', 'order', 'unread', '2025-11-16 20:46:34'),
(177, 1, 'Order Status Updated', 'Your order #31 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-16 20:46:38'),
(178, 1, 'Order Placed Successfully', 'Your order #32 has been placed successfully. Total: ₱4,320.00', 'order', 'unread', '2025-11-18 08:35:50'),
(179, 1, 'Order Approved', 'Your order #32 has been approved and is now being processed.', 'order', 'unread', '2025-11-18 08:36:13'),
(180, 1, 'Order Status Updated', 'Your order #32 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Arjay Del', 'order', 'unread', '2025-11-18 08:36:21'),
(181, 1, 'Delivery Status Updated', 'Your order #32 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-18 08:37:10'),
(182, 6, 'Order Placed Successfully', 'Your order #33 has been placed successfully. Total: ₱12.00', 'order', 'unread', '2025-11-18 09:35:03'),
(183, 6, 'Order Approved', 'Your order #33 has been approved and is now being processed.', 'order', 'unread', '2025-11-18 09:35:37'),
(184, 6, 'Order Status Updated', 'Your order #33 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Ryan Deli', 'order', 'unread', '2025-11-18 09:36:30'),
(185, 6, 'Delivery Status Updated', 'Your order #33 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-18 09:37:25'),
(186, 6, 'Delivery Status Updated', 'Your order #33 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:37:35'),
(187, 6, 'Delivery Status Updated', 'Your order #33 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:38:45'),
(188, 6, 'Delivery Status Updated', 'Your order #33 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:38:55'),
(189, 6, 'Delivery Status Updated', 'Your order #33 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:39:04'),
(190, 6, 'Delivery Status Updated', 'Your order #33 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-18 09:39:23'),
(191, 6, 'Return Request Submitted', 'Your return request for order #33 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-18 09:40:48'),
(192, 6, 'Return Approved', 'Your return request for order #33 has been approved. A delivery rider will pick up the item from your address.', 'return', 'unread', '2025-11-18 09:42:05'),
(193, 6, 'Return Pickup Status Updated', 'Your return for order #33 is being transported to our warehouse.', 'return', 'unread', '2025-11-18 09:42:50'),
(194, 6, 'Return Pickup Status Updated', 'Your return for order #33 is being transported to our warehouse.', 'return', 'unread', '2025-11-18 09:43:32'),
(195, 6, 'Return Pickup Status Updated', 'Your return for order #33 has been picked up and processed. ₱12.00 coins have been credited to your account as store credit.', 'return', 'unread', '2025-11-18 09:43:57'),
(196, 6, 'Order Placed Successfully', 'Your order #34 has been placed successfully. Total: ₱12.00 (Discount with coins: ₱12.00)', 'order', 'unread', '2025-11-18 09:45:21'),
(197, 6, 'Order Approved', 'Your order #34 has been approved and is now being processed.', 'order', 'unread', '2025-11-18 09:45:39'),
(198, 6, 'Order Status Updated', 'Your order #34 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Ryan Deli', 'order', 'unread', '2025-11-18 09:45:46'),
(199, 6, 'Delivery Status Updated', 'Your order #34 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-18 09:46:36'),
(200, 6, 'Delivery Status Updated', 'Your order #34 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:46:52'),
(201, 6, 'Delivery Status Updated', 'Your order #34 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:47:03'),
(202, 6, 'Delivery Status Updated', 'Your order #34 is on the way to you!', 'delivery', 'unread', '2025-11-18 09:47:07'),
(203, 6, 'Delivery Status Updated', 'Your order #34 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-18 09:50:32'),
(204, 11, 'Order Placed Successfully', 'Your order #35 has been placed successfully. Total: ₱1.00', 'order', 'unread', '2025-11-18 10:04:26'),
(205, 11, 'Order Approved', 'Your order #35 has been approved and is now being processed.', 'order', 'unread', '2025-11-18 10:05:09'),
(206, 11, 'Order Status Updated', 'Your order #35 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Ryan Deli', 'order', 'unread', '2025-11-18 10:06:03'),
(207, 11, 'Delivery Status Updated', 'Your order #35 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-18 10:06:41'),
(208, 6, 'Order Auto-Confirmed as Received', 'Your order #000034 was automatically marked as received after 7 days. If you haven\'t received it, please contact support.', 'order', 'unread', '2025-11-21 21:31:22'),
(209, 1, 'Order Received Confirmation', 'Customer confirmed receipt for order #000032.', 'order', 'unread', '2025-11-21 21:34:09'),
(210, 5, 'Order Placed Successfully', 'Your order #36 has been placed successfully. Total: ₱15,798.00', 'order', 'unread', '2025-11-21 22:10:40'),
(211, 5, 'Order Approved', 'Your order #36 has been approved and is now being processed.', 'order', 'unread', '2025-11-21 22:13:43'),
(212, 5, 'Order Status Updated', 'Your order #36 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Ryan Deli', 'order', 'unread', '2025-11-21 22:13:52'),
(213, 5, 'Delivery Status Updated', 'Your order #36 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-21 22:14:02'),
(214, 5, 'Delivery Status Updated', 'Your order #36 is on the way to you!', 'delivery', 'unread', '2025-11-21 22:15:05'),
(215, 5, 'Delivery Status Updated', 'Your order #36 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-21 22:38:52'),
(216, 5, 'Order Received Confirmation', 'Customer confirmed receipt for order #000036.', 'order', 'unread', '2025-11-21 22:39:37'),
(217, 1, 'Order Placed Successfully', 'Your order #37 has been placed successfully. Total: ₱4,200.00', 'order', 'unread', '2025-11-21 22:54:36'),
(218, 5, 'Order Placed Successfully', 'Your order #38 has been placed successfully. Total: ₱7,911.00', 'order', 'unread', '2025-11-21 22:55:06'),
(219, 1, 'Order Approved', 'Your order #37 has been approved and is now being processed.', 'order', 'unread', '2025-11-21 22:55:08'),
(220, 1, 'Order Status Updated', 'Your order #37 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-21 22:55:12'),
(221, 5, 'Order Approved', 'Your order #38 has been approved and is now being processed.', 'order', 'unread', '2025-11-21 22:56:00'),
(222, 5, 'Order Status Updated', 'Your order #38 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Ryan Deli', 'order', 'unread', '2025-11-21 22:56:09'),
(223, 5, 'Delivery Status Updated', 'Your order #36 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-21 22:56:18'),
(224, 5, 'Delivery Status Updated', 'Your order #36 is on the way to you!', 'delivery', 'unread', '2025-11-21 22:56:46'),
(225, 1, 'Delivery Status Updated', 'Your order #37 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-21 22:56:50'),
(226, 5, 'Delivery Status Updated', 'Your order #38 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-21 22:56:56'),
(227, 1, 'Delivery Status Updated', 'Your order #37 is on the way to you!', 'delivery', 'unread', '2025-11-21 22:57:02'),
(228, 6, 'Delivery Status Updated', 'Your order #34 is on the way to you!', 'delivery', 'unread', '2025-11-21 22:57:02'),
(229, 6, 'Delivery Status Updated', 'Your order #34 is on the way to you!', 'delivery', 'unread', '2025-11-21 22:57:12'),
(230, 1, 'Delivery Status Updated', 'Your order #37 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-21 23:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `VariationID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `SubTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`OrderItemID`, `OrderID`, `ProductID`, `VariationID`, `Quantity`, `SubTotal`) VALUES
(44, 29, 5, 6, 1, 4600.00),
(45, 30, 10, 25, 1, 7899.00),
(47, 32, 7, 16, 1, 4320.00),
(48, 33, 15, 32, 1, 12.00),
(49, 34, 15, 32, 1, 12.00),
(50, 35, 17, 35, 1, 1.00),
(51, 36, 10, 21, 2, 15798.00),
(52, 37, 5, 5, 1, 4200.00),
(53, 38, 10, 12, 1, 7899.00),
(54, 38, 15, 32, 1, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `TrackingID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `Discount` decimal(10,2) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `PlaceOrdered` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerID`, `TrackingID`, `PaymentID`, `Discount`, `TotalAmount`, `PlaceOrdered`) VALUES
(29, 1, 42, 33, 1000.00, 4600.00, '2025-11-16 16:16:53'),
(30, 1, 43, 34, 500.00, 7899.00, '2025-11-16 16:27:28'),
(32, 1, 45, 36, 0.00, 4320.00, '2025-11-18 08:35:50'),
(33, 6, 46, 37, 0.00, 12.00, '2025-11-18 09:35:03'),
(34, 6, 48, 38, 12.00, 12.00, '2025-11-02 09:45:21'),
(35, 11, 49, 39, 0.00, 1.00, '2025-11-18 10:04:26'),
(36, 5, 50, 40, 0.00, 15798.00, '2025-11-21 22:10:40'),
(37, 1, 51, 41, 0.00, 4200.00, '2025-11-21 22:54:36'),
(38, 5, 52, 42, 0.00, 7911.00, '2025-11-21 22:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PaymentID`, `OrderID`, `Amount`, `Status`, `TransactionDate`) VALUES
(33, 29, 3600.00, 'Pending', '2025-11-16 16:16:53'),
(34, 30, 7399.00, 'Pending', '2025-11-16 16:27:28'),
(36, 32, 4320.00, 'Pending', '2025-11-18 08:35:50'),
(37, 33, 12.00, 'Pending', '2025-11-18 09:35:03'),
(38, 34, 0.00, 'Pending', '2025-11-18 09:45:21'),
(39, 35, 1.00, 'Pending', '2025-11-18 10:04:26'),
(40, 36, 15798.00, 'Pending', '2025-11-21 22:10:40'),
(41, 37, 4200.00, 'Pending', '2025-11-21 22:54:36'),
(42, 38, 7911.00, 'Pending', '2025-11-21 22:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `productimages`
--

CREATE TABLE `productimages` (
  `ID` int(11) NOT NULL,
  `ProductImageID` int(11) NOT NULL,
  `Path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productimages`
--

INSERT INTO `productimages` (`ID`, `ProductImageID`, `Path`) VALUES
(4, 1, 'mechakeys/products/keyboards/AulaF75/image1.png'),
(5, 1, 'mechakeys/products/keyboards/AulaF75/image2.png'),
(6, 1, 'mechakeys/products/keyboards/AulaF75/image3.png'),
(7, 1, 'mechakeys/products/keyboards/AulaF75/image4.png'),
(8, 1, 'mechakeys/products/keyboards/AulaF75/image5.png'),
(9, 1, 'mechakeys/products/keyboards/AulaF75/image6.png'),
(10, 1, 'mechakeys/products/keyboards/AulaF75/image7.png'),
(11, 1, 'mechakeys/products/keyboards/AulaF75/image8.png'),
(12, 1, 'mechakeys/products/keyboards/AulaF75/image9.png'),
(13, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image1.png'),
(14, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image2.png'),
(15, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image3.png'),
(16, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image4.png'),
(17, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image5.png'),
(18, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image6.png'),
(19, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image7.png'),
(20, 5, 'mechakeys/products/keyboards/MonsgeekM1V5/image8.png'),
(21, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image1.png'),
(22, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image2.png'),
(23, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image3.png'),
(24, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image4.png'),
(25, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image5.png'),
(26, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image6.png'),
(27, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image7.png'),
(28, 6, 'mechakeys/products/keyboards/MonsgeekMG108BRainbowMarshmallows/image8.png'),
(35, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image7.png'),
(36, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image2.png'),
(37, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image3.png'),
(38, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image4.png'),
(39, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image5.png'),
(40, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image6.png'),
(41, 8, 'mechakeys/products/keyboards/KeychronK3MaxQMK/VIA/image1.png'),
(42, 9, 'mechakeys/products/keyboards/LogitechMXMechanical/image1.png'),
(43, 9, 'mechakeys/products/keyboards/LogitechMXMechanical/image2.webp'),
(44, 9, 'mechakeys/products/keyboards/LogitechMXMechanical/image3.webp'),
(45, 9, 'mechakeys/products/keyboards/LogitechMXMechanical/image4.webp'),
(46, 9, 'mechakeys/products/keyboards/LogitechMXMechanical/image5.webp'),
(47, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image1.png'),
(48, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image2.png'),
(49, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image3.png'),
(50, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image4.png'),
(51, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image5.png'),
(52, 10, 'mechakeys/products/keyboards/RAKKHananUltra/image6.png'),
(53, 11, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsCilantroSwitches/image1.png'),
(54, 11, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsCilantroSwitches/image2.png'),
(55, 11, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsCilantroSwitches/image3.png'),
(56, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image1.png'),
(57, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image2.png'),
(58, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image3.png'),
(59, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image4.png'),
(60, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image5.png'),
(61, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image6.png'),
(62, 12, 'mechakeys/products/keycapss/N/AAkkoKuromiKeycapSet(138-key)/image7.png'),
(63, 13, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsRosewoodSwitches/image1.png'),
(64, 13, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsRosewoodSwitches/image2.png'),
(65, 13, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsRosewoodSwitches/image3.png'),
(66, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image1.png'),
(67, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image2.png'),
(68, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image3.png'),
(69, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image4.png'),
(70, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image5.png'),
(71, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image6.png'),
(72, 14, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image7.png'),
(73, 15, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom665/image1.png'),
(74, 16, 'mechakeys/products/switchess/N/A123456ll/image1.png'),
(75, 17, 'mechakeys/products/accessoriess/N/Amegaukelele/image1.png');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `Brand` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Category` varchar(20) NOT NULL,
  `TotalSold` int(11) NOT NULL,
  `ProductImageID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Brand`, `Model`, `Description`, `Category`, `TotalSold`, `ProductImageID`) VALUES
(3, 'Aula', 'F75', 'Ultimate Versatility and Performance with Aula F75 3-in-1 Mechanical Keyboard\r\n\r\nTake your gaming and productivity to the next level with the Aula F75 3-in-1 Gaming Mechanical Keyboard. Designed for gamers and multitaskers alike, this keyboard combines advanced mechanical performance with unmatched flexibility—featuring three connection modes, including Type-C wired, 2.4G wireless, and Bluetooth.\r\n\r\nThe gasket mount construction delivers a softer, quieter typing experience, while hot-swappable mechanical switches allow you to easily customize your keys without soldering. With RGB lighting effects, a compact 80-key layout, and a built-in rechargeable lithium battery, the Aula F75 offers everything you need for a smooth and stylish setup.\r\n\r\n\r\nKey Features and Specifications:\r\n- 3 Connection Modes: Type-C Wired, 2.4G Wireless, and Bluetooth\r\n- Hot-Swappable Mechanical Switches for easy switch replacement\r\n- Gasket Mount Construction for a more cushioned and silent typing experience\r\n- 80-Key Compact Layout for space-saving functionality without losing essential keys\r\n- RGB Switchable Lighting Effects to match your mood or setup\r\n- Rechargeable Lithium Battery for extended wireless use\r\n- Pluggable Design – easily switch out keys or connect across devices\r\n\r\n\r\n️✍ Product Specification：\r\n● Product Name: AULA F75\r\n● Key count: 80 keys\r\n● Rated voltage: DC 3.7V (fully charged with 4.2V)\r\n● Battery capacity: 4000mAh rechargeable lithium battery\r\n● Product weight: approximately 1023g (including wire/receiver)\r\n● Transmission method: Bluetooth/2.4G/wired\r\n● Total travel of buttons: 4.0mm\r\n● Voltage/current: DC 5V ≌ 700mA\r\n● Charging interface: Type-C interface\r\n● Product size: 322.7 * 143.2 * 43.1 ± 1mm\r\n● Accessories: Key puller * 1, switch * 2, data cable * 1, instruction manual * 1\r\n\r\n\r\nWhether you\'re a competitive gamer or a productivity-focused professional, the Aula F75 delivers top-tier performance with the flexibility of three connection modes.', 'keyboard', 22, 1),
(5, 'Monsgeek', 'M1 V5', 'An excellent upgrade from the previous version, featuring Tool-free Rapid Assembly and Disassembly for effortless customization. It’s both budget-friendly and accessible for beginners and advanced users alike.\r\n\r\n\r\nAdditional highlights include:\r\n- 8000mAh battery for long-lasting, stable performance;\r\n- Multi-mode connectivity, supporting up to 5 devices simultaneously;\r\n- Separate Encoder for Enhanced Stability;\r\n- VIA support for easy key remapping and RGB customization.\r\n\r\nDisclaimer: This model supports customization via VIA only. MonsGeek Driver is not supported.\r\n\r\n\r\nModel: M1 V5 VIA Rapid Disassembly\r\nSpecifications\r\nMount: Gasket\r\nCase Material: Aluminum\r\nLayout: ANSI\r\nConnection: USB-C Wired & 2.4G Wireless & Bluetooth\r\nLED: RGB\r\nHotswap: Y (5-pin)\r\nPCB Thickness: 1.2mm\r\nStabilizers: Pre-assembled Plated-mounted Stabilizers; Separate Screw-in Stabilizers\r\nBattery: 8000mAh\r\nPlate: PC\r\nPlate Foam: Y\r\nSwitch Pad: Y\r\nTape Mod: N\r\nCase Foam: Y\r\nVIA: Y\r\nMonsGeek Driver: N', 'keyboard', 14, 5),
(6, 'Monsgeek', 'MG108B Rainbow Marshmallows', 'MonsGeek x Akko Rainbow Marshmallows Keyboard features rainbow-color Marshmallow with playful emojis\r\n\r\nThis full-sized keyboard offers a comprehensive range of function keys and a numeric pad, making it ideal for gamers, office professionals, programmers, and typists alike.\r\n\r\nMulti-modes (Bluetooth 5.0, 2.4Ghz, and Type-C);\r\n\r\nMOG Profile Dye-sub Keycaps;\r\n\r\n5-pin Hotswappable;\r\n\r\nRGB Backlit;\r\n\r\nKeys Remapping and RGB Customization through MonsGeek Driver.', 'keyboard', 0, 6),
(8, 'Keychron', 'K3 Max QMK/VIA ', 'The Keychron K3 Max is an ultra-slim 75% layout mechanical keyboard engineered for enthusiasts who demand both portability and uncompromising customization. It combines a sleek, low-profile form factor with the powerful, open-source QMK and VIA software, allowing for deep, real-time personalization without the need for complex flashing.\r\n\r\nThis compact (75%) layout retains the crucial function and navigation keys while significantly reducing the keyboard\'s footprint, making it the perfect centerpiece for a clean, efficient desk setup. It is ideally suited for programmers, writers, and power users who need extensive key customization while saving space.\r\n\r\nSwitch Types:\r\n\r\nRed (K3M-A1): A smooth and consistent linear switch, offering quiet keystrokes with no tactile bump. Ideal for fast-paced gaming and rapid, fluid typing.\r\n\r\n** Brown (K3M-A3): A subtle tactile switch that provides a gentle bump for feedback without being loud. Perfect for those who want the assurance of a tactile response in office or shared environments.\r\n\r\nKey Features:\r\n\r\nUnmatched Customization with QMK/VIA: The standout feature of the K3 Max is its support for the open-source QMK firmware and VIA remapping software. This allows you to reprogram every key, create complex macros, and design sophisticated layered functions directly through a simple web interface, all in real-time.\r\n\r\nTri-Mode Wireless Connectivity: Enjoy complete cable-free freedom. Switch seamlessly between Bluetooth 5.1 for connecting up to three devices, the low-latency 2.4GHz wireless mode for a reliable gaming connection, or the wired USB-C mode for continuous use and charging.\r\n\r\nSlim & Portable Design: With its low-profile keycaps and slim aluminum frame, the Kron K3 Max is incredibly portable and ergonomic, reducing strain on your wrists during long typing sessions. It’s designed to be your go-to keyboard for both desk use and on-the-go productivity.\r\n\r\nHot-Swappable Sockets: The keyboard features hot-swappable sockets, enabling you to easily change between the included low-profile optical switches without any soldering. This future-proofs your investment and lets you experiment with different switch feels.\r\n\r\nWhite Backlighting: Features a clean and elegant white backlight (south-facing) with multiple lighting effects. It provides excellent key visibility in low-light conditions while maintaining a professional and minimalist aesthetic.\r\n\r\nRobust Build & macOS/Windows Compatibility: Crafted with a durable aluminum frame and high-quality keycaps, the K3 Max is built to last. It offers dedicated keycaps and full functional support for both macOS and Windows operating systems.', 'keyboard', 2, 8),
(9, 'Logitech', 'MX Mechanical', 'The Logitech MX Mechanical is a sophisticated wireless keyboard engineered for professionals and power users who demand precision, comfort, and seamless workflow integration. It combines a modern, low-profile design with smart features that adapt to your environment, creating a truly intelligent typing experience.\r\n\r\nAvailable in both a comprehensive Full-size layout with an integrated number pad and a space-saving Mini (75% compact) layout, it caters to different desk setups and user preferences without sacrificing functionality. The compact version retains essential navigation keys for efficiency.\r\n\r\nSwitch Types:\r\n\r\nTactile Quiet: Provides a subtle, satisfying bump for accurate typing with minimal sound, perfect for shared workspaces like offices and home setups.\r\n\r\nClicky: Offers an audible click and a distinct tactile feedback for a classic, responsive mechanical typing feel that boosts confidence and rhythm.\r\n\r\nLinear: Delivers a smooth, consistent keystroke from top to bottom with a quiet operation, ideal for fast, fluid typing and gaming.\r\n\r\nKey Features:\r\n\r\nSmart Illuminated Keys: The adaptive backlighting automatically adjusts its brightness based on ambient light conditions to conserve power. The proximity sensor detects your hands, waking the keyboard and lighting up the keys as you approach. Individual keycaps are also edge-lit for superior visibility in any lighting.\r\n\r\nAdvanced Multi-Device Pairing: Effortlessly connect and switch between up to three different devices (Windows, macOS, iOS, Android) using either Bluetooth Low Energy or the included Logitech Unifying USB receiver. Seamlessly flow your work from a PC to a laptop or a tablet.\r\n\r\nLogi Options+ Software Customization: Unlock the full potential of the MX Mechanical with the Logi Options+ software. Remap keys, assign complex shortcuts and emoji commands to the F-key row, and create app-specific profiles that automatically switch based on the active application.\r\n\r\nEcosystem Integration with Logi Bolt: For enhanced security and reliable performance in crowded wireless environments, it supports the Logi Bolt receiver, ensuring a robust connection with your computer.\r\n\r\nComfortable Low-Profile Design: The carefully sculpted, low-profile keycaps are optimized for comfort and stability, reducing finger fatigue and promoting a more natural typing posture during extended use.\r\n\r\nUSB-C Quick Charging: A full-speed USB-C port provides convenience. A full charge delivers up to 15 days of use with backlighting on, or up to 10 months with backlighting off, ensuring exceptional battery life for uninterrupted productivity.\r\n\r\nPremium Build & Sustainable Materials: Built with a solid, minimalist aluminum frame for durability and stability, it is also designed with sustainability in mind, using post-consumer recycled plastic in its keycaps and housing. Available in Graphite and Pale Grey to complement any professional workspace.', 'keyboard', 2, 9),
(10, 'RAKK', 'Hanan Ultra', 'Discover the RAKK Hanan Ultra Mechanical Keyboard, a meticulously crafted tool engineered for peak performance and enduring quality. Designed for enthusiasts who refuse to compromise, it combines a sophisticated gasket-mount structure within a durable aluminum CNC-milled case, offering a uniquely refined typing experience that is both crisp and satisfyingly muted.\r\n\r\nThis compact 81-key (75%) layout efficiently maximizes desk space by eliminating the number pad while retaining the essential function and navigation cluster, making it the ultimate choice for gamers requiring mouse real estate, professionals seeking a minimalist setup, and touch-typists who value efficiency.\r\n\r\nKey Features:\r\n\r\nGasket Mount Structure: The keyboard is equipped with a premium gasket mount system, where the PCB is suspended by soft silicone gaskets instead of being hard-mounted. This innovative design absorbs keystroke impact and minimizes metal-on-metal resonance, resulting in a uniquely softer, more consistent, and poppy typing sound across the entire board.\r\n\r\nUnibody Aluminum CNC Case: Machined from a solid block of aluminum, the case provides exceptional heft, durability, and resistance to flex. The pristine white finish not only offers a sleek, modern aesthetic but also ensures the keyboard remains a stable foundation during intense typing or gaming sessions.\r\n\r\nVersatile Tri-Mode Connectivity: Enjoy complete wireless freedom and a reliable wired connection. Switch effortlessly between Bluetooth 5.0 for multi-device pairing, a lag-free 2.4GHz wireless connection for competitive gaming, and a wired USB-Type C mode for uninterrupted use and charging.\r\n\r\n5-Pin Hot-Swappable PCB: The heart of customization, the hot-swappable sockets allow you to easily install or change any compatible 3-pin or 5-pin mechanical switches without soldering. This empowers you to tailor the actuation force and feedback—be it linear, tactile, or clicky—to your exact preference.\r\n\r\nDynamic RGB Backlighting: Experience a vibrant light show with fully customizable per-key RGB lighting. With a wide spectrum of colors and numerous pre-installed effects, you can personalize your setup\'s ambiance while ensuring perfect key visibility in any environment.\r\n\r\nFull Software Support: Unlock the keyboard\'s full potential with dedicated software. Remap any key, create complex macros, and fine-tune every aspect of the RGB lighting effects to match your workflow and gaming style perfectly.\r\n\r\nPremium Keycaps & Stabilizers: Equipped with high-quality, dye-sublimated PBT keycaps that resist shine and fading over time, ensuring legends remain crisp. The pre-lubricated screw-in stabilizers are meticulously tuned to eliminate wire rattle, providing a smooth and consistent feel for larger keys like the spacebar and shift.\r\n\r\n', 'keyboard', 11, 10),
(11, 'N/A', 'Akko 2 Pack-90 Pcs Cilantro Switches', 'Cilantro switches are Akko’s first deep clack tactile switches.\r\nIt comes with an early bump at very top, with strong tactile feedback.\r\n\r\nSwitches Parameter\r\nAkko Cilantro Switches\r\nType: Early Tactile\r\nOperating Force: 36 ± 5gf\r\nTotal Travel: 3.5 ± 0.3mm\r\nPre-Travel: 2.1 ± 0.5mm\r\nTactile Travel: 0.2 ± 0.2mm\r\nTactile Force: 58 ± 5gf', 'switches', 1, 11),
(12, 'N/A', 'Akko Kuromi Keycap Set (138-key)', 'Akko x Kuromi Limited Edition Keycap Set\r\nModel：Kuromi Keycap Set (138-key)\r\n\r\n\r\nSpecification\r\n- MOA profile;\r\n- PBT Dye-Sub Keycaps\r\n-  With advanced 5-sided dye sublimation technology, the keycaps feature vibrant and colorful printing across every surface. Made from durable PBT materials, the legends will not fade easily.\r\n - Compatible with major-sizes keyboard including but not limited to 60%, 64-key, 65%, TKL, 75%, 96%, 1800 compact, and full-size keyboards.', 'keycaps', 3, 12),
(13, 'N/A', 'Akko 2 Pack-90 Pcs Rosewood Switches', 'Rosewood is born with the resolution of making a nice low-pitch switch for our MU01 wooden case keyboard.\r\n\r\nMajor Changes:\r\n -  The signature deep sound provides a pleasant thocky sound out of box with a thin layer of factory lubrication.\r\n -  The switch features our custom Nylon (Pro) stem, a PA12 blend top cover, and a PA6 bottom housing, creating a unique material combination.\r\n -  Maintaining the classic and nostalgic 4.0mm total travel, the 22mm spring ensures a responsive feel without any mushiness.\r\n -  5-pin and fits keycaps with standard MX structure.\r\n -  FYI. If you are looking for a slightly high-pitch version, please check our Botany Switches (TBD).\r\n\r\n\r\nSwitches Parameter\r\n\r\n\r\nAkko Rosewood Switches\r\n\r\nType: Linear\r\nOperating Force: 40 ± 5gf\r\nPre-Travel: 2.0 ± 0.5mm\r\nTotal Travel: 4.0mm\r\nTactile Travel: N/A\r\nTactile Force: N/A', 'switches', 4, 13),
(14, 'Royal Kludge', 'RKM87Famicom', 'RK Royal Kludge RK M87 Famicom Keyboard captures the iconic aesthetic of the classic 8-bit era with its signature red, white, and grey color block design, evoking a powerful sense of nostalgia for gaming pioneers. This isn\'t just a keyboard; it\'s a functional piece of retro art for your modern desk setup.\r\n\r\nThis compact 88-key TenKeyLess (TKL) layout eliminates the number pad to free up valuable desk space for broader mouse movements, making it a top choice for competitive gamers, minimalists, and anyone seeking an efficient and ergonomic workspace without sacrificing the core function row.\r\n\r\nKey Features:\r\n\r\nIntelligent Tri-Mode Connectivity: Effortlessly switch between three connection methods. Pair with up to three devices via Bluetooth for ultimate flexibility, use the included 2.4GHz wireless dongle for a lag-free gaming experience, or connect via USB-Type C for reliable, pass-through charging and wired use.\r\n\r\nInteractive TFT Color Display: The standout feature is the vibrant TFT screen that goes beyond simple indicators. It provides real-time system data like battery life, connection mode, and Caps/Num Lock status. Crucially, it allows you to upload custom GIFs and images, making your keyboard a truly unique centerpiece.\r\n\r\n5-Pin Hotswappable PCB: Embrace the custom keyboard hobby with a beginner-friendly hot-swap socket design. This allows you to effortlessly install or change any compatible 3-pin or 5-pin mechanical switches to tailor the actuation force and feel—be it linear, tactile, or clicky—to your personal preference, all without soldering.\r\n\r\nDynamic RGB Backlighting: Beneath the retro keycaps lies a modern, vibrant RGB lighting system. Choose from a spectrum of pre-installed lighting effects and colors to match your mood or setup. The shine-through keycap legends ensure perfect visibility, day or night.\r\n\r\nComprehensive Software Suite: Unlock the full potential of your keyboard with the RK Official Software. This powerful tool allows for deep customization, including advanced key remapping, complex macro programming, fine-tuning of every RGB lighting mode, and managing the content displayed on the TFT screen.\r\n\r\nLong-Lasting Battery & Robust Build: Engineered for both wireless freedom and durability, the keyboard is equipped with a high-capacity battery for extended use and features a solid construction that ensures stability during intense typing or gaming sessions.', 'keyboard', 0, 14),
(15, 'Aula', 'RKM87Famicom', 'ado', 'keyboard', 3, 15),
(16, 'N/A', '123456ll', '78jkj', 'switches', 0, 16),
(17, 'Royal Kludge', 'mega ukelelehhhhhh', 'torototjjjj', 'accessories', 1, 17);

-- --------------------------------------------------------

--
-- Table structure for table `productvariations`
--

CREATE TABLE `productvariations` (
  `VariationID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Layout` int(11) NOT NULL,
  `SwitchType` varchar(50) NOT NULL,
  `Color` varchar(20) NOT NULL,
  `Price` decimal(8,2) NOT NULL,
  `StockQuantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productvariations`
--

INSERT INTO `productvariations` (`VariationID`, `ProductID`, `Layout`, `SwitchType`, `Color`, `Price`, `StockQuantity`) VALUES
(1, 3, 75, 'Reaper SW', 'Blue', 2999.00, 18),
(2, 3, 75, 'Reaper SW', 'Black', 2999.00, 14),
(3, 3, 75, 'Reaper SW', 'Light Blue', 3000.00, 21),
(4, 5, 75, 'Piano Pro', 'Moonlight Black', 4200.00, 16),
(5, 5, 75, 'Cilantro Green', 'Moonlight Black', 4200.00, 11),
(6, 5, 75, 'Rosewood', 'White', 4600.00, 25),
(7, 5, 75, 'Piano Pro', 'White', 4600.00, 0),
(8, 6, 100, 'Akko V3 Piano Pro Switch, Akko Creamy Cyan Switch', 'Rainbow Marshmallows', 5699.00, 15),
(10, 8, 80, 'Red (Linear – K3M-A1)', 'Gray', 5590.00, 12),
(11, 9, 75, 'Logitech Tactile Quiet', ' Graphite', 7395.00, 12),
(12, 10, 75, 'Linear', 'Blue', 7899.00, 40),
(13, 11, 0, '', '', 1370.00, 42),
(14, 12, 0, '', '', 2249.00, 29),
(15, 13, 0, '', '', 1370.00, 91),
(21, 10, 75, 'Quiet', 'Blue', 7899.00, 9),
(22, 10, 75, 'Clicky', 'Blue', 7899.00, 18),
(23, 10, 75, 'Linear', 'Red', 7899.00, 19),
(24, 10, 75, 'Clicky', 'Red', 7899.00, 18),
(25, 10, 75, 'Clicky', 'Green', 7899.00, 20),
(26, 9, 75, 'Logitech Tactile Quiet', 'Pale Grey', 6999.00, 8),
(27, 9, 75, 'Clicky', 'Pale Grey', 6999.00, 12),
(28, 9, 75, 'Linear', 'Pale Grey', 6999.00, 19),
(29, 8, 80, 'Brown (Tactile – K3M-A3)', 'Gray', 5590.00, 12),
(30, 14, 75, 'Beige', 'Famicon', 4320.00, 21),
(31, 14, 75, 'Blue', 'Famicon', 4300.00, 20),
(32, 15, 80, '123', 'black', 12.00, 10),
(33, 15, 100, '456', 'blue', 13.00, 13),
(34, 16, 0, '', '', 888.00, 1000),
(35, 17, 0, '12228', 'vilette', 1200.00, 3);

-- --------------------------------------------------------

--
-- Table structure for table `remittance_logs`
--

CREATE TABLE `remittance_logs` (
  `RemittanceLogID` int(10) UNSIGNED NOT NULL,
  `RemittanceID` int(10) UNSIGNED NOT NULL,
  `AdminID` int(11) NOT NULL,
  `Action` varchar(50) NOT NULL,
  `OldStatus` varchar(50) DEFAULT NULL,
  `NewStatus` varchar(50) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `TimeCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remittance_logs`
--

INSERT INTO `remittance_logs` (`RemittanceLogID`, `RemittanceID`, `AdminID`, `Action`, `OldStatus`, `NewStatus`, `Note`, `TimeCreated`) VALUES
(4, 3, 2, 'Created', NULL, 'Pending', '', '2025-11-16 16:25:59'),
(5, 3, 2, 'StatusChanged', '', 'Cancelled', '', '2025-11-16 16:26:37'),
(6, 3, 2, 'StatusChanged', '', 'Pending', '', '2025-11-16 16:26:57'),
(7, 3, 2, 'StatusChanged', '', 'Paid', '', '2025-11-16 16:27:49'),
(8, 4, 2, 'Created', NULL, 'Pending', '', '2025-11-16 16:29:20'),
(9, 4, 2, 'StatusChanged', '', 'Paid', '', '2025-11-16 16:29:37'),
(10, 5, 2, 'Created', NULL, 'Pending', '', '2025-11-18 08:40:56'),
(11, 5, 2, 'StatusChanged', '', 'Paid', '', '2025-11-18 08:41:06');

-- --------------------------------------------------------

--
-- Table structure for table `remittance_orders`
--

CREATE TABLE `remittance_orders` (
  `RemittanceID` int(10) UNSIGNED NOT NULL,
  `OrderID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remittance_orders`
--

INSERT INTO `remittance_orders` (`RemittanceID`, `OrderID`) VALUES
(3, 29),
(4, 30),
(5, 32);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `ReturnID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `ReturnReason` text NOT NULL,
  `ProofImage` varchar(255) NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Pending' COMMENT 'Pending, Approved, Rejected, Returned, Completed',
  `AdminMessage` text DEFAULT NULL,
  `ReturnTrackingID` int(11) DEFAULT NULL,
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`ReturnID`, `OrderID`, `CustomerID`, `ReturnReason`, `ProofImage`, `Status`, `AdminMessage`, `ReturnTrackingID`, `CreatedAt`, `UpdatedAt`) VALUES
(11, 33, 6, 'Ukinnam met', 'mechakeys/proofofdelivery/returns/proof_return_33_1763430048_239c3719.jpg', 'Returned', NULL, 47, '2025-11-18 09:40:48', '2025-11-18 09:43:57');

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `ReturnItemID` int(11) NOT NULL,
  `ReturnID` int(11) NOT NULL,
  `OrderItemID` int(11) NOT NULL,
  `RefundAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_items`
--

INSERT INTO `return_items` (`ReturnItemID`, `ReturnID`, `OrderItemID`, `RefundAmount`) VALUES
(13, 11, 48, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `rider_remittances`
--

CREATE TABLE `rider_remittances` (
  `RemittanceID` int(10) UNSIGNED NOT NULL,
  `RiderID` int(10) UNSIGNED NOT NULL,
  `Amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PeriodStart` date DEFAULT NULL,
  `PeriodEnd` date DEFAULT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT current_timestamp(),
  `PaymentMethod` enum('Cash') NOT NULL DEFAULT 'Cash',
  `Reference` varchar(255) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `Status` enum('Pending','Paid','Cancelled','Failed') NOT NULL DEFAULT 'Pending',
  `CreatedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT NULL,
  `UpdatedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rider_remittances`
--

INSERT INTO `rider_remittances` (`RemittanceID`, `RiderID`, `Amount`, `PeriodStart`, `PeriodEnd`, `TransactionDate`, `PaymentMethod`, `Reference`, `Notes`, `Status`, `CreatedAt`, `UpdatedAt`, `UpdatedBy`) VALUES
(3, 3, 3600.00, '0000-00-00', '0000-00-00', '2025-11-16 16:25:59', 'Cash', '', '', 'Paid', '2025-11-16 16:25:59', NULL, NULL),
(4, 3, 7399.00, '0000-00-00', '0000-00-00', '2025-11-16 16:29:20', 'Cash', '', '', 'Paid', '2025-11-16 16:29:20', NULL, NULL),
(5, 8, 4320.00, '0000-00-00', '0000-00-00', '2025-11-18 08:40:56', 'Cash', '', '', 'Paid', '2025-11-18 08:40:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trackings`
--

CREATE TABLE `trackings` (
  `TrackingID` int(11) NOT NULL,
  `DeliveryPersonID` int(11) NOT NULL,
  `DeliveryStatus` varchar(20) NOT NULL,
  `FailedMessage` text NOT NULL,
  `DeliveryProof` varchar(255) DEFAULT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trackings`
--

INSERT INTO `trackings` (`TrackingID`, `DeliveryPersonID`, `DeliveryStatus`, `FailedMessage`, `DeliveryProof`, `LastUpdated`) VALUES
(42, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_42_1763281104_6f7693d7.jpg', '2025-11-16 16:18:24'),
(43, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_43_1763281686_26d4dbdb.jpg', '2025-11-16 16:28:06'),
(45, 8, 'Order Received', '', 'mechakeys/proofofdelivery/proof_45_1763426230_99022f57.jpg', '2025-11-21 21:34:09'),
(46, 7, 'Delivered', '', 'mechakeys/proofofdelivery/proof_46_1763429963_386fedbb.jpg', '2025-11-18 09:39:23'),
(47, 7, 'Delivered', '', 'mechakeys/proofofdelivery/proof_47_1763430237_66244501.jpg', '2025-11-18 09:43:57'),
(48, 7, 'In Transit', '', 'mechakeys/proofofdelivery/proof_48_1763430632_1f278572.jpg', '2025-11-21 22:57:12'),
(49, 7, 'Delivered', '', 'mechakeys/proofofdelivery/proof_49_1763431601_dd15e226.jpg', '2025-11-18 10:06:41'),
(50, 7, 'In Transit', '', 'mechakeys/proofofdelivery/proof_50_1763735932_d01a7727.jpg', '2025-11-21 22:56:46'),
(51, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_51_1763737465_9fd553ab.jpg', '2025-11-21 23:04:25'),
(52, 7, 'Picked', '', NULL, '2025-11-21 22:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `FullName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Contact` varchar(20) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Role` varchar(10) NOT NULL,
  `Coins` decimal(10,2) NOT NULL DEFAULT 0.00,
  `DateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `FullName`, `Email`, `Password`, `Contact`, `Address`, `Location`, `Role`, `Coins`, `DateCreated`) VALUES
(1, 'Justine Agcanas', 'customer@gmail.com', '$2y$10$3BahdjMnBOTBo6FC/6VyQOXlNCgP/818SMolBWMN4TnCTHbIHBWwu', '09182738829', 'J. P. Rizal Avenue Bgy. No. 1, Laoag City, Ilocos Norte, Philippines', '', 'customer', 5828.00, '2025-10-16 20:57:36'),
(2, 'Admin Justine', 'admin@gmail.com', '$2y$10$wTWVyRSuPT.brut5JIikIuWKZgfnDEWdz.4PfRssiDiB.BetEg1QW', '', '', '', 'admin', 0.00, '2025-10-16 21:01:44'),
(3, 'Justine Delivery', 'delivery@gmail.com', '$2y$10$Otfgo.ws5ePSJOLnjWvYHOg625AA5.YDjpM8KLlPihmoH9gQ.ZFwS', '09283028474', 'J. P. Rizal Avenue Bgy. No. 1, Laoag City, Ilocos Norte, Philippines', '', 'delivery', 0.00, '2025-10-26 19:16:54'),
(4, 'John Burat', 'johnburat@gmail.com', '$2y$10$oR8fF3/t6U3Y8eEVInOyJeLMjBAq.q4wPg7PKy9UpJEF.HQme/JXK', '09654345674', 'Brgy. 13 Magat Laod Laoag City', '', 'customer', 0.00, '2025-10-26 21:28:00'),
(5, 'Rayan Bautista', 'ryanpalalay04@gmail.com', '$2y$10$zlIEbGp5qr/an.ZGBhv0KezlPUxtgpsFtwMtlMMtjFSGlxR8eTqUS', '09392467600', 'Brgy. Saoit, Burgos, Ilocos Norte', '', 'customer', 0.00, '2025-10-28 08:08:59'),
(6, 'doraimon', 'doraimon@gmail.com', '$2y$10$q8KwNdSLV.aL96n.40hSTOcDU6G.G3FSEuGeA2ysJQFHKt.kfuCZi', '09274473721', 'J. P. Rizal Avenue Bgy. No. 11, Laoag City, Ilocos Norte, Philippines', '', 'customer', 0.00, '2025-10-28 09:26:51'),
(7, 'Ryan Delivery', 'ryandelivery@gmail.com', '$2y$10$PGSQJnCokc5qf4QFUTFwE.P1eTjRnXiIxrf9vsODyjvb6EO/vWTUK', '09832948903', '69 Purok 7 Saoit Burgos Ilocos Norte', '', 'delivery', 0.00, '2025-11-10 19:17:11'),
(8, 'Arjay Delivery', 'arjaydelivery@gmail.com', '$2y$10$tw9v47b4EsqCvHUH3slSUOOHENqSH0r.BSnbe9StdtcggRzo1zqsm', '09873429823', '67 Sitio Papa Bangsar Banna Ilocos Norte', '', 'delivery', 0.00, '2025-11-10 19:17:57'),
(9, 'asd', 'asd@l.com', '$2y$10$wfsnm8UtIgon.gkUeQrsmuDTpnzll.x8g4P8rtxB.ocQ.OQgcEsyq', '', '', NULL, 'customer', 0.00, '2025-11-18 09:56:18'),
(10, 'Jayjayrillorta', 'Jayjayrillorta@gmail.com', '$2y$10$qhoq/buKdPdSOX9a73v.H.h8IT/AMshe.gk9LjWu86qZIzboQNxQu', '', '', NULL, 'customer', 0.00, '2025-11-18 09:57:26'),
(11, 'Loki Baltazar', 'loki@gmail', '$2y$10$hrCAGWtS9oOLNoNThooJueaUnnAGyifo5mPIuRz3xvG55Fwh6KO6O', '09999999999', 'Governor Primo Lazaro Street Bgy. No. 13, Laoag City, Ilocos Norte, Philippines', '', 'customer', 0.00, '2025-11-18 10:01:04'),
(12, 'Justine Agcanas', 'testnew@gmail.com', '$2y$10$yDBoVSYNm3PDTvJP3QIpoeXWKgOloE/ppgHO4iCWH2t98Ck64WqYO', '', '', NULL, 'customer', 0.00, '2025-11-21 18:12:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`CartID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`OrderItemID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`);

--
-- Indexes for table `productimages`
--
ALTER TABLE `productimages`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `ProductImage` (`ProductImageID`);

--
-- Indexes for table `productvariations`
--
ALTER TABLE `productvariations`
  ADD PRIMARY KEY (`VariationID`),
  ADD KEY `Product` (`ProductID`);

--
-- Indexes for table `remittance_logs`
--
ALTER TABLE `remittance_logs`
  ADD PRIMARY KEY (`RemittanceLogID`),
  ADD KEY `idx_remittance_logs_remittance_id` (`RemittanceID`);

--
-- Indexes for table `remittance_orders`
--
ALTER TABLE `remittance_orders`
  ADD PRIMARY KEY (`RemittanceID`,`OrderID`),
  ADD KEY `idx_remittance_orders_remittance_id` (`RemittanceID`),
  ADD KEY `idx_remittance_orders_order_id` (`OrderID`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`ReturnID`),
  ADD KEY `ReturnTrackingID` (`ReturnTrackingID`),
  ADD KEY `idx_customer_id` (`CustomerID`),
  ADD KEY `idx_order_id` (`OrderID`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_created_at` (`CreatedAt`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`ReturnItemID`),
  ADD KEY `ReturnID` (`ReturnID`),
  ADD KEY `OrderItemID` (`OrderItemID`);

--
-- Indexes for table `rider_remittances`
--
ALTER TABLE `rider_remittances`
  ADD PRIMARY KEY (`RemittanceID`),
  ADD KEY `idx_rider_remittances_rider_id` (`RiderID`);

--
-- Indexes for table `trackings`
--
ALTER TABLE `trackings`
  ADD PRIMARY KEY (`TrackingID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `productimages`
--
ALTER TABLE `productimages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `productvariations`
--
ALTER TABLE `productvariations`
  MODIFY `VariationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `remittance_logs`
--
ALTER TABLE `remittance_logs`
  MODIFY `RemittanceLogID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `ReturnID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `ReturnItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `rider_remittances`
--
ALTER TABLE `rider_remittances`
  MODIFY `RemittanceID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trackings`
--
ALTER TABLE `trackings`
  MODIFY `TrackingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `productvariations`
--
ALTER TABLE `productvariations`
  ADD CONSTRAINT `Product` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `users` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`ReturnTrackingID`) REFERENCES `trackings` (`TrackingID`) ON DELETE SET NULL;

--
-- Constraints for table `return_items`
--
ALTER TABLE `return_items`
  ADD CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`ReturnID`) REFERENCES `returns` (`ReturnID`) ON DELETE CASCADE,
  ADD CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`OrderItemID`) REFERENCES `orderitems` (`OrderItemID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
