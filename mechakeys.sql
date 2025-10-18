-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 03:25 PM
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
  `Quantity` int(11) NOT NULL,
  `Price` decimal(5,2) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `SubTotal` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `TrackingID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL,
  `TotalAmount` decimal(5,2) NOT NULL,
  `PlaceOrdered` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `Amount` decimal(5,2) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `TransactionDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(12, 1, 'mechakeys/products/keyboards/AulaF75/image9.png');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Description` text NOT NULL,
  `Category` varchar(20) NOT NULL,
  `Price` decimal(5,2) NOT NULL,
  `TotalSold` int(11) NOT NULL,
  `ProductImageID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Name`, `Description`, `Category`, `Price`, `TotalSold`, `ProductImageID`) VALUES
(3, 'Aula F75', 'Ultimate Versatility and Performance with Aula F75 3-in-1 Mechanical Keyboard\r\n\r\nTake your gaming and productivity to the next level with the Aula F75 3-in-1 Gaming Mechanical Keyboard. Designed for gamers and multitaskers alike, this keyboard combines advanced mechanical performance with unmatched flexibility—featuring three connection modes, including Type-C wired, 2.4G wireless, and Bluetooth.\r\n\r\nThe gasket mount construction delivers a softer, quieter typing experience, while hot-swappable mechanical switches allow you to easily customize your keys without soldering. With RGB lighting effects, a compact 80-key layout, and a built-in rechargeable lithium battery, the Aula F75 offers everything you need for a smooth and stylish setup.\r\n\r\n\r\nKey Features and Specifications:\r\n- 3 Connection Modes: Type-C Wired, 2.4G Wireless, and Bluetooth\r\n- Hot-Swappable Mechanical Switches for easy switch replacement\r\n- Gasket Mount Construction for a more cushioned and silent typing experience\r\n- 80-Key Compact Layout for space-saving functionality without losing essential keys\r\n- RGB Switchable Lighting Effects to match your mood or setup\r\n- Rechargeable Lithium Battery for extended wireless use\r\n- Pluggable Design – easily switch out keys or connect across devices\r\n\r\n\r\n️✍ Product Specification：\r\n● Product Name: AULA F75\r\n● Key count: 80 keys\r\n● Rated voltage: DC 3.7V (fully charged with 4.2V)\r\n● Battery capacity: 4000mAh rechargeable lithium battery\r\n● Product weight: approximately 1023g (including wire/receiver)\r\n● Transmission method: Bluetooth/2.4G/wired\r\n● Total travel of buttons: 4.0mm\r\n● Voltage/current: DC 5V ≌ 700mA\r\n● Charging interface: Type-C interface\r\n● Product size: 322.7 * 143.2 * 43.1 ± 1mm\r\n● Accessories: Key puller * 1, switch * 2, data cable * 1, instruction manual * 1\r\n\r\n\r\nWhether you\'re a competitive gamer or a productivity-focused professional, the Aula F75 delivers top-tier performance with the flexibility of three connection modes.', 'keyboard', 999.99, 3, 1);

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
  `StockQuantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productvariations`
--

INSERT INTO `productvariations` (`VariationID`, `ProductID`, `Layout`, `SwitchType`, `Color`, `StockQuantity`) VALUES
(1, 3, 75, 'Reaper SW', 'Blue', 15),
(2, 3, 75, 'Reaper SW', 'Black', 12),
(3, 3, 75, 'Reaper SW', 'Light Blue', 8);

-- --------------------------------------------------------

--
-- Table structure for table `trackings`
--

CREATE TABLE `trackings` (
  `TrackingID` int(11) NOT NULL,
  `CourierName` varchar(50) NOT NULL,
  `TrackingNumber` varchar(20) NOT NULL,
  `DeliveryStatus` varchar(20) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `Role` varchar(10) NOT NULL,
  `DateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `FullName`, `Email`, `Password`, `Contact`, `Address`, `Role`, `DateCreated`) VALUES
(1, 'Justine Agcanas', 'customer@gmail.com', '123456', '', '', 'customer', '2025-10-16 20:57:36'),
(2, 'Admin Justine', 'admin@gmail.com', '123456', '', '', 'admin', '2025-10-16 21:01:44');

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
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `Customer` (`CustomerID`),
  ADD KEY `Payment` (`PaymentID`),
  ADD KEY `Tracking` (`TrackingID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `Order` (`OrderID`);

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
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productimages`
--
ALTER TABLE `productimages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `productvariations`
--
ALTER TABLE `productvariations`
  MODIFY `VariationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trackings`
--
ALTER TABLE `trackings`
  MODIFY `TrackingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `Customer` FOREIGN KEY (`CustomerID`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `Payment` FOREIGN KEY (`PaymentID`) REFERENCES `payments` (`PaymentID`),
  ADD CONSTRAINT `Tracking` FOREIGN KEY (`TrackingID`) REFERENCES `trackings` (`TrackingID`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `Order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);

--
-- Constraints for table `productvariations`
--
ALTER TABLE `productvariations`
  ADD CONSTRAINT `Product` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
