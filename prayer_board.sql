-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2025 at 03:30 PM
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
-- Database: `prayer_board`
--

-- --------------------------------------------------------

--
-- Table structure for table `envelopes`
--

CREATE TABLE `envelopes` (
  `id` int(11) NOT NULL,
  `board_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `is_answered_envelope` tinyint(1) DEFAULT 0,
  `order_index` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `envelopes`
--

INSERT INTO `envelopes` (`id`, `board_id`, `name`, `color`, `is_answered_envelope`, `order_index`) VALUES
(1, 1, 'family edited', '#9e78a1', 0, 0),
(2, 1, 'Career', '#8bfdae', 0, 1),
(5, 2, 'third envelope', '#491d1d', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `prayer_boards`
--

CREATE TABLE `prayer_boards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `background_color` varchar(7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_boards`
--

INSERT INTO `prayer_boards` (`id`, `user_id`, `name`, `description`, `background_color`, `created_at`) VALUES
(1, 1, '2025', '2025 board', '#854bdd', '2025-01-09 08:43:12'),
(2, 1, '2026', 'my board for 2026', '#81a8c1', '2025-01-13 11:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_points`
--

CREATE TABLE `prayer_points` (
  `id` int(11) NOT NULL,
  `envelope_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `answered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prayer_points`
--

INSERT INTO `prayer_points` (`id`, `envelope_id`, `content`, `created_at`, `answered_at`) VALUES
(1, 1, 'Stability', '2025-01-13 09:24:07', NULL),
(2, 1, 'Prosperity', '2025-01-13 09:24:40', NULL),
(3, 1, 'Stability edited', '2025-01-13 09:59:38', NULL),
(6, 2, 'for career', '2025-01-13 11:06:51', NULL),
(7, 5, 'gifts', '2025-01-13 12:52:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'Gabriel', 'gabrielkadiwa@gmail.com', '$2y$10$5.X5UtWIsXYr4ABpfsbA3eTRC28u9wlnz.HWrWAsH5/CQ6MPt5mDq', '2025-01-09 08:42:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `envelopes`
--
ALTER TABLE `envelopes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `board_id` (`board_id`);

--
-- Indexes for table `prayer_boards`
--
ALTER TABLE `prayer_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prayer_points`
--
ALTER TABLE `prayer_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `envelope_id` (`envelope_id`);

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
-- AUTO_INCREMENT for table `envelopes`
--
ALTER TABLE `envelopes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prayer_boards`
--
ALTER TABLE `prayer_boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prayer_points`
--
ALTER TABLE `prayer_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `envelopes`
--
ALTER TABLE `envelopes`
  ADD CONSTRAINT `envelopes_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `prayer_boards` (`id`);

--
-- Constraints for table `prayer_boards`
--
ALTER TABLE `prayer_boards`
  ADD CONSTRAINT `prayer_boards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prayer_points`
--
ALTER TABLE `prayer_points`
  ADD CONSTRAINT `prayer_points_ibfk_1` FOREIGN KEY (`envelope_id`) REFERENCES `envelopes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
