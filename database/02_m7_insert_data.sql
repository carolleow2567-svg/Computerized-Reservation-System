SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE `computerized_reservation_system`;


-- Data: users
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone_number`, `role`, `created_at`) VALUES
(1, 'Test Passanger', 'testpassanger@example.com', 'TestPassword123!', '0123456789', 'passenger', '2026-11-23 00:44:25');

-- Data: stations
INSERT INTO `stations` (`station_id`, `station_code`, `station_name`, `city`, `status`) VALUES
(1, 'KL', 'Kuala Lumpur Sentral', 'Kuala Lumpur', 'active'),
(2, 'JB', 'Johor Bahru Sentral', 'Johor Bahru', 'active'),
(3, 'IP', 'Ipoh Railway Station', 'Ipoh', 'active'),
(4, 'PG', 'Padang Besar Station', 'Padang Besar', 'active'),
(5, 'SP', 'Sungai Petani Station', 'Sungai Petani', 'active'),
(6, 'BP', 'Batu Pahat Station', 'Batu Pahat', 'active'),
(7, 'CRSKU', 'Kuantan Central', 'Kuantan', 'active'),
(8, 'CRSKT', 'Kuala Terengganu Central', 'Kuala Terengganu', 'active'),
(9, 'CRSAL', 'Alor Setar Central', 'Alor Setar', 'active'),
(10, 'CRSML', 'Melaka Sentral', 'Melaka', 'active');

-- Data: classes
INSERT INTO `classes` (`class_id`, `class_name`, `class_label`) VALUES
(1, 'Silver', 'S'),
(2, 'Gold', 'G'),
(3, 'Platinum', 'P');

-- Data: trains
INSERT INTO `trains` (`train_id`, `train_number`, `train_name`, `train_type`, `status`, `created_at`, `updated_at`, `capacity`, `operating_days`) VALUES
(1, 'TR1001', 'Southern Express', 'ETS', 'active', '2026-11-21 18:44:27', '2026-11-21 18:44:27', 300, 'Daily'),
(2, 'TR1002', 'Northern Star', 'ETS', 'active', '2026-11-21 18:44:27', '2026-11-21 18:44:27', 300, 'Daily'),
(3, 'TR1003', 'Coastal Runner', 'Intercity', 'active', '2026-11-21 18:44:27', '2026-11-21 18:44:27', 300, 'Daily'),
(4, 'TR1004', 'Metro Link', 'Intercity', 'active', '2026-11-21 18:44:27', '2026-11-21 18:44:27', 300, 'Daily'),
(5, 'TR1005', 'Highland Express', 'ETS', 'active', '2026-11-21 18:44:27', '2026-11-21 18:44:27', 300, 'Daily'),
(6, 'TR1006', 'Update Train', 'ETS', 'inactive', '2026-11-21 19:51:01', '2026-11-22 07:42:35', 300, 'Daily'),
(8, 'TR1007', 'East Coast Express', 'Express', 'active', '2026-11-23 15:21:36', '2026-11-23 15:21:36', 300, 'Daily'),
(9, 'TR1008', 'Northern Explorer', 'Intercity', 'active', '2026-11-23 15:21:36', '2026-11-23 15:21:36', 250, 'Daily'),
(10, 'TR1009', 'Southern Connect', 'Express', 'active', '2026-11-23 15:21:36', '2026-11-23 15:21:36', 300, 'Monday-Sunday'),
(11, 'TR1010', 'Malaysia Explorer', 'Intercity', 'active', '2026-11-23 15:21:36', '2026-11-23 15:21:36', 200, 'Daily');

-- Data: train_classes
INSERT INTO `train_classes` (`train_id`, `class_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3),
(3, 1),
(3, 2),
(3, 3),
(4, 1),
(5, 1),
(5, 2),
(8, 1),
(8, 2),
(8, 3),
(9, 1),
(9, 2),
(9, 3),
(10, 1),
(10, 2),
(10, 3),
(11, 1),
(11, 2),
(11, 3);

-- Data: schedules
INSERT INTO `schedules` (`schedule_id`, `train_id`, `origin_station_id`, `destination_station_id`, `departure_datetime`, `arrival_datetime`, `base_fare`, `status`) VALUES
(1, 1, 1, 3, '2026-12-01 08:00:00', '2026-12-01 11:00:00', 35.00, 'scheduled'),
(2, 2, 3, 4, '2026-12-01 09:30:00', '2026-12-01 13:00:00', 45.00, 'scheduled'),
(3, 3, 2, 1, '2026-12-01 14:00:00', '2026-12-01 18:30:00', 50.00, 'scheduled'),
(4, 4, 1, 5, '2026-12-02 10:00:00', '2026-12-02 12:30:00', 25.00, 'scheduled'),
(5, 5, 5, 6, '2026-12-02 15:00:00', '2026-12-02 18:00:00', 30.00, 'scheduled'),
(6, 2, 4, 1, '2026-11-06 20:30:00', '2026-11-07 00:00:00', 82.00, 'cancelled'),
(7, 3, 5, 6, '2026-11-22 19:43:00', '2026-11-23 19:43:00', 110.00, 'completed'),
(8, 8, 7, 8, '2026-12-03 08:00:00', '2026-12-03 11:00:00', 45.00, 'scheduled'),
(9, 9, 9, 10, '2026-12-03 09:00:00', '2026-12-03 13:00:00', 55.00, 'scheduled'),
(10, 10, 10, 7, '2026-12-04 08:30:00', '2026-12-04 12:00:00', 65.00, 'scheduled'),
(11, 11, 8, 9, '2026-12-04 14:00:00', '2026-12-04 18:30:00', 75.00, 'scheduled'),
(12, 8, 7, 9, '2026-12-05 07:00:00', '2026-12-05 12:00:00', 85.00, 'scheduled'),
(13, 9, 9, 8, '2026-12-05 10:00:00', '2026-12-05 14:00:00', 60.00, 'scheduled'),
(14, 10, 10, 8, '2026-12-06 09:00:00', '2026-12-06 13:30:00', 90.00, 'scheduled'),
(15, 11, 8, 7, '2026-12-06 15:00:00', '2026-12-06 19:00:00', 70.00, 'scheduled');

