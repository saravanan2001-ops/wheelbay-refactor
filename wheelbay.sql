-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 09:32 AM
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
-- Database: `wheelbay`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `mileage` int(11) NOT NULL,
  `fuel_type` varchar(20) NOT NULL,
  `transmission` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `make`, `model`, `year`, `mileage`, `fuel_type`, `transmission`, `color`, `price`, `description`, `images`, `created_at`, `updated_at`) VALUES
(3, 'Ferrari', '296 GTB (Hybrid)', 2023, 3200, 'Hybrid', 'Automatic', 'Rosso Corsa', 410000.00, 'The Ferrari 296 GTB is a revolutionary plug-in hybrid supercar that combines a 3.0L twin-turbo V6 engine with an electric motor to produce an astonishing 819 horsepower. With stunning aerodynamic design, race-inspired performance, and the ability to drive in full electric mode for short distances, this 2023 model offers a perfect blend of Ferrari heritage and futuristic technology.\\r\\n\\r\\nKey features include:\\r\\n\\r\\n0–100 km/h in 2.9 seconds\\r\\n\\r\\nTop speed over 205 mph (330 km/h)\\r\\n\\r\\nE-Diff and Side Slip Control for precise handling\\r\\n\\r\\nLuxurious interior with carbon fiber and leather finish\\r\\n\\r\\nFerrari warranty and full service history\\r\\n\\r\\nA must-have for collectors and enthusiasts. Serious buyers only.', 'car_6858fc43b80a18.85943222.jpg', '2025-06-23 07:03:31', '2025-06-23 07:03:31');

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `user_type` enum('seller','buyer') DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`id`, `full_name`, `phone_number`, `address`, `profile_image_path`, `user_type`, `registration_date`) VALUES
(1, 'Shanthos Prabakaran', '0765695939', 'sdacd', 'uploads/profile_images/a4324bd771c32724565c25b7a9ae9193.png', 'seller', '2025-06-17 18:50:39'),
(2, 'Shanthos', '0765695934', 'sdacd', 'uploads/profile_images/bea90dee6381f943210374de55687c5e.png', 'seller', '2025-06-17 19:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `car_id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','pending','sold') DEFAULT 'available',
  `year` int(11) DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`car_id`, `dealer_id`, `name`, `price`, `status`, `year`, `mileage`, `fuel_type`, `transmission`, `color`, `image_path`, `views`, `description`) VALUES
(1, 2, 'Ferrari La Ferrari', 10000.00, 'available', 2000, 1000, 'Petrol', 'Automatic', 'black', 'uploads/car_images/ca13e4ce43158062f3aa509e3187b746.jpg', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `house_no` varchar(20) NOT NULL,
  `street` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_country` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `terms_accepted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `profile_image`, `first_name`, `last_name`, `email`, `password`, `phone`, `dob`, `house_no`, `street`, `city`, `state`, `country`, `zip_code`, `license_number`, `license_country`, `license_expiry`, `created_at`, `updated_at`, `terms_accepted`) VALUES
(6, 'uploads/profile_images/68581206af572_6a062eb54391a37f.jpg', 'krish', 'krish', 'kirishoth11@gmail.com', '$2y$10$s352nVfpWjHMoU7NyB8plOrn.EW5OewL/AswGMdp6XgyMQ46R/oQK', '0761700361', '2025-07-02', '89', 'lankan', 'jaffna', 'Northen', 'LK', '40000', '123456', 'IN', '2025-06-03', '2025-06-22 14:24:06', '2025-06-22 14:24:06', 0),
(7, 'uploads/profile_images/6858e52b40ac7_c13cca8f0a411dfa.jpg', 'Sarah', 'Christopher', 'Cus@gmail.com', '$2y$10$SW57hHfbf/s/cxNDSpk7O.kt6M5RHi2kK5VLGndSMDGeAEb0g6pmy', '0761700555', '2025-06-04', '89', 'lankan', 'jaffna', 'Northen', 'LK', '40000', '1234564', 'UK', '2025-07-10', '2025-06-23 05:24:59', '2025-06-23 05:24:59', 1),
(8, 'uploads/profile_images/6858ea459ce54_81c930ce9accf0db.jpg', 'Suman', 'Kandhaiya', 'Suman@gmail.com', '$2y$10$F5Qnpr3rWoVJGjTkqYaj8uo1.bGIslOnDyG5Wtg2cZ9mu.4S/pn5S', '0761234567', '2025-06-04', '895', 'lankan', 'jaffna', 'Northen', 'LK', '40000', '65214dd6', 'AU', '2025-07-11', '2025-06-23 05:46:46', '2025-06-23 05:46:46', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`car_id`),
  ADD KEY `dealer_id` (`dealer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`license_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id` (`id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dealers`
--
ALTER TABLE `dealers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
