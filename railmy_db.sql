-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2026 at 10:39 AM
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
-- Database: `railmy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `identity` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`id`, `fullname`, `email`, `phone`, `identity`, `password_hash`, `created_at`) VALUES
(1, 'IMAN NUR IZZAH BINTI MOHD RAFIZAN', 'imanizzah1702@gmail.com', '0195069417', '060217010424', '$2y$10$84K8BeuQbj8XYUpkiaHiHeOGCU/qTfa9VQvn7v5ROaoZB3f5Q80MW', '2026-11-23 08:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `train_name` varchar(100) NOT NULL,
  `train_number` varchar(30) NOT NULL,
  `coach` varchar(20) NOT NULL,
  `seat_number` varchar(20) NOT NULL,
  `seat_class` varchar(50) NOT NULL,
  `seat_status` enum('Available','Occupied','Reserved','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`id`, `train_name`, `train_number`, `coach`, `seat_number`, `seat_class`, `seat_status`, `created_at`) VALUES
(9, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '1A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(10, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '1B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(11, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '1C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(12, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '1D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(13, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '2A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(14, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '2B', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(15, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '2C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(16, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '2D', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(17, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '3A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(18, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '3B', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(19, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '3C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(20, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '3D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(21, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '4A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(22, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '4B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(23, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '4C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(24, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '4D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(25, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '5A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(26, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '5B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(27, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '5C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(28, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '5D', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(29, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '6A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(30, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '6B', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(31, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '6C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(32, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '6D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(33, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '7A', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(34, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '7B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(35, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '7C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(36, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '7D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(37, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '8A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(38, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '8B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(39, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '8C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(40, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '8D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(41, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '9A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(42, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '9B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(43, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '9C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(44, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '9D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(45, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '10A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(46, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '10B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(47, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '10C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(48, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '10D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(49, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '11A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(50, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '11B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(51, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '11C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(52, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '11D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(53, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '12A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(54, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '12B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(55, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '12C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(56, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach C', '12D', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(57, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '1A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(58, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '1B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(59, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '1C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(60, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '1D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(61, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '2A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(62, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '2B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(63, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '2C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(64, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '2D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(65, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '3A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(66, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '3B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(67, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '3C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(68, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '3D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(69, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '4A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(70, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '4B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(71, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '4C', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(72, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '4D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(73, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '5A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(74, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '5B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(75, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '5C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(76, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '5D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(77, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '6A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(78, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '6B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(79, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '6C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(80, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '6D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(81, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '7A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(82, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '7B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(83, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '7C', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(84, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '7D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(85, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '8A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(86, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '8B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(87, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '8C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(88, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '8D', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(89, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '9A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(90, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '9B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(91, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '9C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(92, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '9D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(93, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '10A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(94, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '10B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(95, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '10C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(96, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '10D', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(97, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '11A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(98, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '11B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(99, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '11C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(100, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '11D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(101, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '12A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(102, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '12B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(103, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '12C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(104, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach D', '12D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(105, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '1A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(106, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '1B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(107, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '1C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(108, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '1D', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(109, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '2A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(110, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '2B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(111, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '2C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(112, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '2D', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(113, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '3A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(114, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '3B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(115, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '3C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(116, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '3D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(117, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '4A', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(118, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '4B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(119, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '4C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(120, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '4D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(121, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '5A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(122, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '5B', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(123, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '5C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(124, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '5D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(125, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '6A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(126, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '6B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(127, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '6C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(128, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '6D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(129, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '7A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(130, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '7B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(131, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '7C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(132, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '7D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(133, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '8A', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(134, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '8B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(135, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '8C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(136, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '8D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(137, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '9A', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(138, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '9B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(139, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '9C', 'Gold', 'Unavailable', '2026-11-23 09:09:03'),
(140, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '9D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(141, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '10A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(142, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '10B', 'Gold', 'Reserved', '2026-11-23 09:09:03'),
(143, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '10C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(144, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '10D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(145, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '11A', 'Gold', 'Available', '2026-11-23 09:09:03'),
(146, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '11B', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(147, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '11C', 'Gold', 'Available', '2026-11-23 09:09:03'),
(148, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '11D', 'Gold', 'Available', '2026-11-23 09:09:03'),
(149, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '12A', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(150, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '12B', 'Gold', 'Available', '2026-11-23 09:09:03'),
(151, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '12C', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(152, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach E', '12D', 'Gold', 'Occupied', '2026-11-23 09:09:03'),
(153, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '1A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(154, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '1B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(155, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '1C', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(156, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '1D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(157, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '2A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(158, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '2B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(159, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '2C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(160, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '2D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(161, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '3A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(162, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '3B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(163, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '3C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(164, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '3D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(165, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '4A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(166, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '4B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(167, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '4C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(168, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '4D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(169, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '5A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(170, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '5B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(171, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '5C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(172, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '5D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(173, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '6A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(174, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '6B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(175, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '6C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(176, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '6D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(177, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '7A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(178, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '7B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(179, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '7C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(180, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '7D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(181, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '8A', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(182, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '8B', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(183, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '8C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(184, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '8D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(185, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '9A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(186, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '9B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(187, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '9C', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(188, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '9D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(189, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '10A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(190, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '10B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(191, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '10C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(192, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '10D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(193, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '11A', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(194, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '11B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(195, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '11C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(196, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '11D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(197, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '12A', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(198, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '12B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(199, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '12C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(200, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach F', '12D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(201, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '1A', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(202, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '1B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(203, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '1C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(204, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '1D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(205, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '2A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(206, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '2B', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(207, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '2C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(208, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '2D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(209, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '3A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(210, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '3B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(211, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '3C', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(212, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '3D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(213, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '4A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(214, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '4B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(215, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '4C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(216, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '4D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(217, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '5A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(218, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '5B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(219, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '5C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(220, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '5D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(221, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '6A', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(222, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '6B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(223, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '6C', 'Silver', 'Available', '2026-11-23 09:09:03'),
(224, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '6D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(225, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '7A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(226, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '7B', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(227, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '7C', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(228, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '7D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(229, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '8A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(230, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '8B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(231, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '8C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(232, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '8D', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(233, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '9A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(234, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '9B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(235, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '9C', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(236, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '9D', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(237, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '10A', 'Silver', 'Available', '2026-11-23 09:09:03'),
(238, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '10B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(239, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '10C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(240, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '10D', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(241, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '11A', 'Silver', 'Reserved', '2026-11-23 09:09:03'),
(242, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '11B', 'Silver', 'Available', '2026-11-23 09:09:03'),
(243, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '11C', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(244, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '11D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(245, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '12A', 'Silver', 'Unavailable', '2026-11-23 09:09:03'),
(246, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '12B', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(247, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '12C', 'Silver', 'Occupied', '2026-11-23 09:09:03'),
(248, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach G', '12D', 'Silver', 'Available', '2026-11-23 09:09:03'),
(249, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '1A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(250, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '1B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(251, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '1C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(252, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '1D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(253, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '2A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(254, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '2B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(255, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '2C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(256, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '2D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(257, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '3A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(258, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '3B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(259, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '3C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(260, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '3D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(261, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '4A', 'Silver', 'Reserved', '2026-11-23 09:24:37'),
(262, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '4B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(263, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '4C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(264, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '4D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(265, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '5A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(266, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '5B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(267, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '5C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(268, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '5D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(269, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '6A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(270, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '6B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(271, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '6C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(272, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '6D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(273, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '7A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(274, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '7B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(275, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '7C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(276, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '7D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(277, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '8A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(278, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '8B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(279, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '8C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(280, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '8D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(281, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '9A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(282, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '9B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(283, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '9C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(284, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '9D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(285, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '10A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(286, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '10B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(287, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '10C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(288, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '10D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(289, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '11A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(290, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '11B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(291, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '11C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(292, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '11D', 'Silver', 'Available', '2026-11-23 09:24:37'),
(293, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '12A', 'Silver', 'Available', '2026-11-23 09:24:37'),
(294, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '12B', 'Silver', 'Available', '2026-11-23 09:24:37'),
(295, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '12C', 'Silver', 'Available', '2026-11-23 09:24:37'),
(296, 'ETS 23 - KL Sentral to Butterworth', 'ETS23', 'Coach I', '12D', 'Silver', 'Available', '2026-11-23 09:24:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `identity` (`identity`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_train_seat` (`train_number`,`coach`,`seat_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=297;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
