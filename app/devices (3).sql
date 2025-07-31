-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 31, 2025 at 08:57 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u197809344_medtuciot`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(50) NOT NULL,
  `place_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icono` varchar(20) DEFAULT '? Genérico',
  `ubicacion` varchar(100) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `mapa` text DEFAULT NULL,
  `esp32_id` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `mac` varchar(30) DEFAULT NULL COMMENT 'MAC address del dispositivo',
  `wifi` varchar(50) DEFAULT NULL COMMENT 'SSID de la red WiFi',
  `ip` varchar(45) DEFAULT NULL COMMENT 'Dirección IP actual',
  `rssi` int(11) DEFAULT NULL COMMENT 'Señal WiFi en dBm',
  `mqtt_status` enum('Online','Offline') DEFAULT 'Offline' COMMENT 'Estado de conexión MQTT',
  `cpu_temp` decimal(5,2) DEFAULT NULL COMMENT 'Temperatura del micro en °C',
  `uptime` varchar(50) DEFAULT NULL COMMENT 'Tiempo desde último reinicio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `serial_number`, `place_id`, `name`, `icono`, `ubicacion`, `domicilio`, `mapa`, `esp32_id`, `user_id`, `created_at`, `mac`, `wifi`, `ip`, `rssi`, `mqtt_status`, `cpu_temp`, `uptime`) VALUES
(110, 'EG331256', 1, 'ESP-Casa-1', '🔧 Genérico', 'Sala Servidores', 'Sarmiento 850, San Miguel de Tucumán, Tucumán, Argantina', 'https://www.google.com/maps?q=Sarmiento+850,+Tucuman&output=embed', 'ESPA7B0', 1, '2025-07-28 02:30:23', 'C8:F0:9E:12:34:56', 'MedTuCloT_WiFi', '192.168.0.110', -47, 'Online', 48.70, '01:12:05'),
(111, 'EG123456', 1, 'ESP-Casa-1', '🏠 Casa', 'Sala de estar', 'Asunción 343, San Miguel de tucuman, argentina, Argentina', 'https://www.google.com/maps?q=Asuncion+343,+Tucuman&output=embed', 'ESP12345', 1, '2025-07-28 02:30:23', 'C8:F0:9E:12:34:57', 'MedTuCloT_WiFi', '192.168.0.111', -51, 'Online', 49.50, '00:05:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `place_id` (`place_id`),
  ADD KEY `fk_devices_users` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_devices_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
