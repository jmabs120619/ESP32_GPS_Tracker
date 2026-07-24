-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2026 at 10:44 AM
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
-- Database: `gps_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `alert_type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `boat_announcements`
--

CREATE TABLE `boat_announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('ONLINE','OFFLINE') DEFAULT 'OFFLINE',
  `last_seen` datetime DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `firmware` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `boat_name` varchar(100) DEFAULT NULL,
  `boat_type` varchar(100) DEFAULT NULL,
  `captain` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_name`, `device_code`, `description`, `status`, `last_seen`, `ip_address`, `firmware`, `created_at`, `boat_name`, `boat_type`, `captain`, `capacity`) VALUES
(1, 'ESP32 GPS Tracker', 'ESP32-001', 'Main GPS Device', 'ONLINE', '2026-07-23 19:11:19', NULL, NULL, '2026-07-22 02:12:17', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `gps_logs`
--

CREATE TABLE `gps_logs` (
  `id` bigint(20) NOT NULL,
  `device_id` int(11) NOT NULL,
  `trip_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `altitude` decimal(8,2) DEFAULT 0.00,
  `speed` decimal(8,2) DEFAULT 0.00,
  `satellites` int(11) DEFAULT 0,
  `gps_status` varchar(30) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `created_date` date GENERATED ALWAYS AS (cast(`recorded_at` as date)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gps_logs`
--

INSERT INTO `gps_logs` (`id`, `device_id`, `trip_id`, `latitude`, `longitude`, `altitude`, `speed`, `satellites`, `gps_status`, `recorded_at`) VALUES
(1, 1, NULL, 18.3520110, 121.6507510, 36.50, 0.17, 10, 'GPS FIX', '2026-07-22 13:50:28'),
(2, 1, NULL, 18.3520360, 121.6507640, 37.90, 0.74, 9, 'GPS FIX', '2026-07-22 13:50:31'),
(3, 1, NULL, 18.3520310, 121.6507720, 39.10, 1.67, 10, 'GPS FIX', '2026-07-22 13:50:41'),
(4, 1, NULL, 18.3520310, 121.6507720, 39.10, 1.67, 10, 'GPS FIX', '2026-07-22 13:50:44'),
(5, 1, NULL, 18.3520050, 121.6507480, 35.40, 0.56, 8, 'GPS FIX', '2026-07-22 13:50:46'),
(6, 1, NULL, 18.3520000, 121.6507470, 33.20, 0.80, 8, 'GPS FIX', '2026-07-22 13:50:53'),
(7, 1, NULL, 18.3519980, 121.6507430, 31.20, 0.33, 9, 'GPS FIX', '2026-07-22 13:50:55'),
(8, 1, NULL, 18.3519970, 121.6507410, 29.30, 0.70, 9, 'GPS FIX', '2026-07-22 13:51:00'),
(9, 1, NULL, 18.3519980, 121.6507270, 26.20, 0.15, 9, 'GPS FIX', '2026-07-22 13:51:06'),
(10, 1, NULL, 18.3519850, 121.6507060, 25.40, 2.37, 9, 'GPS FIX', '2026-07-22 13:51:13'),
(11, 1, NULL, 18.3519980, 121.6507120, 24.70, 2.80, 9, 'GPS FIX', '2026-07-22 13:51:15'),
(12, 1, NULL, 18.3520090, 121.6507250, 26.20, 2.22, 8, 'GPS FIX', '2026-07-22 13:51:20'),
(13, 1, NULL, 18.3520280, 121.6507410, 26.00, 3.15, 8, 'GPS FIX', '2026-07-22 13:51:28'),
(14, 1, NULL, 18.3520440, 121.6507600, 27.00, 1.35, 7, 'GPS FIX', '2026-07-22 13:51:31'),
(15, 1, NULL, 18.3520630, 121.6507700, 27.20, 0.69, 7, 'GPS FIX', '2026-07-22 13:51:36'),
(16, 1, NULL, 18.3520590, 121.6507660, 27.20, 1.41, 7, 'GPS FIX', '2026-07-22 13:51:41'),
(17, 1, NULL, 18.3520640, 121.6507660, 27.60, 0.17, 7, 'GPS FIX', '2026-07-22 13:51:45'),
(18, 1, NULL, 18.3520550, 121.6507490, 27.40, 1.00, 8, 'GPS FIX', '2026-07-22 13:51:54'),
(19, 1, NULL, 18.3520550, 121.6507490, 27.40, 1.00, 8, 'GPS FIX', '2026-07-22 13:51:56'),
(20, 1, NULL, 18.3520290, 121.6507150, 21.70, 0.50, 10, 'GPS FIX', '2026-07-22 13:52:04'),
(21, 1, NULL, 18.3520630, 121.6506210, 21.70, 1.80, 5, 'GPS FIX', '2026-07-22 16:39:39'),
(22, 1, NULL, 18.3521260, 121.6506370, 27.40, 0.65, 4, 'GPS FIX', '2026-07-22 16:42:12'),
(23, 1, NULL, 18.3521410, 121.6506490, 19.60, 1.17, 4, 'GPS FIX', '2026-07-22 16:44:42'),
(24, 1, NULL, 18.3522380, 121.6506510, 13.00, 0.43, 5, 'GPS FIX', '2026-07-22 16:46:57'),
(25, 1, NULL, 18.3519230, 121.6506290, 1.00, 0.56, 5, 'GPS FIX', '2026-07-22 16:48:39'),
(26, 1, NULL, 18.3520070, 121.6506330, 3.90, 1.41, 5, 'GPS FIX', '2026-07-22 16:49:12'),
(27, 1, NULL, 18.3519080, 121.6506200, 1.20, 0.07, 5, 'GPS FIX', '2026-07-22 16:49:42'),
(28, 1, NULL, 18.3520570, 121.6506430, 9.30, 0.28, 6, 'GPS FIX', '2026-07-22 16:51:42'),
(29, 1, NULL, 18.3520680, 121.6506630, 9.80, 0.11, 6, 'GPS FIX', '2026-07-22 16:52:39'),
(30, 1, NULL, 18.3520280, 121.6506410, 9.90, 1.26, 5, 'GPS FIX', '2026-07-22 16:52:42'),
(31, 1, NULL, 18.3520910, 121.6506730, 29.40, 0.22, 6, 'GPS FIX', '2026-07-23 19:03:51'),
(32, 1, NULL, 18.3520970, 121.6506990, 35.50, 0.24, 8, 'GPS FIX', '2026-07-23 19:06:49'),
(33, 1, NULL, 18.3520580, 121.6506920, 30.30, 0.44, 9, 'GPS FIX', '2026-07-23 19:07:19'),
(34, 1, NULL, 18.3520530, 121.6507150, 37.20, 0.07, 9, 'GPS FIX', '2026-07-23 19:07:49'),
(35, 1, NULL, 18.3520300, 121.6507150, 39.40, 0.07, 9, 'GPS FIX', '2026-07-23 19:08:19'),
(36, 1, NULL, 18.3519890, 121.6507070, 35.20, 0.41, 9, 'GPS FIX', '2026-07-23 19:08:49'),
(37, 1, NULL, 18.3520200, 121.6506930, 29.40, 0.11, 9, 'GPS FIX', '2026-07-23 19:09:19'),
(38, 1, NULL, 18.3520260, 121.6506850, 25.80, 0.19, 9, 'GPS FIX', '2026-07-23 19:09:49'),
(39, 1, NULL, 18.3520320, 121.6506790, 24.30, 0.13, 10, 'GPS FIX', '2026-07-23 19:10:19'),
(40, 1, NULL, 18.3519990, 121.6506700, 22.30, 0.11, 10, 'GPS FIX', '2026-07-23 19:10:49'),
(41, 1, NULL, 18.3520070, 121.6506670, 23.30, 0.33, 10, 'GPS FIX', '2026-07-23 19:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `gender` enum('MALE','FEMALE') NOT NULL,
  `age` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `emergency_contact` varchar(150) DEFAULT NULL,
  `emergency_number` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `passenger_type` enum('ADULT','CHILD','SENIOR','PWD') DEFAULT 'ADULT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`id`, `fullname`, `gender`, `age`, `address`, `contact_no`, `emergency_contact`, `emergency_number`, `created_at`, `passenger_type`) VALUES
(1, 'Juan Dela Cruz', 'MALE', 35, 'Aparri', '097171717171', 'Maria Dela Cruz', '09818181811', '2026-07-22 06:53:34', 'ADULT');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `trip_no` varchar(30) NOT NULL,
  `departure` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `captain` varchar(100) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `status` enum('PENDING','ON GOING','COMPLETED','CANCELLED') DEFAULT 'PENDING',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `trip_no`, `departure`, `destination`, `captain`, `departure_time`, `arrival_time`, `status`, `remarks`, `created_at`) VALUES
(1, 'TRIP-0001', 'Dappat', 'Bisagu', 'July', '2026-07-22 15:06:47', '2026-07-22 15:08:32', 'COMPLETED', '', '2026-07-22 07:06:47'),
(2, 'TRIP-0002', 'Dappat', 'Bisagu', 'July', '2026-07-22 15:21:27', '2026-07-22 16:27:09', 'COMPLETED', 'qweqwe', '2026-07-22 07:21:27'),
(3, 'TRIP-0003', 'Dappat', 'Bisagu', 'July', '2026-07-22 16:48:50', '2026-07-23 19:01:21', 'COMPLETED', 'ttttt', '2026-07-22 08:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `trip_passengers`
--

CREATE TABLE `trip_passengers` (
  `id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `time_boarded` datetime DEFAULT current_timestamp(),
  `time_departed` datetime DEFAULT NULL,
  `status` enum('ON BOARD','DISEMBARKED','CANCELLED') DEFAULT 'ON BOARD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_passengers`
--

INSERT INTO `trip_passengers` (`id`, `trip_id`, `passenger_id`, `time_boarded`, `time_departed`, `status`) VALUES
(2, 3, 1, '2026-07-22 16:48:59', NULL, 'ON BOARD');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('SUPER_ADMIN','ADMIN') NOT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Super Administrator', 'admin', '$2y$10$LqSEzQi37nlHKRlhNLXxJuwxnXSAtua1ji83Guvoavjo/QXUlM/li', 'SUPER_ADMIN', 'ACTIVE', '2026-07-22 06:08:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boat_announcements`
--
ALTER TABLE `boat_announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_code` (`device_code`);

--
-- Indexes for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_time` (`device_id`,`recorded_at`),
  ADD KEY `idx_created_date` (`created_date`),
  ADD KEY `fk_trip` (`trip_id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trip_no` (`trip_no`);

--
-- Indexes for table `trip_passengers`
--
ALTER TABLE `trip_passengers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `passenger_id` (`passenger_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `boat_announcements`
--
ALTER TABLE `boat_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gps_logs`
--
ALTER TABLE `gps_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trip_passengers`
--
ALTER TABLE `trip_passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD CONSTRAINT `fk_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gps_logs_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip_passengers`
--
ALTER TABLE `trip_passengers`
  ADD CONSTRAINT `trip_passengers_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_passengers_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
