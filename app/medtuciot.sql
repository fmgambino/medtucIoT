-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 09:58 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensors`
--

INSERT INTO `sensors` (`id`, `device_id`, `name`, `port`, `variable`, `icon`, `created_at`) VALUES
(57, 110, 'Temperatura', 'GPIO34', 'temp', '🌡️', '2025-07-21 07:01:17'),
(58, 110, 'Humedad', 'GPIO34', 'hum', '💧', '2025-07-21 07:01:17'),
(59, 110, 'CO₂', 'GPIO35', 'co2', '🏭', '2025-07-21 07:01:17'),
(60, 110, 'Metano', 'GPIO35', 'methane', '🔥', '2025-07-21 07:01:17'),
(61, 110, 'Butano', 'GPIO35', 'butane', '🔥', '2025-07-21 07:01:17'),
(62, 110, 'Propano', 'GPIO35', 'propane', '🔥', '2025-07-21 07:01:17'),
(63, 110, 'Hum Suelo', 'GPIO32', 'soilHum', '🌱', '2025-07-21 07:01:17'),
(64, 110, 'pH', 'GPIO33', 'ph', '🧪', '2025-07-21 07:01:17'),
(65, 110, 'EC', 'GPIO36', 'ec', '⚡', '2025-07-21 07:01:17'),
(66, 110, 'Nivel H₂O', 'GPIO39', 'h2o', '💧', '2025-07-21 07:01:17'),
(67, 110, 'Nafta', 'GPIO25', 'nafta', '⛽', '2025-07-21 07:01:17'),
(68, 110, 'Aceite', 'GPIO26', 'aceite', '🛢️', '2025-07-21 07:01:17'),
(71, 110, 'LDR', 'GPIO13', 'sldr', '💡', '2025-07-21 07:15:00');

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
(1, 110, 'temp', 24.7, '°C', '2025-07-21 07:54:55'),
(2, 110, 'hum', 27.8, '%', '2025-07-21 07:54:55'),
(3, 110, 'co2', 1760, 'ppm', '2025-07-21 07:54:55'),
(4, 110, 'methane', 151, 'ppm', '2025-07-21 07:54:55'),
(5, 110, 'butane', 156, 'ppm', '2025-07-21 07:54:55'),
(6, 110, 'propane', 124, 'ppm', '2025-07-21 07:54:55'),
(7, 110, 'soilHum', 65.4, '%', '2025-07-21 07:54:55'),
(8, 110, 'ph', 4.53, '', '2025-07-21 07:54:55'),
(9, 110, 'ec', 1782, 'μS/cm', '2025-07-21 07:54:55'),
(10, 110, 'h2o', 34, '%', '2025-07-21 07:54:55'),
(11, 110, 'nafta', 65, '%', '2025-07-21 07:54:55'),
(12, 110, 'aceite', 31, '%', '2025-07-21 07:54:55'),
(13, 110, 'temp', 33.5, '°C', '2025-07-21 07:55:05'),
(14, 110, 'hum', 16.6, '%', '2025-07-21 07:55:05'),
(15, 110, 'co2', 1683, 'ppm', '2025-07-21 07:55:05'),
(16, 110, 'methane', 194, 'ppm', '2025-07-21 07:55:05'),
(17, 110, 'butane', 179, 'ppm', '2025-07-21 07:55:05'),
(18, 110, 'propane', 160, 'ppm', '2025-07-21 07:55:05'),
(19, 110, 'soilHum', 89.8, '%', '2025-07-21 07:55:05'),
(20, 110, 'ph', 4.15, '', '2025-07-21 07:55:05'),
(21, 110, 'ec', 600, 'μS/cm', '2025-07-21 07:55:05'),
(22, 110, 'h2o', 47, '%', '2025-07-21 07:55:05'),
(23, 110, 'nafta', 30, '%', '2025-07-21 07:55:05'),
(24, 110, 'aceite', 70, '%', '2025-07-21 07:55:05'),
(25, 110, 'temp', 33.3, '°C', '2025-07-21 07:55:15'),
(26, 110, 'hum', 31.5, '%', '2025-07-21 07:55:15'),
(27, 110, 'co2', 1915, 'ppm', '2025-07-21 07:55:15'),
(28, 110, 'methane', 49, 'ppm', '2025-07-21 07:55:15'),
(29, 110, 'butane', 104, 'ppm', '2025-07-21 07:55:15'),
(30, 110, 'propane', 3, 'ppm', '2025-07-21 07:55:15'),
(31, 110, 'soilHum', 31.9, '%', '2025-07-21 07:55:15'),
(32, 110, 'ph', 4.33, '', '2025-07-21 07:55:15'),
(33, 110, 'ec', 254, 'μS/cm', '2025-07-21 07:55:15'),
(34, 110, 'h2o', 25, '%', '2025-07-21 07:55:15'),
(35, 110, 'nafta', 40, '%', '2025-07-21 07:55:15'),
(36, 110, 'aceite', 28, '%', '2025-07-21 07:55:15'),
(37, 110, 'temp', 26.2, '°C', '2025-07-21 07:55:25'),
(38, 110, 'hum', 33.4, '%', '2025-07-21 07:55:25'),
(39, 110, 'co2', 1414, 'ppm', '2025-07-21 07:55:25'),
(40, 110, 'methane', 51, 'ppm', '2025-07-21 07:55:25'),
(41, 110, 'butane', 190, 'ppm', '2025-07-21 07:55:25'),
(42, 110, 'propane', 68, 'ppm', '2025-07-21 07:55:25'),
(43, 110, 'soilHum', 81.8, '%', '2025-07-21 07:55:25'),
(44, 110, 'ph', 6.83, '', '2025-07-21 07:55:25'),
(45, 110, 'ec', 1265, 'μS/cm', '2025-07-21 07:55:25'),
(46, 110, 'h2o', 23, '%', '2025-07-21 07:55:25'),
(47, 110, 'nafta', 69, '%', '2025-07-21 07:55:25'),
(48, 110, 'aceite', 93, '%', '2025-07-21 07:55:25'),
(49, 110, 'temp', 21.1, '°C', '2025-07-21 07:55:35'),
(50, 110, 'hum', 30.5, '%', '2025-07-21 07:55:35'),
(51, 110, 'co2', 1066, 'ppm', '2025-07-21 07:55:35'),
(52, 110, 'methane', 171, 'ppm', '2025-07-21 07:55:35'),
(53, 110, 'butane', 110, 'ppm', '2025-07-21 07:55:35'),
(54, 110, 'propane', 48, 'ppm', '2025-07-21 07:55:35'),
(55, 110, 'soilHum', 49.3, '%', '2025-07-21 07:55:35'),
(56, 110, 'ph', 6.41, '', '2025-07-21 07:55:35'),
(57, 110, 'ec', 1167, 'μS/cm', '2025-07-21 07:55:35'),
(58, 110, 'h2o', 35, '%', '2025-07-21 07:55:35'),
(59, 110, 'nafta', 67, '%', '2025-07-21 07:55:35'),
(60, 110, 'aceite', 47, '%', '2025-07-21 07:55:35'),
(61, 110, 'temp', 20.4, '°C', '2025-07-21 07:55:45'),
(62, 110, 'hum', 30.8, '%', '2025-07-21 07:55:45'),
(63, 110, 'co2', 1051, 'ppm', '2025-07-21 07:55:45'),
(64, 110, 'methane', 199, 'ppm', '2025-07-21 07:55:45'),
(65, 110, 'butane', 20, 'ppm', '2025-07-21 07:55:45'),
(66, 110, 'propane', 173, 'ppm', '2025-07-21 07:55:45'),
(67, 110, 'soilHum', 66.5, '%', '2025-07-21 07:55:45'),
(68, 110, 'ph', 4.78, '', '2025-07-21 07:55:45'),
(69, 110, 'ec', 1563, 'μS/cm', '2025-07-21 07:55:45'),
(70, 110, 'h2o', 53, '%', '2025-07-21 07:55:45'),
(71, 110, 'nafta', 97, '%', '2025-07-21 07:55:45'),
(72, 110, 'aceite', 96, '%', '2025-07-21 07:55:45'),
(73, 110, 'temp', 23.2, '°C', '2025-07-21 07:55:55'),
(74, 110, 'hum', 31.2, '%', '2025-07-21 07:55:55'),
(75, 110, 'co2', 1379, 'ppm', '2025-07-21 07:55:55'),
(76, 110, 'methane', 128, 'ppm', '2025-07-21 07:55:55'),
(77, 110, 'butane', 5, 'ppm', '2025-07-21 07:55:55'),
(78, 110, 'propane', 161, 'ppm', '2025-07-21 07:55:55'),
(79, 110, 'soilHum', 32.3, '%', '2025-07-21 07:55:55'),
(80, 110, 'ph', 5.97, '', '2025-07-21 07:55:55'),
(81, 110, 'ec', 1641, 'μS/cm', '2025-07-21 07:55:55'),
(82, 110, 'h2o', 75, '%', '2025-07-21 07:55:55'),
(83, 110, 'nafta', 31, '%', '2025-07-21 07:55:55'),
(84, 110, 'aceite', 4, '%', '2025-07-21 07:55:55'),
(85, 110, 'temp', 21, '°C', '2025-07-21 07:56:05'),
(86, 110, 'hum', 24, '%', '2025-07-21 07:56:05'),
(87, 110, 'co2', 1586, 'ppm', '2025-07-21 07:56:05'),
(88, 110, 'methane', 114, 'ppm', '2025-07-21 07:56:05'),
(89, 110, 'butane', 20, 'ppm', '2025-07-21 07:56:05'),
(90, 110, 'propane', 29, 'ppm', '2025-07-21 07:56:05'),
(91, 110, 'soilHum', 53.4, '%', '2025-07-21 07:56:05'),
(92, 110, 'ph', 5.63, '', '2025-07-21 07:56:05'),
(93, 110, 'ec', 1411, 'μS/cm', '2025-07-21 07:56:05'),
(94, 110, 'h2o', 75, '%', '2025-07-21 07:56:05'),
(95, 110, 'nafta', 23, '%', '2025-07-21 07:56:05'),
(96, 110, 'aceite', 87, '%', '2025-07-21 07:56:05'),
(97, 110, 'temp', 32.3, '°C', '2025-07-21 07:56:15'),
(98, 110, 'hum', 28.8, '%', '2025-07-21 07:56:15'),
(99, 110, 'co2', 854, 'ppm', '2025-07-21 07:56:15'),
(100, 110, 'methane', 152, 'ppm', '2025-07-21 07:56:15'),
(101, 110, 'butane', 161, 'ppm', '2025-07-21 07:56:15'),
(102, 110, 'propane', 82, 'ppm', '2025-07-21 07:56:15'),
(103, 110, 'soilHum', 70.6, '%', '2025-07-21 07:56:15'),
(104, 110, 'ph', 8.29, '', '2025-07-21 07:56:15'),
(105, 110, 'ec', 1514, 'μS/cm', '2025-07-21 07:56:15'),
(106, 110, 'h2o', 63, '%', '2025-07-21 07:56:15'),
(107, 110, 'nafta', 8, '%', '2025-07-21 07:56:15'),
(108, 110, 'aceite', 34, '%', '2025-07-21 07:56:15'),
(109, 110, 'temp', 27.5, '°C', '2025-07-21 07:56:25'),
(110, 110, 'hum', 22.4, '%', '2025-07-21 07:56:25'),
(111, 110, 'co2', 1869, 'ppm', '2025-07-21 07:56:25'),
(112, 110, 'methane', 110, 'ppm', '2025-07-21 07:56:25'),
(113, 110, 'butane', 81, 'ppm', '2025-07-21 07:56:25'),
(114, 110, 'propane', 102, 'ppm', '2025-07-21 07:56:25'),
(115, 110, 'soilHum', 56.6, '%', '2025-07-21 07:56:25'),
(116, 110, 'ph', 5.55, '', '2025-07-21 07:56:25'),
(117, 110, 'ec', 1235, 'μS/cm', '2025-07-21 07:56:25'),
(118, 110, 'h2o', 25, '%', '2025-07-21 07:56:25'),
(119, 110, 'nafta', 77, '%', '2025-07-21 07:56:25'),
(120, 110, 'aceite', 80, '%', '2025-07-21 07:56:25'),
(121, 110, 'temp', 19.3, '°C', '2025-07-21 07:56:35'),
(122, 110, 'hum', 25.9, '%', '2025-07-21 07:56:35'),
(123, 110, 'co2', 952, 'ppm', '2025-07-21 07:56:35'),
(124, 110, 'methane', 186, 'ppm', '2025-07-21 07:56:35'),
(125, 110, 'butane', 38, 'ppm', '2025-07-21 07:56:35'),
(126, 110, 'propane', 41, 'ppm', '2025-07-21 07:56:35'),
(127, 110, 'soilHum', 75.8, '%', '2025-07-21 07:56:35'),
(128, 110, 'ph', 7.93, '', '2025-07-21 07:56:35'),
(129, 110, 'ec', 1141, 'μS/cm', '2025-07-21 07:56:35'),
(130, 110, 'h2o', 25, '%', '2025-07-21 07:56:35'),
(131, 110, 'nafta', 75, '%', '2025-07-21 07:56:35'),
(132, 110, 'aceite', 11, '%', '2025-07-21 07:56:35'),
(133, 110, 'temp', 18, '°C', '2025-07-21 07:56:45'),
(134, 110, 'hum', 27.7, '%', '2025-07-21 07:56:45'),
(135, 110, 'co2', 1020, 'ppm', '2025-07-21 07:56:45'),
(136, 110, 'methane', 42, 'ppm', '2025-07-21 07:56:45'),
(137, 110, 'butane', 78, 'ppm', '2025-07-21 07:56:45'),
(138, 110, 'propane', 71, 'ppm', '2025-07-21 07:56:45'),
(139, 110, 'soilHum', 52.7, '%', '2025-07-21 07:56:45'),
(140, 110, 'ph', 5.26, '', '2025-07-21 07:56:45'),
(141, 110, 'ec', 297, 'μS/cm', '2025-07-21 07:56:45'),
(142, 110, 'h2o', 87, '%', '2025-07-21 07:56:45'),
(143, 110, 'nafta', 23, '%', '2025-07-21 07:56:45'),
(144, 110, 'aceite', 93, '%', '2025-07-21 07:56:45'),
(145, 110, 'temp', 16.8, '°C', '2025-07-21 07:56:55'),
(146, 110, 'hum', 15.6, '%', '2025-07-21 07:56:55'),
(147, 110, 'co2', 1557, 'ppm', '2025-07-21 07:56:55'),
(148, 110, 'methane', 74, 'ppm', '2025-07-21 07:56:55'),
(149, 110, 'butane', 130, 'ppm', '2025-07-21 07:56:55'),
(150, 110, 'propane', 199, 'ppm', '2025-07-21 07:56:55'),
(151, 110, 'soilHum', 57.2, '%', '2025-07-21 07:56:55'),
(152, 110, 'ph', 6.24, '', '2025-07-21 07:56:55'),
(153, 110, 'ec', 1479, 'μS/cm', '2025-07-21 07:56:55'),
(154, 110, 'h2o', 82, '%', '2025-07-21 07:56:55'),
(155, 110, 'nafta', 6, '%', '2025-07-21 07:56:55'),
(156, 110, 'aceite', 33, '%', '2025-07-21 07:56:55'),
(157, 110, 'temp', 20.2, '°C', '2025-07-21 07:57:05'),
(158, 110, 'hum', 31.1, '%', '2025-07-21 07:57:05'),
(159, 110, 'co2', 564, 'ppm', '2025-07-21 07:57:05'),
(160, 110, 'methane', 129, 'ppm', '2025-07-21 07:57:05'),
(161, 110, 'butane', 15, 'ppm', '2025-07-21 07:57:05'),
(162, 110, 'propane', 134, 'ppm', '2025-07-21 07:57:05'),
(163, 110, 'soilHum', 51.1, '%', '2025-07-21 07:57:05'),
(164, 110, 'ph', 6.25, '', '2025-07-21 07:57:05'),
(165, 110, 'ec', 1067, 'μS/cm', '2025-07-21 07:57:05'),
(166, 110, 'h2o', 21, '%', '2025-07-21 07:57:05'),
(167, 110, 'nafta', 78, '%', '2025-07-21 07:57:05'),
(168, 110, 'aceite', 27, '%', '2025-07-21 07:57:05'),
(169, 110, 'temp', 28.9, '°C', '2025-07-21 07:57:15'),
(170, 110, 'hum', 24.8, '%', '2025-07-21 07:57:15'),
(171, 110, 'co2', 1767, 'ppm', '2025-07-21 07:57:15'),
(172, 110, 'methane', 36, 'ppm', '2025-07-21 07:57:15'),
(173, 110, 'butane', 128, 'ppm', '2025-07-21 07:57:15'),
(174, 110, 'propane', 86, 'ppm', '2025-07-21 07:57:15'),
(175, 110, 'soilHum', 24.9, '%', '2025-07-21 07:57:15'),
(176, 110, 'ph', 8.8, '', '2025-07-21 07:57:15'),
(177, 110, 'ec', 637, 'μS/cm', '2025-07-21 07:57:15'),
(178, 110, 'h2o', 20, '%', '2025-07-21 07:57:15'),
(179, 110, 'nafta', 41, '%', '2025-07-21 07:57:15'),
(180, 110, 'aceite', 59, '%', '2025-07-21 07:57:15'),
(181, 110, 'temp', 26, '°C', '2025-07-21 07:57:25'),
(182, 110, 'hum', 23.8, '%', '2025-07-21 07:57:25'),
(183, 110, 'co2', 1350, 'ppm', '2025-07-21 07:57:25'),
(184, 110, 'methane', 164, 'ppm', '2025-07-21 07:57:25'),
(185, 110, 'butane', 59, 'ppm', '2025-07-21 07:57:25'),
(186, 110, 'propane', 112, 'ppm', '2025-07-21 07:57:25'),
(187, 110, 'soilHum', 49.7, '%', '2025-07-21 07:57:25'),
(188, 110, 'ph', 4.45, '', '2025-07-21 07:57:25'),
(189, 110, 'ec', 1720, 'μS/cm', '2025-07-21 07:57:25'),
(190, 110, 'h2o', 74, '%', '2025-07-21 07:57:25'),
(191, 110, 'nafta', 4, '%', '2025-07-21 07:57:25'),
(192, 110, 'aceite', 20, '%', '2025-07-21 07:57:25'),
(193, 110, 'temp', 22.4, '°C', '2025-07-21 07:57:35'),
(194, 110, 'hum', 27.8, '%', '2025-07-21 07:57:35'),
(195, 110, 'co2', 526, 'ppm', '2025-07-21 07:57:35'),
(196, 110, 'methane', 109, 'ppm', '2025-07-21 07:57:35'),
(197, 110, 'butane', 77, 'ppm', '2025-07-21 07:57:35'),
(198, 110, 'propane', 152, 'ppm', '2025-07-21 07:57:35'),
(199, 110, 'soilHum', 42.9, '%', '2025-07-21 07:57:35'),
(200, 110, 'ph', 4.74, '', '2025-07-21 07:57:35'),
(201, 110, 'ec', 1564, 'μS/cm', '2025-07-21 07:57:35'),
(202, 110, 'h2o', 19, '%', '2025-07-21 07:57:35'),
(203, 110, 'nafta', 49, '%', '2025-07-21 07:57:35'),
(204, 110, 'aceite', 73, '%', '2025-07-21 07:57:35'),
(205, 110, 'temp', 34.4, '°C', '2025-07-21 07:57:45'),
(206, 110, 'hum', 27.8, '%', '2025-07-21 07:57:45'),
(207, 110, 'co2', 648, 'ppm', '2025-07-21 07:57:45'),
(208, 110, 'methane', 129, 'ppm', '2025-07-21 07:57:45'),
(209, 110, 'butane', 172, 'ppm', '2025-07-21 07:57:45'),
(210, 110, 'propane', 158, 'ppm', '2025-07-21 07:57:45'),
(211, 110, 'soilHum', 27.8, '%', '2025-07-21 07:57:45'),
(212, 110, 'ph', 6.98, '', '2025-07-21 07:57:45'),
(213, 110, 'ec', 835, 'μS/cm', '2025-07-21 07:57:45'),
(214, 110, 'h2o', 88, '%', '2025-07-21 07:57:45'),
(215, 110, 'nafta', 28, '%', '2025-07-21 07:57:45'),
(216, 110, 'aceite', 27, '%', '2025-07-21 07:57:45'),
(217, 110, 'temp', 18.4, '°C', '2025-07-21 07:57:55'),
(218, 110, 'hum', 15.2, '%', '2025-07-21 07:57:55'),
(219, 110, 'co2', 1476, 'ppm', '2025-07-21 07:57:55'),
(220, 110, 'methane', 56, 'ppm', '2025-07-21 07:57:55'),
(221, 110, 'butane', 102, 'ppm', '2025-07-21 07:57:55'),
(222, 110, 'propane', 137, 'ppm', '2025-07-21 07:57:55'),
(223, 110, 'soilHum', 10.2, '%', '2025-07-21 07:57:55'),
(224, 110, 'ph', 4.12, '', '2025-07-21 07:57:55'),
(225, 110, 'ec', 497, 'μS/cm', '2025-07-21 07:57:55'),
(226, 110, 'h2o', 31, '%', '2025-07-21 07:57:55'),
(227, 110, 'nafta', 24, '%', '2025-07-21 07:57:55'),
(228, 110, 'aceite', 24, '%', '2025-07-21 07:57:55'),
(229, 110, 'temp', 18, '°C', '2025-07-21 07:58:05'),
(230, 110, 'hum', 19.2, '%', '2025-07-21 07:58:05'),
(231, 110, 'co2', 354, 'ppm', '2025-07-21 07:58:05'),
(232, 110, 'methane', 85, 'ppm', '2025-07-21 07:58:05'),
(233, 110, 'butane', 114, 'ppm', '2025-07-21 07:58:05'),
(234, 110, 'propane', 14, 'ppm', '2025-07-21 07:58:05'),
(235, 110, 'soilHum', 80.3, '%', '2025-07-21 07:58:05'),
(236, 110, 'ph', 5.08, '', '2025-07-21 07:58:05'),
(237, 110, 'ec', 589, 'μS/cm', '2025-07-21 07:58:05'),
(238, 110, 'h2o', 8, '%', '2025-07-21 07:58:05'),
(239, 110, 'nafta', 75, '%', '2025-07-21 07:58:05'),
(240, 110, 'aceite', 71, '%', '2025-07-21 07:58:05'),
(241, 110, 'temp', 27.5, '°C', '2025-07-21 07:58:15'),
(242, 110, 'hum', 20.5, '%', '2025-07-21 07:58:15'),
(243, 110, 'co2', 781, 'ppm', '2025-07-21 07:58:15'),
(244, 110, 'methane', 77, 'ppm', '2025-07-21 07:58:15'),
(245, 110, 'butane', 98, 'ppm', '2025-07-21 07:58:15'),
(246, 110, 'propane', 149, 'ppm', '2025-07-21 07:58:15'),
(247, 110, 'soilHum', 67.6, '%', '2025-07-21 07:58:15'),
(248, 110, 'ph', 8.33, '', '2025-07-21 07:58:15'),
(249, 110, 'ec', 903, 'μS/cm', '2025-07-21 07:58:15'),
(250, 110, 'h2o', 21, '%', '2025-07-21 07:58:15'),
(251, 110, 'nafta', 32, '%', '2025-07-21 07:58:15'),
(252, 110, 'aceite', 98, '%', '2025-07-21 07:58:15'),
(253, 110, 'temp', 28.1, '°C', '2025-07-21 07:58:25'),
(254, 110, 'hum', 22.7, '%', '2025-07-21 07:58:25'),
(255, 110, 'co2', 408, 'ppm', '2025-07-21 07:58:25'),
(256, 110, 'methane', 195, 'ppm', '2025-07-21 07:58:25'),
(257, 110, 'butane', 120, 'ppm', '2025-07-21 07:58:25'),
(258, 110, 'propane', 109, 'ppm', '2025-07-21 07:58:25'),
(259, 110, 'soilHum', 12.9, '%', '2025-07-21 07:58:25'),
(260, 110, 'ph', 5.2, '', '2025-07-21 07:58:25'),
(261, 110, 'ec', 768, 'μS/cm', '2025-07-21 07:58:25'),
(262, 110, 'h2o', 79, '%', '2025-07-21 07:58:25'),
(263, 110, 'nafta', 97, '%', '2025-07-21 07:58:25'),
(264, 110, 'aceite', 0, '%', '2025-07-21 07:58:25'),
(265, 110, 'temp', 15.1, '°C', '2025-07-21 07:58:35'),
(266, 110, 'hum', 29, '%', '2025-07-21 07:58:35'),
(267, 110, 'co2', 558, 'ppm', '2025-07-21 07:58:35'),
(268, 110, 'methane', 105, 'ppm', '2025-07-21 07:58:35'),
(269, 110, 'butane', 165, 'ppm', '2025-07-21 07:58:35'),
(270, 110, 'propane', 71, 'ppm', '2025-07-21 07:58:35'),
(271, 110, 'soilHum', 72.4, '%', '2025-07-21 07:58:35'),
(272, 110, 'ph', 6.53, '', '2025-07-21 07:58:35'),
(273, 110, 'ec', 311, 'μS/cm', '2025-07-21 07:58:35'),
(274, 110, 'h2o', 63, '%', '2025-07-21 07:58:35'),
(275, 110, 'nafta', 21, '%', '2025-07-21 07:58:35'),
(276, 110, 'aceite', 37, '%', '2025-07-21 07:58:35'),
(277, 110, 'temp', 17.9, '°C', '2025-07-21 07:58:45'),
(278, 110, 'hum', 29.8, '%', '2025-07-21 07:58:45'),
(279, 110, 'co2', 1501, 'ppm', '2025-07-21 07:58:45'),
(280, 110, 'methane', 146, 'ppm', '2025-07-21 07:58:45'),
(281, 110, 'butane', 109, 'ppm', '2025-07-21 07:58:45'),
(282, 110, 'propane', 25, 'ppm', '2025-07-21 07:58:45'),
(283, 110, 'soilHum', 49, '%', '2025-07-21 07:58:45'),
(284, 110, 'ph', 7.97, '', '2025-07-21 07:58:45'),
(285, 110, 'ec', 1475, 'μS/cm', '2025-07-21 07:58:45'),
(286, 110, 'h2o', 24, '%', '2025-07-21 07:58:45'),
(287, 110, 'nafta', 99, '%', '2025-07-21 07:58:45'),
(288, 110, 'aceite', 75, '%', '2025-07-21 07:58:45');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

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
