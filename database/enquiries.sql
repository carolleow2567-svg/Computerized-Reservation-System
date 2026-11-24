-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 03:18 AM
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
-- Database: `computerized_reservation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

USE computerized_reservation_system;

CREATE TABLE `enquiries` (
  `enquiry_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','in_progress','resolved') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`enquiry_id`, `user_id`, `fullname`, `email`, `subject`, `message`, `status`, `submitted_at`) VALUES
(1, 1, 'NOR LAILATUL DANIA BINTI AMROZI', 'amrozidania@gmail.com', 'Cancellation', 'I would like to cancel my trip this weekend, but I already made full payment', 'pending', '2026-11-24 01:59:10'),
(2, 1, 'NOR LAILATUL DANIA BINTI AMROZI', 'amrozidania@gmail.com', 'Cancellation', 'I would like to cancel my trip this weekend, but I already made full payment', 'pending', '2026-11-24 02:06:40'),
(3, 1, 'NOR LAILATUL DANIA BINTI AMROZI', 'amrozidania@gmail.com', 'Cancellation', 'I would like to cancel my trip this weekend, but I already made full payment', 'pending', '2026-11-24 02:06:57'),
(4, 1, 'NOR LAILATUL DANIA BINTI AMROZI', 'amrozidania@gmail.com', 'Cancellation', 'I would like to cancel my trip this weekend, but I already made full payment', 'pending', '2026-11-24 02:07:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`enquiry_id`),
  ADD KEY `fk_enquiries_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `enquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `fk_enquiries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
