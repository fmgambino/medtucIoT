-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 24, 2025 at 08:54 PM
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
('Fernando', 'Gambino', 'Argentina', 'Tucumán', 'San Miguel de tucumán', 'fernando.m.gambino@gmail.com', 1, '', '$2y$10$snSkc4Bqhn5u/r1jLO9IWuIh.r.ZKqFIC9R7lxwwNXKuAsClJ4vFm', 'Prospecto', 'admin', 'assets/files/profile_1_1753388010.png', '2025-07-19 22:30:31'),
('Don', 'Pepito', 'Argentina', 'Tucumán', 'San Miguel de Tucumán', 'donpepito@gmail.com', 3, 'donpepito@gmail.com', '$2y$10$RR92.91K0Z9XAF/PUlJZuOJGA8ZVEXTdTrDZh40TM5wY4wB52DYwO', 'Prospecto', 'admin', 'assets/files/profile_3_1753279812.png', '2025-07-23 14:08:21');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
