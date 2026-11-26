SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `computerized_reservation_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `computerized_reservation_system`;


-- ------------------------------
-- Table: users
-- ------------------------------
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('passenger','admin') NOT NULL DEFAULT 'passenger',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: stations
-- ------------------------------
CREATE TABLE `stations` (
  `station_id` int(11) NOT NULL,
  `station_code` varchar(20) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: classes
-- ------------------------------
CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `class_label` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: trains
-- ------------------------------
CREATE TABLE `trains` (
  `train_id` int(11) NOT NULL,
  `train_number` varchar(20) NOT NULL,
  `train_name` varchar(100) NOT NULL,
  `train_type` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `capacity` int(11) NOT NULL DEFAULT 300,
  `operating_days` varchar(100) NOT NULL DEFAULT 'Daily'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: train_classes
-- ------------------------------
CREATE TABLE `train_classes` (
  `train_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: schedules
-- ------------------------------
CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `train_id` int(11) NOT NULL,
  `origin_station_id` int(11) NOT NULL,
  `destination_station_id` int(11) NOT NULL,
  `departure_datetime` datetime NOT NULL,
  `arrival_datetime` datetime NOT NULL,
  `base_fare` decimal(10,2) NOT NULL,
  `status` enum('scheduled','delayed','cancelled','completed') NOT NULL DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: bookings
-- ------------------------------
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `booking_status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
  `refund_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `refund_status` enum('none','pending','processed','rejected') NOT NULL DEFAULT 'none',
  `cancellation_date` datetime DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: booking_passengers
-- ------------------------------
CREATE TABLE `booking_passengers` (
  `passenger_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `passenger_email` varchar(100) NOT NULL,
  `passenger_phone` varchar(20) NOT NULL,
  `passenger_ic` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Table: booking_seats
-- ------------------------------
CREATE TABLE `booking_seats` (
  `seat_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `passenger_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `coach_number` varchar(10) NOT NULL,
  `seat_number` varchar(5) NOT NULL,
  `class_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------
-- Indexes
-- ------------------------------
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `stations`
  ADD PRIMARY KEY (`station_id`),
  ADD UNIQUE KEY `station_code` (`station_code`);

ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_name` (`class_name`);

ALTER TABLE `trains`
  ADD PRIMARY KEY (`train_id`),
  ADD UNIQUE KEY `train_number` (`train_number`);

ALTER TABLE `train_classes`
  ADD PRIMARY KEY (`train_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `fk_schedules_train` (`train_id`),
  ADD KEY `fk_schedules_origin` (`origin_station_id`),
  ADD KEY `fk_schedules_destination` (`destination_station_id`);

ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `fk_bookings_user` (`user_id`),
  ADD KEY `fk_bookings_schedule` (`schedule_id`);

ALTER TABLE `booking_passengers`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`);

ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`,`coach_number`,`seat_number`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `fk_booking_seats_class` (`class_id`);

-- ------------------------------
-- Auto increment
-- ------------------------------
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `stations`
  MODIFY `station_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `trains`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

ALTER TABLE `booking_passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `booking_seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

-- ------------------------------
-- Foreign keys
-- ------------------------------
ALTER TABLE `train_classes`
  ADD CONSTRAINT `train_classes_ibfk_1` FOREIGN KEY (`train_id`) REFERENCES `trains` (`train_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `train_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_destination` FOREIGN KEY (`destination_station_id`) REFERENCES `stations` (`station_id`),
  ADD CONSTRAINT `fk_schedules_origin` FOREIGN KEY (`origin_station_id`) REFERENCES `stations` (`station_id`),
  ADD CONSTRAINT `fk_schedules_train` FOREIGN KEY (`train_id`) REFERENCES `trains` (`train_id`);

ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`),
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `booking_passengers`
  ADD CONSTRAINT `booking_passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

ALTER TABLE `booking_seats`
  ADD CONSTRAINT `booking_seats_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_seats_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `booking_passengers` (`passenger_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_booking_seats_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
