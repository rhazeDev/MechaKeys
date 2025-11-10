-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 12:52 PM
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
(20, 1, 'Order Placed Successfully', 'Your order #9 has been placed successfully. Total: ₱17,543.00', 'order', 'unread', '2025-10-29 20:58:20'),
(21, 1, 'Order Approved', 'Your order #9 has been approved and is now being processed.', 'order', 'unread', '2025-10-29 20:59:41'),
(22, 1, 'Order Placed Successfully', 'Your order #10 has been placed successfully. Total: ₱13,998.00', 'order', 'unread', '2025-10-29 21:00:12'),
(23, 1, 'Order Cancelled', 'Your order #10 has been cancelled.', 'order', 'unread', '2025-10-29 21:00:26'),
(24, 1, 'Order Status Updated', 'Your order #9 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending', 'order', 'unread', '2025-11-02 22:17:41'),
(25, 1, 'Delivery Status Updated', 'Your order #9 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-02 22:23:47'),
(26, 1, 'Delivery Status Updated', 'Your order #9 is on the way to you!', 'delivery', 'unread', '2025-11-02 22:23:57'),
(27, 1, 'Delivery Status Updated', 'Your order #9 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-02 22:24:17'),
(28, 1, 'Delivery Status Updated', 'Your order #9 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-02 22:25:20'),
(29, 1, 'Delivery Status Updated', 'Your order #9 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-02 22:25:26'),
(30, 1, 'Order Placed Successfully', 'Your order #11 has been placed successfully. Total: ₱7,899.00', 'order', 'unread', '2025-11-05 21:35:48'),
(31, 1, 'Delivery Status Updated', 'Your order #9 is on the way to you!', 'delivery', 'unread', '2025-11-05 21:44:29'),
(32, 1, 'Order Approved', 'Your order #11 has been approved and is now being processed.', 'order', 'unread', '2025-11-05 21:48:58'),
(33, 1, 'Delivery Status Updated', 'Your order #9 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-05 23:19:43'),
(34, 1, 'Order Status Updated', 'Your order #11 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-05 23:20:26'),
(35, 1, 'Delivery Status Updated', 'Your order #11 is on the way to you!', 'delivery', 'unread', '2025-11-05 23:20:34'),
(36, 1, 'Delivery Status Updated', 'Your order #11 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:09:50'),
(37, 1, 'Delivery Status Updated', 'Your order #11 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:09:53'),
(38, 1, 'Delivery Status Updated', 'Your order #11 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-08 21:11:24'),
(39, 1, 'Order Placed Successfully', 'Your order #12 has been placed successfully. Total: ₱7,899.00', 'order', 'unread', '2025-11-08 21:23:45'),
(40, 1, 'Order Approved', 'Your order #12 has been approved and is now being processed.', 'order', 'unread', '2025-11-08 21:24:38'),
(41, 1, 'Order Status Updated', 'Your order #12 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-08 21:24:42'),
(42, 1, 'Delivery Status Updated', 'Your order #12 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:24:49'),
(43, 1, 'Delivery Status Updated', 'Your order #12 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:24:52'),
(44, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:24:53'),
(45, 1, 'Delivery Status Updated', 'Your order #12 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:26:01'),
(46, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:26:12'),
(47, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:27:07'),
(48, 1, 'Order Placed Successfully', 'Your order #13 has been placed successfully. Total: ₱4,200.00', 'order', 'unread', '2025-11-08 21:36:00'),
(49, 1, 'Order Approved', 'Your order #13 has been approved and is now being processed.', 'order', 'unread', '2025-11-08 21:38:17'),
(50, 1, 'Order Status Updated', 'Your order #13 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-08 21:38:21'),
(51, 1, 'Delivery Status Updated', 'Your order #13 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:38:26'),
(52, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:53:59'),
(53, 1, 'Delivery Status Updated', 'Your order #13 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:56:11'),
(54, 1, 'Delivery Status Updated', 'Your order #13 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:56:14'),
(55, 1, 'Delivery Status Updated', 'Your order #12 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 21:57:33'),
(56, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 21:57:35'),
(57, 1, 'Delivery Status Updated', 'Your order #12 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-08 22:00:24'),
(58, 1, 'Delivery Status Updated', 'Your order #12 is on the way to you!', 'delivery', 'unread', '2025-11-08 22:00:27'),
(59, 1, 'Delivery Status Updated', 'Your order #13 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-08 22:03:53'),
(60, 1, 'Return Request Submitted', 'Your return request for order #13 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-09 16:20:40'),
(61, 1, 'Return Rejected', 'Your return request for order #13 has been rejected. Reason: ta rupam', 'return', 'unread', '2025-11-09 16:32:20'),
(62, 1, 'Order Placed Successfully', 'Your order #14 has been placed successfully. Total: ₱2,999.00', 'order', 'unread', '2025-11-09 16:34:08'),
(63, 1, 'Order Approved', 'Your order #14 has been approved and is now being processed.', 'order', 'unread', '2025-11-09 16:34:26'),
(64, 1, 'Order Status Updated', 'Your order #14 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-09 16:34:30'),
(65, 1, 'Delivery Status Updated', 'Your order #14 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-09 16:37:08'),
(66, 1, 'Return Request Submitted', 'Your return request for order #14 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-09 16:37:38'),
(67, 1, 'Return Approved', 'Your return request for order #14 has been approved. A delivery rider will pick up the item from your address.', 'return', 'unread', '2025-11-09 16:38:12'),
(68, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:15'),
(69, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:21'),
(70, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:22'),
(71, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:22'),
(72, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:23'),
(73, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:34:31'),
(74, 1, 'Return Pickup Status Updated', 'Your return for order #14 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 19:40:19'),
(75, 1, 'Return Pickup Status Updated', 'Your return for order #14 has been picked up and processed. Refund will be issued soon.', 'return', 'unread', '2025-11-09 19:40:42'),
(76, 1, 'Delivery Status Updated', 'Your order #12 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-09 19:43:06'),
(77, 1, 'Order Placed Successfully', 'Your order #15 has been placed successfully. Total: ₱6,999.00', 'order', 'unread', '2025-11-09 20:29:03'),
(78, 1, 'Order Approved', 'Your order #15 has been approved and is now being processed.', 'order', 'unread', '2025-11-09 20:29:23'),
(79, 1, 'Order Status Updated', 'Your order #15 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-09 20:29:27'),
(80, 1, 'Delivery Status Updated', 'Your order #15 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-09 20:29:54'),
(81, 1, 'Delivery Status Updated', 'Your order #15 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-09 20:31:06'),
(82, 1, 'Delivery Status Updated', 'Your order #15 is on the way to you!', 'delivery', 'unread', '2025-11-09 20:31:26'),
(83, 1, 'Delivery Status Updated', 'Your order #15 is on the way to you!', 'delivery', 'unread', '2025-11-09 20:34:20'),
(84, 1, 'Delivery Status Updated', 'Your order #15 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-09 20:38:43'),
(85, 1, 'Return Request Submitted', 'Your return request for order #15 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-09 20:39:11'),
(86, 1, 'Return Approved', 'Your return request for order #15 has been approved. A delivery rider will pick up the item from your address.', 'return', 'unread', '2025-11-09 21:02:46'),
(87, 1, 'Return Pickup Status Updated', 'Your return for order #15 is being transported to our warehouse.', 'return', 'unread', '2025-11-09 21:03:47'),
(88, 1, 'Return Pickup Status Updated', 'Your return for order #15 has been picked up and processed. Refund will be issued soon.', 'return', 'unread', '2025-11-09 21:04:38'),
(89, 1, 'Order Placed Successfully', 'Your order #16 has been placed successfully. Total: ₱10,639.00', 'order', 'unread', '2025-11-10 19:15:10'),
(90, 1, 'Order Approved', 'Your order #16 has been approved and is now being processed.', 'order', 'unread', '2025-11-10 19:15:45'),
(91, 1, 'Order Status Updated', 'Your order #16 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-10 19:15:52'),
(92, 1, 'Delivery Status Updated', 'Your order #16 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-10 19:18:16'),
(93, 1, 'Delivery Status Updated', 'Your order #16 is on the way to you!', 'delivery', 'unread', '2025-11-10 19:18:27'),
(94, 1, 'Delivery Status Updated', 'Your order #16 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-10 19:20:20'),
(95, 1, 'Return Request Submitted', 'Your return request for order #16 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-10 19:20:56'),
(96, 1, 'Return Rejected', 'Your return request for order #16 has been rejected. Reason: taulom isublim', 'return', 'unread', '2025-11-10 19:21:25'),
(97, 1, 'Order Placed Successfully', 'Your order #17 has been placed successfully. Total: ₱9,770.00', 'order', 'unread', '2025-11-10 19:29:08'),
(98, 1, 'Order Approved', 'Your order #17 has been approved and is now being processed.', 'order', 'unread', '2025-11-10 19:34:26'),
(99, 1, 'Order Status Updated', 'Your order #17 has been updated. Delivery Status: Ready to Deliver, Payment Status: Pending. Your order has been assigned to delivery rider: Justine D', 'order', 'unread', '2025-11-10 19:34:31'),
(100, 1, 'Delivery Status Updated', 'Your order #17 has been picked up and is on the way.', 'delivery', 'unread', '2025-11-10 19:34:43'),
(101, 1, 'Delivery Status Updated', 'Your order #17 has been delivered. Thank you!', 'delivery', 'unread', '2025-11-10 19:38:55'),
(102, 1, 'Return Request Submitted', 'Your return request for order #17 has been submitted. Awaiting admin approval.', 'return', 'unread', '2025-11-10 19:39:40'),
(103, 1, 'Return Approved', 'Your return request for order #17 has been approved. A delivery rider will pick up the item from your address.', 'return', 'unread', '2025-11-10 19:40:23'),
(104, 1, 'Return Pickup Status Updated', 'Your return for order #17 is being transported to our warehouse.', 'return', 'unread', '2025-11-10 19:40:53'),
(105, 1, 'Return Pickup Status Updated', 'Your return for order #17 has been picked up and processed. Refund will be issued soon.', 'return', 'unread', '2025-11-10 19:41:54'),
(106, 1, 'Order Placed Successfully', 'Your order #18 has been placed successfully. Total: ₱2,999.00 (Discount with coins: ₱100.00)', 'order', 'unread', '2025-11-10 19:47:57');

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
(19, 9, 12, 14, 1, 2249.00),
(20, 9, 10, 25, 1, 7899.00),
(21, 9, 9, 11, 1, 7395.00),
(22, 10, 9, 28, 2, 13998.00),
(23, 11, 10, 25, 1, 7899.00),
(24, 12, 10, 25, 1, 7899.00),
(25, 13, 5, 5, 1, 4200.00),
(26, 14, 3, 2, 1, 2999.00),
(27, 15, 9, 26, 1, 6999.00),
(28, 16, 13, 15, 2, 2740.00),
(29, 16, 10, 25, 1, 7899.00),
(30, 17, 5, 5, 2, 8400.00),
(31, 17, 13, 15, 1, 1370.00),
(32, 18, 3, 1, 1, 2999.00);

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
(9, 1, 12, 13, 0.00, 17543.00, '2025-10-29 20:58:20'),
(10, 1, 13, 14, 0.00, 13998.00, '2025-10-29 21:00:12'),
(11, 1, 14, 15, 0.00, 7899.00, '2025-11-05 21:35:48'),
(12, 1, 15, 16, 0.00, 7899.00, '2025-11-08 21:23:45'),
(13, 1, 16, 17, 0.00, 4200.00, '2025-11-08 21:36:00'),
(14, 1, 17, 18, 0.00, 2999.00, '2025-11-09 16:34:08'),
(15, 1, 20, 19, 0.00, 6999.00, '2025-11-09 20:29:03'),
(16, 1, 23, 20, 0.00, 10639.00, '2025-11-10 19:15:10'),
(17, 1, 24, 21, 0.00, 9770.00, '2025-11-10 19:29:08'),
(18, 1, 26, 22, 0.00, 2999.00, '2025-11-10 19:47:57');

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
(13, 9, 17543.00, 'Pending', '2025-10-29 20:58:20'),
(14, 10, 13998.00, 'Pending', '2025-10-29 21:00:12'),
(15, 11, 7899.00, 'Pending', '2025-11-05 21:35:48'),
(16, 12, 7899.00, 'Pending', '2025-11-08 21:23:45'),
(17, 13, 4200.00, 'Pending', '2025-11-08 21:36:00'),
(18, 14, 2999.00, 'Pending', '2025-11-09 16:34:08'),
(19, 15, 6999.00, 'Pending', '2025-11-09 20:29:03'),
(20, 16, 10639.00, 'Pending', '2025-11-10 19:15:10'),
(21, 17, 9770.00, 'Pending', '2025-11-10 19:29:08'),
(22, 18, 2899.00, 'Pending', '2025-11-10 19:47:57');

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
(29, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image4.png'),
(30, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image2.png'),
(31, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image1.png'),
(32, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image3.png'),
(33, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image5.png'),
(34, 7, 'mechakeys/products/keyboards/RoyalKludgeRKM87Famicom/image6.png'),
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
(65, 13, 'mechakeys/products/switchess/N/AAkko2Pack-90PcsRosewoodSwitches/image3.png');

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
(3, 'Aula', 'F75', 'Ultimate Versatility and Performance with Aula F75 3-in-1 Mechanical Keyboard\r\n\r\nTake your gaming and productivity to the next level with the Aula F75 3-in-1 Gaming Mechanical Keyboard. Designed for gamers and multitaskers alike, this keyboard combines advanced mechanical performance with unmatched flexibility—featuring three connection modes, including Type-C wired, 2.4G wireless, and Bluetooth.\r\n\r\nThe gasket mount construction delivers a softer, quieter typing experience, while hot-swappable mechanical switches allow you to easily customize your keys without soldering. With RGB lighting effects, a compact 80-key layout, and a built-in rechargeable lithium battery, the Aula F75 offers everything you need for a smooth and stylish setup.\r\n\r\n\r\nKey Features and Specifications:\r\n- 3 Connection Modes: Type-C Wired, 2.4G Wireless, and Bluetooth\r\n- Hot-Swappable Mechanical Switches for easy switch replacement\r\n- Gasket Mount Construction for a more cushioned and silent typing experience\r\n- 80-Key Compact Layout for space-saving functionality without losing essential keys\r\n- RGB Switchable Lighting Effects to match your mood or setup\r\n- Rechargeable Lithium Battery for extended wireless use\r\n- Pluggable Design – easily switch out keys or connect across devices\r\n\r\n\r\n️✍ Product Specification：\r\n● Product Name: AULA F75\r\n● Key count: 80 keys\r\n● Rated voltage: DC 3.7V (fully charged with 4.2V)\r\n● Battery capacity: 4000mAh rechargeable lithium battery\r\n● Product weight: approximately 1023g (including wire/receiver)\r\n● Transmission method: Bluetooth/2.4G/wired\r\n● Total travel of buttons: 4.0mm\r\n● Voltage/current: DC 5V ≌ 700mA\r\n● Charging interface: Type-C interface\r\n● Product size: 322.7 * 143.2 * 43.1 ± 1mm\r\n● Accessories: Key puller * 1, switch * 2, data cable * 1, instruction manual * 1\r\n\r\n\r\nWhether you\'re a competitive gamer or a productivity-focused professional, the Aula F75 delivers top-tier performance with the flexibility of three connection modes.', 'keyboard', 21, 1),
(5, 'Monsgeek', 'M1 V5', 'An excellent upgrade from the previous version, featuring Tool-free Rapid Assembly and Disassembly for effortless customization. It’s both budget-friendly and accessible for beginners and advanced users alike.\r\n\r\n\r\nAdditional highlights include:\r\n- 8000mAh battery for long-lasting, stable performance;\r\n- Multi-mode connectivity, supporting up to 5 devices simultaneously;\r\n- Separate Encoder for Enhanced Stability;\r\n- VIA support for easy key remapping and RGB customization.\r\n\r\nDisclaimer: This model supports customization via VIA only. MonsGeek Driver is not supported.\r\n\r\n\r\nModel: M1 V5 VIA Rapid Disassembly\r\nSpecifications\r\nMount: Gasket\r\nCase Material: Aluminum\r\nLayout: ANSI\r\nConnection: USB-C Wired & 2.4G Wireless & Bluetooth\r\nLED: RGB\r\nHotswap: Y (5-pin)\r\nPCB Thickness: 1.2mm\r\nStabilizers: Pre-assembled Plated-mounted Stabilizers; Separate Screw-in Stabilizers\r\nBattery: 8000mAh\r\nPlate: PC\r\nPlate Foam: Y\r\nSwitch Pad: Y\r\nTape Mod: N\r\nCase Foam: Y\r\nVIA: Y\r\nMonsGeek Driver: N', 'keyboard', 11, 5),
(6, 'Monsgeek', 'MG108B Rainbow Marshmallows', 'MonsGeek x Akko Rainbow Marshmallows Keyboard features rainbow-color Marshmallow with playful emojis\r\n\r\nThis full-sized keyboard offers a comprehensive range of function keys and a numeric pad, making it ideal for gamers, office professionals, programmers, and typists alike.\r\n\r\nMulti-modes (Bluetooth 5.0, 2.4Ghz, and Type-C);\r\n\r\nMOG Profile Dye-sub Keycaps;\r\n\r\n5-pin Hotswappable;\r\n\r\nRGB Backlit;\r\n\r\nKeys Remapping and RGB Customization through MonsGeek Driver.', 'keyboard', 0, 6),
(7, 'Royal Kludge', 'RK M87 Famicom', 'RK Royal Kludge RK M87 Famicom Keyboard captures the iconic aesthetic of the classic 8-bit era with its signature red, white, and grey color block design, evoking a powerful sense of nostalgia for gaming pioneers. This isn\'t just a keyboard; it\'s a functional piece of retro art for your modern desk setup.\r\n\r\nThis compact 88-key TenKeyLess (TKL) layout eliminates the number pad to free up valuable desk space for broader mouse movements, making it a top choice for competitive gamers, minimalists, and anyone seeking an efficient and ergonomic workspace without sacrificing the core function row.\r\n\r\nKey Features:\r\n\r\nIntelligent Tri-Mode Connectivity: Effortlessly switch between three connection methods. Pair with up to three devices via Bluetooth for ultimate flexibility, use the included 2.4GHz wireless dongle for a lag-free gaming experience, or connect via USB-Type C for reliable, pass-through charging and wired use.\r\n\r\nInteractive TFT Color Display: The standout feature is the vibrant TFT screen that goes beyond simple indicators. It provides real-time system data like battery life, connection mode, and Caps/Num Lock status. Crucially, it allows you to upload custom GIFs and images, making your keyboard a truly unique centerpiece.\r\n\r\n5-Pin Hotswappable PCB: Embrace the custom keyboard hobby with a beginner-friendly hot-swap socket design. This allows you to effortlessly install or change any compatible 3-pin or 5-pin mechanical switches to tailor the actuation force and feel—be it linear, tactile, or clicky—to your personal preference, all without soldering.\r\n\r\nDynamic RGB Backlighting: Beneath the retro keycaps lies a modern, vibrant RGB lighting system. Choose from a spectrum of pre-installed lighting effects and colors to match your mood or setup. The shine-through keycap legends ensure perfect visibility, day or night.\r\n\r\nComprehensive Software Suite: Unlock the full potential of your keyboard with the RK Official Software. This powerful tool allows for deep customization, including advanced key remapping, complex macro programming, fine-tuning of every RGB lighting mode, and managing the content displayed on the TFT screen.\r\n\r\nLong-Lasting Battery & Robust Build: Engineered for both wireless freedom and durability, the keyboard is equipped with a high-capacity battery for extended use and features a solid construction that ensures stability during intense typing or gaming sessions.', 'keyboard', 0, 7),
(8, 'Keychron', 'K3 Max QMK/VIA ', 'The Keychron K3 Max is an ultra-slim 75% layout mechanical keyboard engineered for enthusiasts who demand both portability and uncompromising customization. It combines a sleek, low-profile form factor with the powerful, open-source QMK and VIA software, allowing for deep, real-time personalization without the need for complex flashing.\r\n\r\nThis compact (75%) layout retains the crucial function and navigation keys while significantly reducing the keyboard\'s footprint, making it the perfect centerpiece for a clean, efficient desk setup. It is ideally suited for programmers, writers, and power users who need extensive key customization while saving space.\r\n\r\nSwitch Types:\r\n\r\nRed (K3M-A1): A smooth and consistent linear switch, offering quiet keystrokes with no tactile bump. Ideal for fast-paced gaming and rapid, fluid typing.\r\n\r\n** Brown (K3M-A3): A subtle tactile switch that provides a gentle bump for feedback without being loud. Perfect for those who want the assurance of a tactile response in office or shared environments.\r\n\r\nKey Features:\r\n\r\nUnmatched Customization with QMK/VIA: The standout feature of the K3 Max is its support for the open-source QMK firmware and VIA remapping software. This allows you to reprogram every key, create complex macros, and design sophisticated layered functions directly through a simple web interface, all in real-time.\r\n\r\nTri-Mode Wireless Connectivity: Enjoy complete cable-free freedom. Switch seamlessly between Bluetooth 5.1 for connecting up to three devices, the low-latency 2.4GHz wireless mode for a reliable gaming connection, or the wired USB-C mode for continuous use and charging.\r\n\r\nSlim & Portable Design: With its low-profile keycaps and slim aluminum frame, the Kron K3 Max is incredibly portable and ergonomic, reducing strain on your wrists during long typing sessions. It’s designed to be your go-to keyboard for both desk use and on-the-go productivity.\r\n\r\nHot-Swappable Sockets: The keyboard features hot-swappable sockets, enabling you to easily change between the included low-profile optical switches without any soldering. This future-proofs your investment and lets you experiment with different switch feels.\r\n\r\nWhite Backlighting: Features a clean and elegant white backlight (south-facing) with multiple lighting effects. It provides excellent key visibility in low-light conditions while maintaining a professional and minimalist aesthetic.\r\n\r\nRobust Build & macOS/Windows Compatibility: Crafted with a durable aluminum frame and high-quality keycaps, the K3 Max is built to last. It offers dedicated keycaps and full functional support for both macOS and Windows operating systems.', 'keyboard', 0, 8),
(9, 'Logitech', 'MX Mechanical', 'The Logitech MX Mechanical is a sophisticated wireless keyboard engineered for professionals and power users who demand precision, comfort, and seamless workflow integration. It combines a modern, low-profile design with smart features that adapt to your environment, creating a truly intelligent typing experience.\r\n\r\nAvailable in both a comprehensive Full-size layout with an integrated number pad and a space-saving Mini (75% compact) layout, it caters to different desk setups and user preferences without sacrificing functionality. The compact version retains essential navigation keys for efficiency.\r\n\r\nSwitch Types:\r\n\r\nTactile Quiet: Provides a subtle, satisfying bump for accurate typing with minimal sound, perfect for shared workspaces like offices and home setups.\r\n\r\nClicky: Offers an audible click and a distinct tactile feedback for a classic, responsive mechanical typing feel that boosts confidence and rhythm.\r\n\r\nLinear: Delivers a smooth, consistent keystroke from top to bottom with a quiet operation, ideal for fast, fluid typing and gaming.\r\n\r\nKey Features:\r\n\r\nSmart Illuminated Keys: The adaptive backlighting automatically adjusts its brightness based on ambient light conditions to conserve power. The proximity sensor detects your hands, waking the keyboard and lighting up the keys as you approach. Individual keycaps are also edge-lit for superior visibility in any lighting.\r\n\r\nAdvanced Multi-Device Pairing: Effortlessly connect and switch between up to three different devices (Windows, macOS, iOS, Android) using either Bluetooth Low Energy or the included Logitech Unifying USB receiver. Seamlessly flow your work from a PC to a laptop or a tablet.\r\n\r\nLogi Options+ Software Customization: Unlock the full potential of the MX Mechanical with the Logi Options+ software. Remap keys, assign complex shortcuts and emoji commands to the F-key row, and create app-specific profiles that automatically switch based on the active application.\r\n\r\nEcosystem Integration with Logi Bolt: For enhanced security and reliable performance in crowded wireless environments, it supports the Logi Bolt receiver, ensuring a robust connection with your computer.\r\n\r\nComfortable Low-Profile Design: The carefully sculpted, low-profile keycaps are optimized for comfort and stability, reducing finger fatigue and promoting a more natural typing posture during extended use.\r\n\r\nUSB-C Quick Charging: A full-speed USB-C port provides convenience. A full charge delivers up to 15 days of use with backlighting on, or up to 10 months with backlighting off, ensuring exceptional battery life for uninterrupted productivity.\r\n\r\nPremium Build & Sustainable Materials: Built with a solid, minimalist aluminum frame for durability and stability, it is also designed with sustainability in mind, using post-consumer recycled plastic in its keycaps and housing. Available in Graphite and Pale Grey to complement any professional workspace.', 'keyboard', 2, 9),
(10, 'RAKK', 'Hanan Ultra', 'Discover the RAKK Hanan Ultra Mechanical Keyboard, a meticulously crafted tool engineered for peak performance and enduring quality. Designed for enthusiasts who refuse to compromise, it combines a sophisticated gasket-mount structure within a durable aluminum CNC-milled case, offering a uniquely refined typing experience that is both crisp and satisfyingly muted.\r\n\r\nThis compact 81-key (75%) layout efficiently maximizes desk space by eliminating the number pad while retaining the essential function and navigation cluster, making it the ultimate choice for gamers requiring mouse real estate, professionals seeking a minimalist setup, and touch-typists who value efficiency.\r\n\r\nKey Features:\r\n\r\nGasket Mount Structure: The keyboard is equipped with a premium gasket mount system, where the PCB is suspended by soft silicone gaskets instead of being hard-mounted. This innovative design absorbs keystroke impact and minimizes metal-on-metal resonance, resulting in a uniquely softer, more consistent, and poppy typing sound across the entire board.\r\n\r\nUnibody Aluminum CNC Case: Machined from a solid block of aluminum, the case provides exceptional heft, durability, and resistance to flex. The pristine white finish not only offers a sleek, modern aesthetic but also ensures the keyboard remains a stable foundation during intense typing or gaming sessions.\r\n\r\nVersatile Tri-Mode Connectivity: Enjoy complete wireless freedom and a reliable wired connection. Switch effortlessly between Bluetooth 5.0 for multi-device pairing, a lag-free 2.4GHz wireless connection for competitive gaming, and a wired USB-Type C mode for uninterrupted use and charging.\r\n\r\n5-Pin Hot-Swappable PCB: The heart of customization, the hot-swappable sockets allow you to easily install or change any compatible 3-pin or 5-pin mechanical switches without soldering. This empowers you to tailor the actuation force and feedback—be it linear, tactile, or clicky—to your exact preference.\r\n\r\nDynamic RGB Backlighting: Experience a vibrant light show with fully customizable per-key RGB lighting. With a wide spectrum of colors and numerous pre-installed effects, you can personalize your setup\'s ambiance while ensuring perfect key visibility in any environment.\r\n\r\nFull Software Support: Unlock the keyboard\'s full potential with dedicated software. Remap any key, create complex macros, and fine-tune every aspect of the RGB lighting effects to match your workflow and gaming style perfectly.\r\n\r\nPremium Keycaps & Stabilizers: Equipped with high-quality, dye-sublimated PBT keycaps that resist shine and fading over time, ensuring legends remain crisp. The pre-lubricated screw-in stabilizers are meticulously tuned to eliminate wire rattle, providing a smooth and consistent feel for larger keys like the spacebar and shift.\r\n\r\n', 'keyboard', 4, 10),
(11, 'N/A', 'Akko 2 Pack-90 Pcs Cilantro Switches', 'Cilantro switches are Akko’s first deep clack tactile switches.\r\nIt comes with an early bump at very top, with strong tactile feedback.\r\n\r\nSwitches Parameter\r\nAkko Cilantro Switches\r\nType: Early Tactile\r\nOperating Force: 36 ± 5gf\r\nTotal Travel: 3.5 ± 0.3mm\r\nPre-Travel: 2.1 ± 0.5mm\r\nTactile Travel: 0.2 ± 0.2mm\r\nTactile Force: 58 ± 5gf', 'switches', 0, 11),
(12, 'N/A', 'Akko Kuromi Keycap Set (138-key)', 'Akko x Kuromi Limited Edition Keycap Set\r\nModel：Kuromi Keycap Set (138-key)\r\n\r\n\r\nSpecification\r\n- MOA profile;\r\n- PBT Dye-Sub Keycaps\r\n-  With advanced 5-sided dye sublimation technology, the keycaps feature vibrant and colorful printing across every surface. Made from durable PBT materials, the legends will not fade easily.\r\n - Compatible with major-sizes keyboard including but not limited to 60%, 64-key, 65%, TKL, 75%, 96%, 1800 compact, and full-size keyboards.', 'keycaps', 1, 12),
(13, 'N/A', 'Akko 2 Pack-90 Pcs Rosewood Switches', 'Rosewood is born with the resolution of making a nice low-pitch switch for our MU01 wooden case keyboard.\r\n\r\nMajor Changes:\r\n -  The signature deep sound provides a pleasant thocky sound out of box with a thin layer of factory lubrication.\r\n -  The switch features our custom Nylon (Pro) stem, a PA12 blend top cover, and a PA6 bottom housing, creating a unique material combination.\r\n -  Maintaining the classic and nostalgic 4.0mm total travel, the 22mm spring ensures a responsive feel without any mushiness.\r\n -  5-pin and fits keycaps with standard MX structure.\r\n -  FYI. If you are looking for a slightly high-pitch version, please check our Botany Switches (TBD).\r\n\r\n\r\nSwitches Parameter\r\n\r\n\r\nAkko Rosewood Switches\r\n\r\nType: Linear\r\nOperating Force: 40 ± 5gf\r\nPre-Travel: 2.0 ± 0.5mm\r\nTotal Travel: 4.0mm\r\nTactile Travel: N/A\r\nTactile Force: N/A', 'switches', 3, 13);

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
(1, 3, 75, 'Reaper SW', 'Blue', 2999.00, 8),
(2, 3, 75, 'Reaper SW', 'Black', 2999.00, 15),
(3, 3, 75, 'Reaper SW', 'Light Blue', 3000.00, 21),
(4, 5, 75, 'Piano Pro', 'Moonlight Black', 4200.00, 16),
(5, 5, 75, 'Cilantro Green', 'Moonlight Black', 4200.00, 12),
(6, 5, 75, 'Rosewood', 'White', 4600.00, 26),
(7, 5, 75, 'Piano Pro', 'White', 4600.00, 21),
(8, 6, 100, 'Akko V3 Piano Pro Switch, Akko Creamy Cyan Switch', 'Rainbow Marshmallows', 5699.00, 15),
(9, 7, 75, 'Chartreuse', 'Famicom', 4320.00, 31),
(10, 8, 80, 'Red (Linear – K3M-A1)', 'Gray', 5590.00, 13),
(11, 9, 75, 'Logitech Tactile Quiet', ' Graphite', 7395.00, 12),
(12, 10, 75, 'Linear', 'Blue', 7899.00, 41),
(13, 11, 0, '', '', 1370.00, 43),
(14, 12, 0, '', '', 2249.00, 31),
(15, 13, 0, '', '', 1370.00, 92),
(16, 7, 75, 'Blue', 'Famicom', 4320.00, 21),
(17, 7, 75, 'Brown', 'Famicom', 4320.00, 22),
(18, 7, 75, 'Beige', 'Famicom', 4320.00, 12),
(19, 7, 75, 'Chartreuse', 'Ocean Blue', 4320.00, 33),
(20, 7, 75, 'Brown', 'Ocean Blue', 4320.00, 15),
(21, 10, 75, 'Quiet', 'Blue', 7899.00, 12),
(22, 10, 75, 'Clicky', 'Blue', 7899.00, 19),
(23, 10, 75, 'Linear', 'Red', 7899.00, 19),
(24, 10, 75, 'Clicky', 'Red', 7899.00, 18),
(25, 10, 75, 'Clicky', 'Green', 7899.00, 22),
(26, 9, 75, 'Logitech Tactile Quiet', 'Pale Grey', 6999.00, 8),
(27, 9, 75, 'Clicky', 'Pale Grey', 6999.00, 12),
(28, 9, 75, 'Linear', 'Pale Grey', 6999.00, 9),
(29, 8, 80, 'Brown (Tactile – K3M-A3)', 'Gray', 5590.00, 11);

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
(1, 13, 1, 'nagalas yt', 'mechakeys/proofofdelivery/returns/proof_return_13_1762676440_bf0ab617.webp', 'Rejected', 'ta rupam', NULL, '2025-11-09 16:20:40', '2025-11-09 16:32:20'),
(2, 14, 1, 'adda red na yt', 'mechakeys/proofofdelivery/returns/proof_return_14_1762677458_3e452e0f.jpg', 'Returned', NULL, 18, '2025-11-09 16:37:38', '2025-11-09 19:40:42'),
(3, 15, 1, 'test return. deflective', 'mechakeys/proofofdelivery/returns/proof_return_15_1762691951_7960cb91.jpg', 'Returned', NULL, 22, '2025-11-09 20:39:11', '2025-11-09 21:04:38'),
(4, 16, 1, 'Try return item', 'mechakeys/proofofdelivery/returns/proof_return_16_1762773656_1b9a591f.png', 'Rejected', 'taulom isublim', NULL, '2025-11-10 19:20:56', '2025-11-10 19:21:25'),
(5, 17, 1, 'test return. approve', 'mechakeys/proofofdelivery/returns/proof_return_17_1762774780_1feca6b7.png', 'Returned', NULL, 25, '2025-11-10 19:39:40', '2025-11-10 19:41:54');

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
(1, 1, 25, 4200.00),
(2, 2, 26, 2999.00),
(3, 3, 27, 6999.00),
(4, 4, 28, 2740.00),
(5, 4, 29, 7899.00),
(6, 5, 30, 8400.00),
(7, 5, 31, 1370.00);

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
(12, 3, 'Delivered', '', NULL, '2025-11-05 23:19:43'),
(13, 0, 'Cancelled', '', NULL, '2025-10-29 21:00:26'),
(14, 3, 'Delivered', '', NULL, '2025-11-08 21:11:24'),
(15, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_15_1762688586_2f64eff3.jpg', '2025-11-09 19:43:06'),
(16, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_16_1762610633_8336c843.jpg', '2025-11-08 22:03:53'),
(17, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_17_1762677428_e01081b9.jpg', '2025-11-09 16:37:08'),
(18, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_18_1762688442_04dbd041.jpg', '2025-11-09 19:40:42'),
(20, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_20_1762691923_7d845595.jpg', '2025-11-09 20:38:43'),
(22, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_22_1762693478_93a1d78f.jpg', '2025-11-09 21:04:38'),
(23, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_23_1762773620_5419f8a9.jpg', '2025-11-10 19:20:20'),
(24, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_24_1762774735_f77889ef.jpg', '2025-11-10 19:38:55'),
(25, 3, 'Delivered', '', 'mechakeys/proofofdelivery/proof_25_1762774914_4244e19e.jpg', '2025-11-10 19:41:54'),
(26, 0, 'Pending', '', NULL, '2025-11-10 19:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `FullName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Password` varchar(20) NOT NULL,
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
(1, 'Justine Agcanas', 'customer@gmail.com', '123456', '09182738829', 'J. P. Rizal Avenue Bgy. No. 1, Laoag City, Ilocos Norte, Philippines', '120.58544005175321,18.1991155548574', 'customer', 0.00, '2025-10-16 20:57:36'),
(2, 'Admin Justine', 'admin@gmail.com', '123456', '', '', '', 'admin', 0.00, '2025-10-16 21:01:44'),
(3, 'Justine Delivery', 'delivery@gmail.com', '123456', '09283028474', 'J. P. Rizal Avenue Bgy. No. 1, Laoag City, Ilocos Norte, Philippines', '120.6842717,18.0767717', 'delivery', 0.00, '2025-10-26 19:16:54'),
(4, 'John Burat', 'johnburat@gmail.com', 'Qwerty123', '09654345674', 'Brgy. 13 Magat Laod Laoag City', '', 'customer', 0.00, '2025-10-26 21:28:00'),
(5, 'Rayan Bautista', 'ryanpalalay04@gmail.com', 'shibal123', '09392467600', 'Brgy. Saoit, Burgos, Ilocos Norte', '', 'customer', 0.00, '2025-10-28 08:08:59'),
(6, 'doraimon', 'doraimon@gmail.com', 'doraimon', '09274473721', 'J. P. Rizal Avenue Bgy. No. 11, Laoag City, Ilocos Norte, Philippines', '120.59134538126494,18.19673683978084', 'customer', 0.00, '2025-10-28 09:26:51'),
(7, 'Ryan Delivery', 'ryandelivery@gmail.com', '@Qwerty123', '09832948903', '69 Purok 7 Saoit Burgos Ilocos Norte', NULL, 'delivery', 0.00, '2025-11-10 19:17:11'),
(8, 'Arjay Delivery', 'arjaydelivery@gmail.com', '@Qwerty123', '09873429823', '67 Sitio Papa Bangsar Banna Ilocos Norte', NULL, 'delivery', 0.00, '2025-11-10 19:17:57');

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
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `productimages`
--
ALTER TABLE `productimages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `productvariations`
--
ALTER TABLE `productvariations`
  MODIFY `VariationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `ReturnID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `ReturnItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trackings`
--
ALTER TABLE `trackings`
  MODIFY `TrackingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
