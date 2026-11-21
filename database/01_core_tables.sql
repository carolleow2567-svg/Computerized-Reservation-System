-- =====================================================
-- CRS: Computerized Reservation & E-Ticketing System
-- Core Database Tables
-- =====================================================

USE computerized_reservation_system;

-- 1. USERS TABLE
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    role ENUM('passenger', 'admin') NOT NULL DEFAULT 'passenger',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TRAINS TABLE
CREATE TABLE trains (
    train_id INT AUTO_INCREMENT PRIMARY KEY,
    train_number VARCHAR(20) NOT NULL UNIQUE,
    train_name VARCHAR(100) NOT NULL,
    train_type VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

-- 3. STATIONS TABLE
CREATE TABLE stations (
    station_id INT AUTO_INCREMENT PRIMARY KEY,
    station_code VARCHAR(20) NOT NULL UNIQUE,
    station_name VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
);

-- 4. SCHEDULES TABLE
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    train_id INT NOT NULL,
    origin_station_id INT NOT NULL,
    destination_station_id INT NOT NULL,
    departure_datetime DATETIME NOT NULL,
    arrival_datetime DATETIME NOT NULL,
    base_fare DECIMAL(10,2) NOT NULL,
    status ENUM('scheduled', 'delayed', 'cancelled', 'completed')
        NOT NULL DEFAULT 'scheduled',

    CONSTRAINT fk_schedules_train
        FOREIGN KEY (train_id)
        REFERENCES trains(train_id),

    CONSTRAINT fk_schedules_origin
        FOREIGN KEY (origin_station_id)
        REFERENCES stations(station_id),

    CONSTRAINT fk_schedules_destination
        FOREIGN KEY (destination_station_id)
        REFERENCES stations(station_id)
);

-- 5. BOOKINGS TABLE
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    booking_status ENUM('pending', 'confirmed', 'cancelled')
        NOT NULL DEFAULT 'pending',

    CONSTRAINT fk_bookings_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id),

    CONSTRAINT fk_bookings_schedule
        FOREIGN KEY (schedule_id)
        REFERENCES schedules(schedule_id)
);