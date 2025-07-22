-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2025 at 10:16 PM
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
(91, 110, 'Propano', 'A0', 'propane', '⛽', '2025-07-21 08:29:49', 'mq135'),
(92, 110, 'Voltaje [V]', 'GPIO24', 'sVoltaDC', '⚡', '2025-07-21 17:56:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `sensor_type` varchar(50) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `unit` varchar(20) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `device_id`, `sensor_type`, `value`, `unit`, `timestamp`) VALUES
(1, 110, 'tempHum', '{\"temperature\": 27, \"humidity\": 61.7}', '', '2025-07-21 23:57:05'),
(2, 110, 'temperature', '27.0', '°C', '2025-07-21 23:57:05'),
(3, 110, 'humidity', '61.7', '%', '2025-07-21 23:57:05'),
(4, 110, 'mq135', '{\"co2\": 1402, \"methane\": 40, \"butane\": 38, \"propane\": 12}', '', '2025-07-21 23:57:05'),
(5, 110, 'soilHum', '54.2', '%', '2025-07-21 23:57:05'),
(6, 110, 'ph', '7.25', '', '2025-07-21 23:57:05'),
(7, 110, 'ec', '1745.0', 'μS/cm', '2025-07-21 23:57:05'),
(8, 110, 'h2o', '27.0', '%', '2025-07-21 23:57:05'),
(9, 110, 'nafta', '69.0', '%', '2025-07-21 23:57:05'),
(10, 110, 'aceite', '33.0', '%', '2025-07-21 23:57:05'),
(11, 110, 'tempHum', '{\"temperature\": 32, \"humidity\": 46.8}', '', '2025-07-21 23:57:16'),
(12, 110, 'temperature', '32.0', '°C', '2025-07-21 23:57:16'),
(13, 110, 'humidity', '46.8', '%', '2025-07-21 23:57:16'),
(14, 110, 'mq135', '{\"co2\": 1333, \"methane\": 77, \"butane\": 110, \"propane\": 114}', '', '2025-07-21 23:57:16'),
(15, 110, 'soilHum', '72.7', '%', '2025-07-21 23:57:18'),
(16, 110, 'ph', '8.19', '', '2025-07-21 23:57:18'),
(17, 110, 'ec', '1812.0', 'μS/cm', '2025-07-21 23:57:18'),
(18, 110, 'h2o', '37.0', '%', '2025-07-21 23:57:18'),
(19, 110, 'nafta', '8.0', '%', '2025-07-21 23:57:18'),
(20, 110, 'aceite', '88.0', '%', '2025-07-21 23:57:18'),
(21, 110, 'tempHum', '{\"temperature\": 26.5, \"humidity\": 65.1}', '', '2025-07-21 23:57:33'),
(22, 110, 'temperature', '26.5', '°C', '2025-07-21 23:57:34'),
(23, 110, 'humidity', '65.1', '%', '2025-07-21 23:57:34'),
(24, 110, 'mq135', '{\"co2\": 1989, \"methane\": 88, \"butane\": 11, \"propane\": 52}', '', '2025-07-21 23:57:35'),
(25, 110, 'soilHum', '32.0', '%', '2025-07-21 23:57:35'),
(26, 110, 'ph', '4.47', '', '2025-07-21 23:57:35'),
(27, 110, 'ec', '413.0', 'μS/cm', '2025-07-21 23:57:35'),
(28, 110, 'h2o', '56.0', '%', '2025-07-21 23:57:35'),
(29, 110, 'nafta', '58.0', '%', '2025-07-21 23:57:35'),
(30, 110, 'aceite', '8.0', '%', '2025-07-21 23:57:35'),
(31, 110, 'tempHum', '{\"temperature\": 27.1, \"humidity\": 45.9}', '', '2025-07-21 23:57:46'),
(32, 110, 'temperature', '27.1', '°C', '2025-07-21 23:57:47'),
(33, 110, 'humidity', '45.9', '%', '2025-07-21 23:57:47'),
(34, 110, 'mq135', '{\"co2\": 1335, \"methane\": 191, \"butane\": 173, \"propane\": 83}', '', '2025-07-21 23:57:47'),
(35, 110, 'soilHum', '46.0', '%', '2025-07-21 23:57:47'),
(36, 110, 'ph', '8.33', '', '2025-07-21 23:57:47'),
(37, 110, 'ec', '610.0', 'μS/cm', '2025-07-21 23:57:47'),
(38, 110, 'h2o', '94.0', '%', '2025-07-21 23:57:47'),
(39, 110, 'nafta', '65.0', '%', '2025-07-21 23:57:47'),
(40, 110, 'aceite', '98.0', '%', '2025-07-21 23:57:47'),
(41, 110, 'tempHum', '{\"temperature\": 21.4, \"humidity\": 46.6}', '', '2025-07-21 23:58:01'),
(42, 110, 'temperature', '21.4', '°C', '2025-07-21 23:58:01'),
(43, 110, 'humidity', '46.6', '%', '2025-07-21 23:58:01'),
(44, 110, 'mq135', '{\"co2\": 633, \"methane\": 24, \"butane\": 39, \"propane\": 11}', '', '2025-07-21 23:58:01'),
(45, 110, 'soilHum', '73.0', '%', '2025-07-21 23:58:01'),
(46, 110, 'ph', '6.74', '', '2025-07-21 23:58:01'),
(47, 110, 'ec', '1414.0', 'μS/cm', '2025-07-21 23:58:01'),
(48, 110, 'h2o', '2.0', '%', '2025-07-21 23:58:01'),
(49, 110, 'nafta', '73.0', '%', '2025-07-21 23:58:01'),
(50, 110, 'aceite', '69.0', '%', '2025-07-21 23:58:01'),
(51, 110, 'tempHum', '{\"temperature\": 27.1, \"humidity\": 72.9}', '', '2025-07-21 23:58:20'),
(52, 110, 'temperature', '27.1', '°C', '2025-07-21 23:58:25'),
(53, 110, 'humidity', '72.9', '%', '2025-07-21 23:58:25'),
(54, 110, 'mq135', '{\"co2\": 1308, \"methane\": 169, \"butane\": 26, \"propane\": 158}', '', '2025-07-21 23:58:33'),
(55, 110, 'soilHum', '57.1', '%', '2025-07-21 23:58:45'),
(56, 110, 'ph', '7.47', '', '2025-07-21 23:58:45'),
(57, 110, 'ec', '1018.0', 'μS/cm', '2025-07-21 23:58:45'),
(58, 110, 'h2o', '24.0', '%', '2025-07-21 23:58:45'),
(59, 110, 'nafta', '21.0', '%', '2025-07-21 23:58:45'),
(60, 110, 'aceite', '34.0', '%', '2025-07-21 23:58:45'),
(61, 110, 'tempHum', '{\"temperature\": 24.7, \"humidity\": 49.9}', '', '2025-07-21 23:58:45'),
(62, 110, 'temperature', '24.7', '°C', '2025-07-21 23:58:45'),
(63, 110, 'humidity', '49.9', '%', '2025-07-21 23:58:45'),
(64, 110, 'mq135', '{\"co2\": 1607, \"methane\": 134, \"butane\": 120, \"propane\": 95}', '', '2025-07-21 23:58:45'),
(65, 110, 'soilHum', '55.4', '%', '2025-07-21 23:58:46'),
(66, 110, 'ph', '8.85', '', '2025-07-21 23:58:46'),
(67, 110, 'ec', '1576.0', 'μS/cm', '2025-07-21 23:58:46'),
(68, 110, 'h2o', '74.0', '%', '2025-07-21 23:58:46'),
(69, 110, 'nafta', '96.0', '%', '2025-07-21 23:58:46'),
(70, 110, 'aceite', '81.0', '%', '2025-07-21 23:58:46'),
(71, 110, 'tempHum', '{\"temperature\": 30.4, \"humidity\": 59.2}', '', '2025-07-21 23:58:46'),
(72, 110, 'temperature', '30.4', '°C', '2025-07-21 23:58:46'),
(73, 110, 'humidity', '59.2', '%', '2025-07-21 23:58:46'),
(74, 110, 'mq135', '{\"co2\": 1374, \"methane\": 72, \"butane\": 192, \"propane\": 189}', '', '2025-07-21 23:58:46'),
(75, 110, 'soilHum', '72.7', '%', '2025-07-21 23:58:46'),
(76, 110, 'ph', '5.77', '', '2025-07-21 23:58:46'),
(77, 110, 'ec', '807.0', 'μS/cm', '2025-07-21 23:58:46'),
(78, 110, 'h2o', '62.0', '%', '2025-07-21 23:58:46'),
(79, 110, 'nafta', '62.0', '%', '2025-07-21 23:58:46'),
(80, 110, 'aceite', '42.0', '%', '2025-07-21 23:58:46'),
(81, 110, 'tempHum', '{\"temperature\": 20.6, \"humidity\": 60}', '', '2025-07-21 23:59:01'),
(82, 110, 'temperature', '20.6', '°C', '2025-07-21 23:59:01'),
(83, 110, 'humidity', '60.0', '%', '2025-07-21 23:59:01'),
(84, 110, 'mq135', '{\"co2\": 1988, \"methane\": 77, \"butane\": 100, \"propane\": 106}', '', '2025-07-21 23:59:02'),
(85, 110, 'soilHum', '40.7', '%', '2025-07-21 23:59:06'),
(86, 110, 'ph', '6.99', '', '2025-07-21 23:59:06'),
(87, 110, 'ec', '1669.0', 'μS/cm', '2025-07-21 23:59:06'),
(88, 110, 'h2o', '61.0', '%', '2025-07-21 23:59:06'),
(89, 110, 'nafta', '38.0', '%', '2025-07-21 23:59:06'),
(90, 110, 'aceite', '22.0', '%', '2025-07-21 23:59:06'),
(91, 110, 'mq135', '{\"co2\": 1988, \"methane\": 77, \"butane\": 100, \"propane\": 106}', '', '2025-07-22 00:01:35'),
(92, 110, 'tempHum', '{\"temperature\": 20.6, \"humidity\": 60}', '', '2025-07-22 00:01:35'),
(93, 110, 'temperature', '20.6', '°C', '2025-07-22 00:01:35'),
(94, 110, 'humidity', '60.0', '%', '2025-07-22 00:01:35'),
(95, 110, 'mq135', '{\"co2\": 1988, \"methane\": 77, \"butane\": 100, \"propane\": 106}', '', '2025-07-22 00:04:51'),
(96, 110, 'tempHum', '{\"temperature\": 20.6, \"humidity\": 60}', '', '2025-07-22 00:04:51'),
(97, 110, 'temperature', '20.6', '°C', '2025-07-22 00:04:51'),
(98, 110, 'humidity', '60.0', '%', '2025-07-22 00:04:51'),
(99, 110, 'tempHum', '{\"temperature\": 34.9, \"humidity\": 40.5}', '', '2025-07-22 01:04:57'),
(100, 110, 'temperature', '34.9', '°C', '2025-07-22 01:04:57'),
(101, 110, 'humidity', '40.5', '%', '2025-07-22 01:04:57'),
(102, 110, 'mq135', '{\"co2\": 464, \"methane\": 158, \"butane\": 4, \"propane\": 39}', '', '2025-07-22 01:04:57'),
(103, 110, 'soilHum', '54.4', '%', '2025-07-22 01:04:57'),
(104, 110, 'ph', '6.12', '', '2025-07-22 01:04:57'),
(105, 110, 'ec', '1502.0', 'μS/cm', '2025-07-22 01:04:57'),
(106, 110, 'h2o', '94.0', '%', '2025-07-22 01:04:57'),
(107, 110, 'nafta', '68.0', '%', '2025-07-22 01:04:57'),
(108, 110, 'aceite', '55.0', '%', '2025-07-22 01:04:57'),
(109, 110, 'tempHum', '{\"temperature\": 29.9, \"humidity\": 73.6}', '', '2025-07-22 02:04:57'),
(110, 110, 'temperature', '29.9', '°C', '2025-07-22 02:04:57'),
(111, 110, 'humidity', '73.6', '%', '2025-07-22 02:04:57'),
(112, 110, 'mq135', '{\"co2\": 1749, \"methane\": 139, \"butane\": 99, \"propane\": 54}', '', '2025-07-22 02:04:57'),
(113, 110, 'soilHum', '46.9', '%', '2025-07-22 02:04:57'),
(114, 110, 'ph', '7.87', '', '2025-07-22 02:04:57'),
(115, 110, 'ec', '910.0', 'μS/cm', '2025-07-22 02:04:57'),
(116, 110, 'h2o', '2.0', '%', '2025-07-22 02:04:57'),
(117, 110, 'nafta', '13.0', '%', '2025-07-22 02:04:57'),
(118, 110, 'aceite', '94.0', '%', '2025-07-22 02:04:57'),
(119, 110, 'tempHum', '{\"temperature\": 33.8, \"humidity\": 53.4}', '', '2025-07-22 03:04:57'),
(120, 110, 'temperature', '33.8', '°C', '2025-07-22 03:04:57'),
(121, 110, 'humidity', '53.4', '%', '2025-07-22 03:04:57'),
(122, 110, 'mq135', '{\"co2\": 1161, \"methane\": 180, \"butane\": 56, \"propane\": 72}', '', '2025-07-22 03:04:57'),
(123, 110, 'soilHum', '23.5', '%', '2025-07-22 03:04:57'),
(124, 110, 'ph', '6.65', '', '2025-07-22 03:04:57'),
(125, 110, 'ec', '1153.0', 'μS/cm', '2025-07-22 03:04:57'),
(126, 110, 'h2o', '12.0', '%', '2025-07-22 03:04:57'),
(127, 110, 'nafta', '12.0', '%', '2025-07-22 03:04:57'),
(128, 110, 'aceite', '35.0', '%', '2025-07-22 03:04:57'),
(129, 110, 'tempHum', '{\"temperature\": 20.4, \"humidity\": 47.4}', '', '2025-07-22 04:04:57'),
(130, 110, 'temperature', '20.4', '°C', '2025-07-22 04:04:57'),
(131, 110, 'humidity', '47.4', '%', '2025-07-22 04:04:57'),
(132, 110, 'mq135', '{\"co2\": 655, \"methane\": 98, \"butane\": 64, \"propane\": 39}', '', '2025-07-22 04:04:57'),
(133, 110, 'soilHum', '67.2', '%', '2025-07-22 04:04:57'),
(134, 110, 'ph', '7.73', '', '2025-07-22 04:04:57'),
(135, 110, 'ec', '1840.0', 'μS/cm', '2025-07-22 04:04:57'),
(136, 110, 'h2o', '69.0', '%', '2025-07-22 04:04:57'),
(137, 110, 'nafta', '10.0', '%', '2025-07-22 04:04:57'),
(138, 110, 'aceite', '54.0', '%', '2025-07-22 04:04:57'),
(139, 110, 'tempHum', '{\"temperature\": 25.5, \"humidity\": 68.5}', '', '2025-07-22 05:04:56'),
(140, 110, 'temperature', '25.5', '°C', '2025-07-22 05:04:56'),
(141, 110, 'humidity', '68.5', '%', '2025-07-22 05:04:56'),
(142, 110, 'mq135', '{\"co2\": 1912, \"methane\": 175, \"butane\": 105, \"propane\": 141}', '', '2025-07-22 05:04:57'),
(143, 110, 'soilHum', '60.8', '%', '2025-07-22 05:04:57'),
(144, 110, 'ph', '7.52', '', '2025-07-22 05:04:57'),
(145, 110, 'ec', '1508.0', 'μS/cm', '2025-07-22 05:04:57'),
(146, 110, 'h2o', '87.0', '%', '2025-07-22 05:04:57'),
(147, 110, 'nafta', '20.0', '%', '2025-07-22 05:04:57'),
(148, 110, 'aceite', '30.0', '%', '2025-07-22 05:04:57'),
(149, 110, 'tempHum', '{\"temperature\": 26.2, \"humidity\": 60}', '', '2025-07-22 06:04:56'),
(150, 110, 'temperature', '26.2', '°C', '2025-07-22 06:04:56'),
(151, 110, 'humidity', '60.0', '%', '2025-07-22 06:04:56'),
(152, 110, 'mq135', '{\"co2\": 799, \"methane\": 51, \"butane\": 174, \"propane\": 35}', '', '2025-07-22 06:04:57'),
(153, 110, 'soilHum', '57.3', '%', '2025-07-22 06:04:57'),
(154, 110, 'ph', '4.9', '', '2025-07-22 06:04:57'),
(155, 110, 'ec', '1808.0', 'μS/cm', '2025-07-22 06:04:57'),
(156, 110, 'h2o', '68.0', '%', '2025-07-22 06:04:57'),
(157, 110, 'nafta', '91.0', '%', '2025-07-22 06:04:57'),
(158, 110, 'aceite', '94.0', '%', '2025-07-22 06:04:57'),
(159, 110, 'tempHum', '{\"temperature\": 30.5, \"humidity\": 56.8}', '', '2025-07-22 07:04:56'),
(160, 110, 'temperature', '30.5', '°C', '2025-07-22 07:04:56'),
(161, 110, 'humidity', '56.8', '%', '2025-07-22 07:04:56'),
(162, 110, 'mq135', '{\"co2\": 1092, \"methane\": 57, \"butane\": 142, \"propane\": 126}', '', '2025-07-22 07:04:57'),
(163, 110, 'soilHum', '25.7', '%', '2025-07-22 07:04:57'),
(164, 110, 'ph', '6.78', '', '2025-07-22 07:04:57'),
(165, 110, 'ec', '1596.0', 'μS/cm', '2025-07-22 07:04:57'),
(166, 110, 'h2o', '65.0', '%', '2025-07-22 07:04:57'),
(167, 110, 'nafta', '1.0', '%', '2025-07-22 07:04:57'),
(168, 110, 'aceite', '64.0', '%', '2025-07-22 07:04:57'),
(169, 110, 'tempHum', '{\"temperature\": 23.7, \"humidity\": 42.2}', '', '2025-07-22 08:04:56'),
(170, 110, 'temperature', '23.7', '°C', '2025-07-22 08:04:56'),
(171, 110, 'humidity', '42.2', '%', '2025-07-22 08:04:56'),
(172, 110, 'mq135', '{\"co2\": 1143, \"methane\": 57, \"butane\": 100, \"propane\": 127}', '', '2025-07-22 08:04:56'),
(173, 110, 'soilHum', '64.4', '%', '2025-07-22 08:04:56'),
(174, 110, 'ph', '6.51', '', '2025-07-22 08:04:56'),
(175, 110, 'ec', '1337.0', 'μS/cm', '2025-07-22 08:04:56'),
(176, 110, 'h2o', '61.0', '%', '2025-07-22 08:04:56'),
(177, 110, 'nafta', '98.0', '%', '2025-07-22 08:04:56'),
(178, 110, 'aceite', '38.0', '%', '2025-07-22 08:04:56'),
(179, 110, 'tempHum', '{\"temperature\": 30.2, \"humidity\": 61.6}', '', '2025-07-22 09:04:56'),
(180, 110, 'temperature', '30.2', '°C', '2025-07-22 09:04:56'),
(181, 110, 'humidity', '61.6', '%', '2025-07-22 09:04:56'),
(182, 110, 'mq135', '{\"co2\": 1991, \"methane\": 63, \"butane\": 199, \"propane\": 120}', '', '2025-07-22 09:04:56'),
(183, 110, 'soilHum', '42.7', '%', '2025-07-22 09:04:56'),
(184, 110, 'ph', '6.45', '', '2025-07-22 09:04:56'),
(185, 110, 'ec', '783.0', 'μS/cm', '2025-07-22 09:04:56'),
(186, 110, 'h2o', '4.0', '%', '2025-07-22 09:04:57'),
(187, 110, 'nafta', '29.0', '%', '2025-07-22 09:04:57'),
(188, 110, 'aceite', '84.0', '%', '2025-07-22 09:04:57'),
(189, 110, 'tempHum', '{\"temperature\": 29.6, \"humidity\": 70.6}', '', '2025-07-22 10:04:56'),
(190, 110, 'temperature', '29.6', '°C', '2025-07-22 10:04:56'),
(191, 110, 'humidity', '70.6', '%', '2025-07-22 10:04:56'),
(192, 110, 'mq135', '{\"co2\": 1439, \"methane\": 121, \"butane\": 58, \"propane\": 1}', '', '2025-07-22 10:04:56'),
(193, 110, 'soilHum', '81.1', '%', '2025-07-22 10:04:56'),
(194, 110, 'ph', '7.64', '', '2025-07-22 10:04:56'),
(195, 110, 'ec', '938.0', 'μS/cm', '2025-07-22 10:04:56'),
(196, 110, 'h2o', '44.0', '%', '2025-07-22 10:04:56'),
(197, 110, 'nafta', '90.0', '%', '2025-07-22 10:04:56'),
(198, 110, 'aceite', '98.0', '%', '2025-07-22 10:04:56'),
(199, 110, 'tempHum', '{\"temperature\": 30.7, \"humidity\": 42.9}', '', '2025-07-22 11:04:56'),
(200, 110, 'temperature', '30.7', '°C', '2025-07-22 11:04:56'),
(201, 110, 'humidity', '42.9', '%', '2025-07-22 11:04:56'),
(202, 110, 'mq135', '{\"co2\": 966, \"methane\": 14, \"butane\": 165, \"propane\": 73}', '', '2025-07-22 11:04:56'),
(203, 110, 'soilHum', '34.3', '%', '2025-07-22 11:04:56'),
(204, 110, 'ph', '8.94', '', '2025-07-22 11:04:56'),
(205, 110, 'ec', '1336.0', 'μS/cm', '2025-07-22 11:04:56'),
(206, 110, 'h2o', '53.0', '%', '2025-07-22 11:04:56'),
(207, 110, 'nafta', '88.0', '%', '2025-07-22 11:04:56'),
(208, 110, 'aceite', '92.0', '%', '2025-07-22 11:04:56'),
(209, 110, 'tempHum', '{\"temperature\": 31.1, \"humidity\": 57.4}', '', '2025-07-22 12:04:56'),
(210, 110, 'temperature', '31.1', '°C', '2025-07-22 12:04:56'),
(211, 110, 'humidity', '57.4', '%', '2025-07-22 12:04:56'),
(212, 110, 'mq135', '{\"co2\": 1864, \"methane\": 109, \"butane\": 34, \"propane\": 173}', '', '2025-07-22 12:04:56'),
(213, 110, 'soilHum', '49.7', '%', '2025-07-22 12:04:56'),
(214, 110, 'ph', '8.72', '', '2025-07-22 12:04:56'),
(215, 110, 'ec', '1735.0', 'μS/cm', '2025-07-22 12:04:56'),
(216, 110, 'h2o', '44.0', '%', '2025-07-22 12:04:56'),
(217, 110, 'nafta', '77.0', '%', '2025-07-22 12:04:56'),
(218, 110, 'aceite', '99.0', '%', '2025-07-22 12:04:56'),
(219, 110, 'tempHum', '{\"temperature\": 23.9, \"humidity\": 61.1}', '', '2025-07-22 13:04:56'),
(220, 110, 'temperature', '23.9', '°C', '2025-07-22 13:04:56'),
(221, 110, 'humidity', '61.1', '%', '2025-07-22 13:04:56'),
(222, 110, 'mq135', '{\"co2\": 746, \"methane\": 155, \"butane\": 129, \"propane\": 42}', '', '2025-07-22 13:04:56'),
(223, 110, 'soilHum', '88.7', '%', '2025-07-22 13:04:56'),
(224, 110, 'ph', '4.27', '', '2025-07-22 13:04:56'),
(225, 110, 'ec', '1302.0', 'μS/cm', '2025-07-22 13:04:56'),
(226, 110, 'h2o', '12.0', '%', '2025-07-22 13:04:56'),
(227, 110, 'nafta', '32.0', '%', '2025-07-22 13:04:56'),
(228, 110, 'aceite', '20.0', '%', '2025-07-22 13:04:56'),
(229, 110, 'tempHum', '{\"temperature\": 30.4, \"humidity\": 59.2}', '', '2025-07-22 14:04:56'),
(230, 110, 'temperature', '30.4', '°C', '2025-07-22 14:04:56'),
(231, 110, 'humidity', '59.2', '%', '2025-07-22 14:04:56'),
(232, 110, 'mq135', '{\"co2\": 1374, \"methane\": 72, \"butane\": 192, \"propane\": 189}', '', '2025-07-22 14:04:56'),
(233, 110, 'soilHum', '72.7', '%', '2025-07-22 14:04:56'),
(234, 110, 'ph', '5.77', '', '2025-07-22 14:04:56'),
(235, 110, 'ec', '807.0', 'μS/cm', '2025-07-22 14:04:56'),
(236, 110, 'h2o', '62.0', '%', '2025-07-22 14:04:56'),
(237, 110, 'nafta', '62.0', '%', '2025-07-22 14:04:56'),
(238, 110, 'aceite', '42.0', '%', '2025-07-22 14:04:56'),
(239, 110, 'tempHum', '{\"temperature\": 31.4, \"humidity\": 72.4}', '', '2025-07-22 15:04:55'),
(240, 110, 'temperature', '31.4', '°C', '2025-07-22 15:04:55'),
(241, 110, 'humidity', '72.4', '%', '2025-07-22 15:04:55'),
(242, 110, 'mq135', '{\"co2\": 1970, \"methane\": 0, \"butane\": 172, \"propane\": 112}', '', '2025-07-22 15:04:56'),
(243, 110, 'soilHum', '60.9', '%', '2025-07-22 15:04:56'),
(244, 110, 'ph', '7.2', '', '2025-07-22 15:04:56'),
(245, 110, 'ec', '326.0', 'μS/cm', '2025-07-22 15:04:56'),
(246, 110, 'h2o', '9.0', '%', '2025-07-22 15:04:56'),
(247, 110, 'nafta', '7.0', '%', '2025-07-22 15:04:56'),
(248, 110, 'aceite', '6.0', '%', '2025-07-22 15:04:56'),
(249, 110, 'tempHum', '{\"temperature\": 28.7, \"humidity\": 71.1}', '', '2025-07-22 16:04:55'),
(250, 110, 'temperature', '28.7', '°C', '2025-07-22 16:04:55'),
(251, 110, 'humidity', '71.1', '%', '2025-07-22 16:04:55'),
(252, 110, 'mq135', '{\"co2\": 556, \"methane\": 195, \"butane\": 181, \"propane\": 146}', '', '2025-07-22 16:04:56'),
(253, 110, 'soilHum', '16.6', '%', '2025-07-22 16:04:56'),
(254, 110, 'ph', '5.07', '', '2025-07-22 16:04:56'),
(255, 110, 'ec', '722.0', 'μS/cm', '2025-07-22 16:04:56'),
(256, 110, 'h2o', '90.0', '%', '2025-07-22 16:04:56'),
(257, 110, 'nafta', '65.0', '%', '2025-07-22 16:04:56'),
(258, 110, 'aceite', '13.0', '%', '2025-07-22 16:04:56'),
(259, 110, 'tempHum', '{\"temperature\": 20.2, \"humidity\": 74.4}', '', '2025-07-22 17:04:55'),
(260, 110, 'temperature', '20.2', '°C', '2025-07-22 17:04:55'),
(261, 110, 'humidity', '74.4', '%', '2025-07-22 17:04:55'),
(262, 110, 'mq135', '{\"co2\": 1037, \"methane\": 83, \"butane\": 168, \"propane\": 32}', '', '2025-07-22 17:04:56'),
(263, 110, 'soilHum', '47.8', '%', '2025-07-22 17:04:56'),
(264, 110, 'ph', '6.84', '', '2025-07-22 17:04:56'),
(265, 110, 'ec', '1224.0', 'μS/cm', '2025-07-22 17:04:56'),
(266, 110, 'h2o', '16.0', '%', '2025-07-22 17:04:56'),
(267, 110, 'nafta', '89.0', '%', '2025-07-22 17:04:56'),
(268, 110, 'aceite', '22.0', '%', '2025-07-22 17:04:56'),
(269, 110, 'tempHum', '{\"temperature\": 20.3, \"humidity\": 73.6}', '', '2025-07-22 18:04:55'),
(270, 110, 'temperature', '20.3', '°C', '2025-07-22 18:04:55'),
(271, 110, 'humidity', '73.6', '%', '2025-07-22 18:04:55'),
(272, 110, 'mq135', '{\"co2\": 786, \"methane\": 75, \"butane\": 81, \"propane\": 160}', '', '2025-07-22 18:04:56'),
(273, 110, 'soilHum', '73.4', '%', '2025-07-22 18:04:56'),
(274, 110, 'ph', '5.9', '', '2025-07-22 18:04:56'),
(275, 110, 'ec', '416.0', 'μS/cm', '2025-07-22 18:04:56'),
(276, 110, 'h2o', '40.0', '%', '2025-07-22 18:04:56'),
(277, 110, 'nafta', '13.0', '%', '2025-07-22 18:04:56'),
(278, 110, 'aceite', '72.0', '%', '2025-07-22 18:04:56'),
(279, 110, 'tempHum', '{\"temperature\": 25, \"humidity\": 62.1}', '', '2025-07-22 19:04:55'),
(280, 110, 'temperature', '25.0', '°C', '2025-07-22 19:04:55'),
(281, 110, 'humidity', '62.1', '%', '2025-07-22 19:04:55'),
(282, 110, 'mq135', '{\"co2\": 607, \"methane\": 41, \"butane\": 138, \"propane\": 90}', '', '2025-07-22 19:04:55'),
(283, 110, 'soilHum', '28.1', '%', '2025-07-22 19:04:55'),
(284, 110, 'ph', '8.45', '', '2025-07-22 19:04:55'),
(285, 110, 'ec', '1473.0', 'μS/cm', '2025-07-22 19:04:55'),
(286, 110, 'h2o', '36.0', '%', '2025-07-22 19:04:56'),
(287, 110, 'nafta', '51.0', '%', '2025-07-22 19:04:56'),
(288, 110, 'aceite', '15.0', '%', '2025-07-22 19:04:56'),
(289, 110, 'tempHum', '{\"temperature\": 25.6, \"humidity\": 44.2}', '', '2025-07-22 20:04:55'),
(290, 110, 'temperature', '25.6', '°C', '2025-07-22 20:04:55'),
(291, 110, 'humidity', '44.2', '%', '2025-07-22 20:04:55'),
(292, 110, 'mq135', '{\"co2\": 718, \"methane\": 192, \"butane\": 39, \"propane\": 64}', '', '2025-07-22 20:04:55'),
(293, 110, 'soilHum', '39.4', '%', '2025-07-22 20:04:55'),
(294, 110, 'ph', '8.35', '', '2025-07-22 20:04:55'),
(295, 110, 'ec', '663.0', 'μS/cm', '2025-07-22 20:04:55'),
(296, 110, 'h2o', '71.0', '%', '2025-07-22 20:04:55'),
(297, 110, 'nafta', '87.0', '%', '2025-07-22 20:04:56'),
(298, 110, 'aceite', '1.0', '%', '2025-07-22 20:04:56');

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_dht22_data`
-- (See below for the actual view)
--
CREATE TABLE `v_dht22_data` (
`id` int(11)
,`device_id` int(11)
,`temperature` longtext
,`humidity` longtext
,`timestamp` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_mq135_data`
-- (See below for the actual view)
--
CREATE TABLE `v_mq135_data` (
`id` int(11)
,`device_id` int(11)
,`co2` longtext
,`methane` longtext
,`butane` longtext
,`propane` longtext
,`timestamp` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `v_dht22_data`
--
DROP TABLE IF EXISTS `v_dht22_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_dht22_data`  AS SELECT `sensor_data`.`id` AS `id`, `sensor_data`.`device_id` AS `device_id`, json_unquote(json_extract(`sensor_data`.`value`,'$.temperature')) AS `temperature`, json_unquote(json_extract(`sensor_data`.`value`,'$.humidity')) AS `humidity`, `sensor_data`.`timestamp` AS `timestamp` FROM `sensor_data` WHERE `sensor_data`.`sensor_type` = 'tempHum' ;

-- --------------------------------------------------------

--
-- Structure for view `v_mq135_data`
--
DROP TABLE IF EXISTS `v_mq135_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mq135_data`  AS SELECT `sensor_data`.`id` AS `id`, `sensor_data`.`device_id` AS `device_id`, json_unquote(json_extract(`sensor_data`.`value`,'$.co2')) AS `co2`, json_unquote(json_extract(`sensor_data`.`value`,'$.methane')) AS `methane`, json_unquote(json_extract(`sensor_data`.`value`,'$.butane')) AS `butane`, json_unquote(json_extract(`sensor_data`.`value`,'$.propane')) AS `propane`, `sensor_data`.`timestamp` AS `timestamp` FROM `sensor_data` WHERE `sensor_data`.`sensor_type` = 'mq135' ;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

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
