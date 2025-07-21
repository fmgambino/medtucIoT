-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 10:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medtuciot`
--

-- --------------------------------------------------------

--
-- Table structure for table `actuators`
--

CREATE TABLE `actuators` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `actuator_logs`
--

CREATE TABLE `actuator_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `actuator_id` varchar(50) NOT NULL,
  `state` enum('ON','OFF') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `esp32_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `place_id`, `name`, `esp32_id`) VALUES
(110, 1, 'ESP-Casa-1', 'ESPA7B0');

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`) VALUES
(1, 'Casa');

-- --------------------------------------------------------

--
-- Table structure for table `reset_logs`
--

CREATE TABLE `reset_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensors`
--

CREATE TABLE `sensors` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `port` varchar(50) NOT NULL,
  `variable` varchar(50) NOT NULL,
  `icon` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sensor_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensors`
--

INSERT INTO `sensors` (`id`, `device_id`, `name`, `port`, `variable`, `icon`, `created_at`, `sensor_type`) VALUES
(63, 110, 'Hum Suelo', 'GPIO32', 'soilHum', '🌱', '2025-07-21 07:01:17', NULL),
(64, 110, 'pH', 'GPIO33', 'ph', '🧪', '2025-07-21 07:01:17', NULL),
(65, 110, 'EC', 'GPIO36', 'ec', '⚡', '2025-07-21 07:01:17', NULL),
(66, 110, 'Nivel H₂O', 'GPIO39', 'h2o', '💧', '2025-07-21 07:01:17', NULL),
(67, 110, 'Nafta', 'GPIO25', 'nafta', '⛽', '2025-07-21 07:01:17', NULL),
(68, 110, 'Aceite', 'GPIO26', 'aceite', '🛢️', '2025-07-21 07:01:17', NULL),
(71, 110, 'LDR', 'GPIO13', 'sldr', '💡', '2025-07-21 07:15:00', NULL),
(86, 110, 'Temperatura', 'D5', 'temp', '🌡️', '2025-07-21 08:29:49', 'tempHum'),
(87, 110, 'Humedad', 'D5', 'hum', '💧', '2025-07-21 08:29:49', 'tempHum'),
(88, 110, 'CO₂', 'A0', 'co2', '⛽', '2025-07-21 08:29:49', 'mq135'),
(89, 110, 'Metano', 'A0', 'methane', '⛽', '2025-07-21 08:29:49', 'mq135'),
(90, 110, 'Butano', 'A0', 'butane', '⛽', '2025-07-21 08:29:49', 'mq135'),
(91, 110, 'Propano', 'A0', 'propane', '⛽', '2025-07-21 08:29:49', 'mq135');

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `sensor_type` varchar(50) NOT NULL,
  `value` float NOT NULL,
  `unit` varchar(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `device_id`, `sensor_type`, `value`, `unit`, `timestamp`) VALUES
(1, 110, 'temp', 19.5, '°C', '2025-07-21 08:17:39'),
(2, 110, 'hum', 32.6, '%', '2025-07-21 08:17:39'),
(3, 110, 'co2', 682, 'ppm', '2025-07-21 08:17:40'),
(4, 110, 'methane', 8, 'ppm', '2025-07-21 08:17:40'),
(5, 110, 'butane', 25, 'ppm', '2025-07-21 08:17:40'),
(6, 110, 'propane', 164, 'ppm', '2025-07-21 08:17:40'),
(7, 110, 'soilHum', 54.8, '%', '2025-07-21 08:17:40'),
(8, 110, 'ph', 6.74, '', '2025-07-21 08:17:40'),
(9, 110, 'ec', 1513, 'μS/cm', '2025-07-21 08:17:40'),
(10, 110, 'h2o', 9, '%', '2025-07-21 08:17:40'),
(11, 110, 'nafta', 88, '%', '2025-07-21 08:17:40'),
(12, 110, 'aceite', 25, '%', '2025-07-21 08:17:40'),
(13, 110, 'temp', 15.4, '°C', '2025-07-21 08:19:19'),
(14, 110, 'hum', 18.9, '%', '2025-07-21 08:19:19'),
(15, 110, 'co2', 1244, 'ppm', '2025-07-21 08:19:20'),
(16, 110, 'methane', 85, 'ppm', '2025-07-21 08:19:20'),
(17, 110, 'butane', 145, 'ppm', '2025-07-21 08:19:20'),
(18, 110, 'propane', 189, 'ppm', '2025-07-21 08:19:20'),
(19, 110, 'soilHum', 64.1, '%', '2025-07-21 08:19:20'),
(20, 110, 'ph', 6.74, '', '2025-07-21 08:19:20'),
(21, 110, 'ec', 545, 'μS/cm', '2025-07-21 08:19:20'),
(22, 110, 'h2o', 36, '%', '2025-07-21 08:19:20'),
(23, 110, 'nafta', 2, '%', '2025-07-21 08:19:20'),
(24, 110, 'aceite', 51, '%', '2025-07-21 08:19:20'),
(25, 110, 'temp', 32.4, '°C', '2025-07-21 08:20:59'),
(26, 110, 'hum', 20.3, '%', '2025-07-21 08:20:59'),
(27, 110, 'co2', 706, 'ppm', '2025-07-21 08:21:00'),
(28, 110, 'methane', 49, 'ppm', '2025-07-21 08:21:00'),
(29, 110, 'butane', 10, 'ppm', '2025-07-21 08:21:00'),
(30, 110, 'propane', 175, 'ppm', '2025-07-21 08:21:00'),
(31, 110, 'soilHum', 19.8, '%', '2025-07-21 08:21:00'),
(32, 110, 'ph', 6.18, '', '2025-07-21 08:21:00'),
(33, 110, 'ec', 1833, 'μS/cm', '2025-07-21 08:21:00'),
(34, 110, 'h2o', 57, '%', '2025-07-21 08:21:00'),
(35, 110, 'nafta', 20, '%', '2025-07-21 08:21:00'),
(36, 110, 'aceite', 67, '%', '2025-07-21 08:21:00'),
(37, 110, 'temp', 33.4, '°C', '2025-07-21 08:22:39'),
(38, 110, 'hum', 15.6, '%', '2025-07-21 08:22:39'),
(39, 110, 'co2', 325, 'ppm', '2025-07-21 08:22:40'),
(40, 110, 'methane', 55, 'ppm', '2025-07-21 08:22:40'),
(41, 110, 'butane', 179, 'ppm', '2025-07-21 08:22:40'),
(42, 110, 'propane', 198, 'ppm', '2025-07-21 08:22:40'),
(43, 110, 'soilHum', 29.3, '%', '2025-07-21 08:22:40'),
(44, 110, 'ph', 6.07, '', '2025-07-21 08:22:40'),
(45, 110, 'ec', 1499, 'μS/cm', '2025-07-21 08:22:40'),
(46, 110, 'h2o', 86, '%', '2025-07-21 08:22:40'),
(47, 110, 'nafta', 38, '%', '2025-07-21 08:22:40'),
(48, 110, 'aceite', 79, '%', '2025-07-21 08:22:40'),
(49, 110, 'temp', 16.5, '°C', '2025-07-21 08:24:19'),
(50, 110, 'hum', 21.5, '%', '2025-07-21 08:24:19'),
(51, 110, 'co2', 1530, 'ppm', '2025-07-21 08:24:20'),
(52, 110, 'methane', 44, 'ppm', '2025-07-21 08:24:20'),
(53, 110, 'butane', 11, 'ppm', '2025-07-21 08:24:20'),
(54, 110, 'propane', 39, 'ppm', '2025-07-21 08:24:20'),
(55, 110, 'soilHum', 60.6, '%', '2025-07-21 08:24:20'),
(56, 110, 'ph', 6.33, '', '2025-07-21 08:24:20'),
(57, 110, 'ec', 739, 'μS/cm', '2025-07-21 08:24:20'),
(58, 110, 'h2o', 37, '%', '2025-07-21 08:24:20'),
(59, 110, 'nafta', 48, '%', '2025-07-21 08:24:20'),
(60, 110, 'aceite', 85, '%', '2025-07-21 08:24:20'),
(61, 110, 'temp', 17.1, '°C', '2025-07-21 08:25:59'),
(62, 110, 'hum', 29.4, '%', '2025-07-21 08:25:59'),
(63, 110, 'co2', 664, 'ppm', '2025-07-21 08:26:00'),
(64, 110, 'methane', 181, 'ppm', '2025-07-21 08:26:00'),
(65, 110, 'butane', 81, 'ppm', '2025-07-21 08:26:00'),
(66, 110, 'propane', 196, 'ppm', '2025-07-21 08:26:00'),
(67, 110, 'soilHum', 67.3, '%', '2025-07-21 08:26:00'),
(68, 110, 'ph', 8.47, '', '2025-07-21 08:26:00'),
(69, 110, 'ec', 1131, 'μS/cm', '2025-07-21 08:26:00'),
(70, 110, 'h2o', 29, '%', '2025-07-21 08:26:00'),
(71, 110, 'nafta', 35, '%', '2025-07-21 08:26:00'),
(72, 110, 'aceite', 60, '%', '2025-07-21 08:26:00'),
(73, 110, 'temp', 23.1, '°C', '2025-07-21 08:27:39'),
(74, 110, 'hum', 21.4, '%', '2025-07-21 08:27:39'),
(75, 110, 'co2', 1484, 'ppm', '2025-07-21 08:27:40'),
(76, 110, 'methane', 167, 'ppm', '2025-07-21 08:27:40'),
(77, 110, 'butane', 73, 'ppm', '2025-07-21 08:27:40'),
(78, 110, 'propane', 186, 'ppm', '2025-07-21 08:27:40'),
(79, 110, 'soilHum', 51.4, '%', '2025-07-21 08:27:40'),
(80, 110, 'ph', 8.69, '', '2025-07-21 08:27:40'),
(81, 110, 'ec', 231, 'μS/cm', '2025-07-21 08:27:40'),
(82, 110, 'h2o', 24, '%', '2025-07-21 08:27:40'),
(83, 110, 'nafta', 43, '%', '2025-07-21 08:27:40'),
(84, 110, 'aceite', 78, '%', '2025-07-21 08:27:40'),
(85, 110, 'temp', 25, '°C', '2025-07-21 08:29:19'),
(86, 110, 'hum', 16.1, '%', '2025-07-21 08:29:19'),
(87, 110, 'co2', 1437, 'ppm', '2025-07-21 08:29:20'),
(88, 110, 'methane', 60, 'ppm', '2025-07-21 08:29:20'),
(89, 110, 'butane', 185, 'ppm', '2025-07-21 08:29:20'),
(90, 110, 'propane', 140, 'ppm', '2025-07-21 08:29:20'),
(91, 110, 'soilHum', 48.7, '%', '2025-07-21 08:29:20'),
(92, 110, 'ph', 8.5, '', '2025-07-21 08:29:20'),
(93, 110, 'ec', 1376, 'μS/cm', '2025-07-21 08:29:20'),
(94, 110, 'h2o', 27, '%', '2025-07-21 08:29:20'),
(95, 110, 'nafta', 54, '%', '2025-07-21 08:29:20'),
(96, 110, 'aceite', 9, '%', '2025-07-21 08:29:20'),
(97, 110, 'temp', 21.6, '°C', '2025-07-21 08:30:59'),
(98, 110, 'hum', 19.9, '%', '2025-07-21 08:30:59'),
(99, 110, 'co2', 1616, 'ppm', '2025-07-21 08:31:00'),
(100, 110, 'methane', 112, 'ppm', '2025-07-21 08:31:00'),
(101, 110, 'butane', 187, 'ppm', '2025-07-21 08:31:00'),
(102, 110, 'propane', 88, 'ppm', '2025-07-21 08:31:00'),
(103, 110, 'soilHum', 52.9, '%', '2025-07-21 08:31:00'),
(104, 110, 'ph', 7.71, '', '2025-07-21 08:31:00'),
(105, 110, 'ec', 636, 'μS/cm', '2025-07-21 08:31:00'),
(106, 110, 'h2o', 28, '%', '2025-07-21 08:31:00'),
(107, 110, 'nafta', 68, '%', '2025-07-21 08:31:00'),
(108, 110, 'aceite', 17, '%', '2025-07-21 08:31:00'),
(109, 110, 'temp', 25.3, '°C', '2025-07-21 08:32:39'),
(110, 110, 'hum', 25.8, '%', '2025-07-21 08:32:39'),
(111, 110, 'co2', 1148, 'ppm', '2025-07-21 08:32:40'),
(112, 110, 'methane', 37, 'ppm', '2025-07-21 08:32:40'),
(113, 110, 'butane', 144, 'ppm', '2025-07-21 08:32:40'),
(114, 110, 'propane', 84, 'ppm', '2025-07-21 08:32:40'),
(115, 110, 'soilHum', 55, '%', '2025-07-21 08:32:40'),
(116, 110, 'ph', 5.19, '', '2025-07-21 08:32:40'),
(117, 110, 'ec', 1318, 'μS/cm', '2025-07-21 08:32:40'),
(118, 110, 'h2o', 31, '%', '2025-07-21 08:32:40'),
(119, 110, 'nafta', 21, '%', '2025-07-21 08:32:40'),
(120, 110, 'aceite', 30, '%', '2025-07-21 08:32:40'),
(121, 110, 'temp', 27.1, '°C', '2025-07-21 08:34:20'),
(122, 110, 'hum', 26.8, '%', '2025-07-21 08:34:20'),
(123, 110, 'co2', 601, 'ppm', '2025-07-21 08:34:20'),
(124, 110, 'methane', 45, 'ppm', '2025-07-21 08:34:20'),
(125, 110, 'butane', 200, 'ppm', '2025-07-21 08:34:20'),
(126, 110, 'propane', 15, 'ppm', '2025-07-21 08:34:20'),
(127, 110, 'soilHum', 27.3, '%', '2025-07-21 08:34:20'),
(128, 110, 'ph', 5.63, '', '2025-07-21 08:34:20'),
(129, 110, 'ec', 1384, 'μS/cm', '2025-07-21 08:34:20'),
(130, 110, 'h2o', 84, '%', '2025-07-21 08:34:20'),
(131, 110, 'nafta', 85, '%', '2025-07-21 08:34:20'),
(132, 110, 'aceite', 69, '%', '2025-07-21 08:34:20'),
(133, 110, 'temp', 22.9, '°C', '2025-07-21 08:35:59'),
(134, 110, 'hum', 15.3, '%', '2025-07-21 08:35:59'),
(135, 110, 'co2', 514, 'ppm', '2025-07-21 08:36:00'),
(136, 110, 'methane', 189, 'ppm', '2025-07-21 08:36:00'),
(137, 110, 'butane', 109, 'ppm', '2025-07-21 08:36:00'),
(138, 110, 'propane', 181, 'ppm', '2025-07-21 08:36:00'),
(139, 110, 'soilHum', 35.9, '%', '2025-07-21 08:36:00'),
(140, 110, 'ph', 4.11, '', '2025-07-21 08:36:00'),
(141, 110, 'ec', 813, 'μS/cm', '2025-07-21 08:36:00'),
(142, 110, 'h2o', 69, '%', '2025-07-21 08:36:00'),
(143, 110, 'nafta', 74, '%', '2025-07-21 08:36:00'),
(144, 110, 'aceite', 7, '%', '2025-07-21 08:36:00'),
(145, 110, 'temp', 30.9, '°C', '2025-07-21 08:37:39'),
(146, 110, 'hum', 20.7, '%', '2025-07-21 08:37:39'),
(147, 110, 'co2', 1065, 'ppm', '2025-07-21 08:37:40'),
(148, 110, 'methane', 115, 'ppm', '2025-07-21 08:37:40'),
(149, 110, 'butane', 76, 'ppm', '2025-07-21 08:37:40'),
(150, 110, 'propane', 181, 'ppm', '2025-07-21 08:37:40'),
(151, 110, 'soilHum', 48.7, '%', '2025-07-21 08:37:40'),
(152, 110, 'ph', 4.38, '', '2025-07-21 08:37:40'),
(153, 110, 'ec', 1155, 'μS/cm', '2025-07-21 08:37:40'),
(154, 110, 'h2o', 52, '%', '2025-07-21 08:37:40'),
(155, 110, 'nafta', 29, '%', '2025-07-21 08:37:40'),
(156, 110, 'aceite', 45, '%', '2025-07-21 08:37:40'),
(157, 110, 'temp', 18.4, '°C', '2025-07-21 08:39:19'),
(158, 110, 'hum', 24.7, '%', '2025-07-21 08:39:19'),
(159, 110, 'co2', 366, 'ppm', '2025-07-21 08:39:20'),
(160, 110, 'methane', 8, 'ppm', '2025-07-21 08:39:20'),
(161, 110, 'butane', 39, 'ppm', '2025-07-21 08:39:20'),
(162, 110, 'propane', 49, 'ppm', '2025-07-21 08:39:20'),
(163, 110, 'soilHum', 46.6, '%', '2025-07-21 08:39:20'),
(164, 110, 'ph', 6.32, '', '2025-07-21 08:39:20'),
(165, 110, 'ec', 1466, 'μS/cm', '2025-07-21 08:39:20'),
(166, 110, 'h2o', 31, '%', '2025-07-21 08:39:20'),
(167, 110, 'nafta', 68, '%', '2025-07-21 08:39:20'),
(168, 110, 'aceite', 92, '%', '2025-07-21 08:39:20'),
(169, 110, 'temp', 25.6, '°C', '2025-07-21 08:40:59'),
(170, 110, 'hum', 18, '%', '2025-07-21 08:40:59'),
(171, 110, 'co2', 576, 'ppm', '2025-07-21 08:41:00'),
(172, 110, 'methane', 82, 'ppm', '2025-07-21 08:41:00'),
(173, 110, 'butane', 88, 'ppm', '2025-07-21 08:41:00'),
(174, 110, 'propane', 91, 'ppm', '2025-07-21 08:41:00'),
(175, 110, 'soilHum', 75, '%', '2025-07-21 08:41:00'),
(176, 110, 'ph', 5.31, '', '2025-07-21 08:41:00'),
(177, 110, 'ec', 332, 'μS/cm', '2025-07-21 08:41:00'),
(178, 110, 'h2o', 41, '%', '2025-07-21 08:41:00'),
(179, 110, 'nafta', 45, '%', '2025-07-21 08:41:00'),
(180, 110, 'aceite', 71, '%', '2025-07-21 08:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `subscription_type` enum('Prospecto','client','admin') NOT NULL DEFAULT 'Prospecto',
  `role` enum('admin','client') NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`first_name`, `last_name`, `country`, `province`, `city`, `email`, `id`, `username`, `password_hash`, `subscription_type`, `role`, `profile_image`, `created_at`) VALUES
('Fernando', 'Gambino', 'Argentina', 'Tucumán', 'San Miguel de tucumán', 'fernando.m.gambino@gmail.com', 1, '', '$2y$10$snSkc4Bqhn5u/r1jLO9IWuIh.r.ZKqFIC9R7lxwwNXKuAsClJ4vFm', 'Prospecto', 'admin', 'default.png', '2025-07-19 22:30:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actuators`
--
ALTER TABLE `actuators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `actuator_logs`
--
ALTER TABLE `actuator_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `actuator_id` (`actuator_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reset_logs`
--
ALTER TABLE `reset_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actuators`
--
ALTER TABLE `actuators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `actuator_logs`
--
ALTER TABLE `actuator_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reset_logs`
--
ALTER TABLE `reset_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensors`
--
ALTER TABLE `sensors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actuators`
--
ALTER TABLE `actuators`
  ADD CONSTRAINT `actuators_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reset_logs`
--
ALTER TABLE `reset_logs`
  ADD CONSTRAINT `reset_logs_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reset_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sensors`
--
ALTER TABLE `sensors`
  ADD CONSTRAINT `sensors_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD CONSTRAINT `sensor_data_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