-- Data: bookings
INSERT INTO `bookings` (`booking_id`, `user_id`, `schedule_id`, `booking_reference`, `booking_date`, `total_amount`, `booking_status`, `payment_status`, `refund_amount`, `refund_status`, `cancellation_date`, `cancellation_reason`) VALUES
(1, 1, 1, 'CRS-TEST-001', '2026-11-23 02:18:14', 50.00, 'cancelled', 'unpaid', 0.00, 'none', NULL, NULL),
(3, 1, 8, 'CRS-TEST-101', '2026-11-23 15:21:36', 90.00, 'cancelled', 'refunded', 45.00, 'processed', '2026-11-24 16:33:57', 'Test for cancellation fuunctionality'),
(4, 1, 9, 'CRS-TEST-102', '2026-11-23 15:21:36', 110.00, 'confirmed', 'paid', 0.00, 'none', NULL, NULL),
(5, 1, 10, 'CRS-TEST-103', '2026-11-23 15:21:36', 130.00, 'cancelled', 'paid', 0.00, 'none', '2026-11-23 23:21:36', 'Test cancellation for CRS administration'),
(6, 1, 11, 'CRS-TEST-104', '2026-11-23 15:21:36', 75.00, 'pending', 'failed', 0.00, 'none', NULL, NULL),
(7, 1, 12, 'CRS-TEST-105', '2026-11-23 15:21:36', 170.00, 'cancelled', 'paid', 100.00, 'pending', '2026-11-23 23:21:36', 'Test cancellation for CRS administration'),
(8, 1, 13, 'CRS-TEST-106', '2026-11-23 15:21:36', 60.00, 'confirmed', 'paid', 0.00, 'none', NULL, NULL),
(9, 1, 14, 'CRS-TEST-107', '2026-11-23 15:21:36', 90.00, 'cancelled', 'refunded', 90.00, 'processed', '2026-11-23 23:21:36', 'Test cancellation for CRS administration'),
(10, 1, 15, 'CRS-TEST-108', '2026-11-23 15:21:36', 70.00, 'cancelled', 'paid', 0.00, 'rejected', '2026-11-23 23:21:36', 'Test cancellation for CRS administration');

-- Data: booking_passengers
INSERT INTO `booking_passengers` (`passenger_id`, `booking_id`, `passenger_name`, `passenger_email`, `passenger_phone`, `passenger_ic`) VALUES
(1, 1, 'Test Passanger', 'testpassanger@example.com', '0123456789', '000000-00-0000'),
(2, 1, 'Mary Tan', 'mary@example.com', '0123456788', '920202-02-2345'),
(3, 1, 'David Tan', 'david@example.com', '0123456787', '950303-03-3456'),
(4, 3, 'Ahmad Firdaus', 'ahmad101@example.com', '0123000101', '900101-01-0101'),
(5, 3, 'Nur Aisyah', 'aisyah101@example.com', '0123000102', '920202-01-0202'),
(6, 4, 'Daniel Tan', 'daniel102@example.com', '0123000103', '950303-01-0303'),
(7, 4, 'Lim Wei Ling', 'weiling102@example.com', '0123000104', '960404-01-0404'),
(8, 5, 'Siti Aminah', 'aminah103@example.com', '0123000105', '970505-01-0505'),
(9, 6, 'Raj Kumar', 'raj104@example.com', '0123000106', '980606-01-0606'),
(10, 7, 'Muhammad Hakim', 'hakim105@example.com', '0123000107', '990707-01-0707'),
(11, 7, 'Nur Farah', 'farah105@example.com', '0123000108', '000808-01-0808'),
(12, 8, 'Chong Mei', 'mei106@example.com', '0123000109', '010909-01-0909'),
(13, 9, 'Arun Raj', 'arun107@example.com', '0123000110', '021010-01-1010'),
(14, 10, 'Kavitha Devi', 'kavitha108@example.com', '0123000111', '031111-01-1111'),
(15, 10, 'Nur Iman', 'iman108@example.com', '0123000112', '041212-01-1212');

-- Data: booking_seats
INSERT INTO `booking_seats` (`seat_id`, `booking_id`, `passenger_id`, `class_id`, `coach_number`, `seat_number`, `class_name`) VALUES
(1, 3, 4, 1, 'F', '1A', 'Silver'),
(2, 3, 5, 1, 'F', '1B', 'Silver'),
(3, 4, 6, 2, 'C', '1A', 'Gold'),
(4, 4, 7, 2, 'C', '1B', 'Gold'),
(5, 5, 8, 1, 'F', '1C', 'Silver'),
(6, 6, 9, 1, 'F', '1D', 'Silver'),
(7, 7, 10, 3, 'A', '1A', 'Platinum'),
(8, 7, 11, 3, 'A', '1B', 'Platinum'),
(9, 8, 12, 2, 'C', '1C', 'Gold'),
(10, 9, 13, 1, 'F', '2A', 'Silver'),
(11, 10, 14, 1, 'F', '2B', 'Silver'),
(12, 10, 15, 1, 'F', '2C', 'Silver');

SET FOREIGN_KEY_CHECKS = 1;
