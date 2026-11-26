-- =====================================================
-- M2 / M8: seat & route tables (extends 01_m7_create_tables.sql)
-- Run AFTER 01_m7_create_tables.sql. Does not change any existing table.
-- =====================================================
USE computerized_reservation_system;

-- Fare multiplier per class. Class price = segment base fare x multiplier.
CREATE TABLE IF NOT EXISTS travel_classes (
    class_name      VARCHAR(20) PRIMARY KEY,
    fare_multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    sort_order      TINYINT NOT NULL DEFAULT 0
);
INSERT IGNORE INTO travel_classes (class_name, fare_multiplier, sort_order) VALUES
    ('Platinum', 1.40, 1), ('Gold', 1.00, 2), ('Silver', 0.70, 3);

-- Intermediate stops of a schedule (optional). A schedule WITHOUT rows here is
-- treated as a 2-stop trip (origin -> destination) by the view below, so
-- schedules created on M7's page work immediately.
-- fare_from_origin = cumulative standard fare from the origin to this stop;
-- the last stop should equal schedules.base_fare.
CREATE TABLE IF NOT EXISTS schedule_stops (
    schedule_id        INT NOT NULL,
    station_id         INT NOT NULL,
    stop_seq           TINYINT NOT NULL,
    arrival_datetime   DATETIME NULL,
    departure_datetime DATETIME NULL,
    fare_from_origin   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (schedule_id, stop_seq),
    UNIQUE KEY uq_schedule_station (schedule_id, station_id),
    CONSTRAINT fk_stops_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id) ON DELETE CASCADE,
    CONSTRAINT fk_stops_station  FOREIGN KEY (station_id)  REFERENCES stations(station_id)
);

-- Coaches and seats belong to a TRAIN (same layout for every schedule of that train)
CREATE TABLE IF NOT EXISTS coaches (
    coach_id   INT AUTO_INCREMENT PRIMARY KEY,
    train_id   INT NOT NULL,
    coach_code VARCHAR(10) NOT NULL,
    class_name VARCHAR(20) NOT NULL,
    total_rows TINYINT NOT NULL DEFAULT 8,          -- 4 seats per row: A B | C D
    UNIQUE KEY uq_coach (train_id, coach_code),
    CONSTRAINT fk_coaches_train FOREIGN KEY (train_id) REFERENCES trains(train_id) ON DELETE CASCADE,
    CONSTRAINT fk_coaches_class FOREIGN KEY (class_name) REFERENCES travel_classes(class_name)
);

CREATE TABLE IF NOT EXISTS seats (
    seat_id     INT AUTO_INCREMENT PRIMARY KEY,
    coach_id    INT NOT NULL,
    seat_number VARCHAR(5) NOT NULL,                -- e.g. 12A
    status      ENUM('available','unavailable') NOT NULL DEFAULT 'available',  -- admin switch (M8)
    UNIQUE KEY uq_seat (coach_id, seat_number),
    CONSTRAINT fk_seats_coach FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE CASCADE
);

-- Who holds / owns a seat on a schedule, for which segment (from_seq -> to_seq).
-- Two journeys overlap when: new.from_seq < old.to_seq AND new.to_seq > old.from_seq
-- 'held' = temporary hold while the passenger books (expires).
-- M3 sets booking_id and changes 'held' -> 'confirmed' after successful payment.
CREATE TABLE IF NOT EXISTS seat_allocations (
    allocation_id     INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id       INT NOT NULL,
    seat_id           INT NOT NULL,
    user_id           INT NOT NULL,
    booking_id        INT NULL,
    from_seq          TINYINT NOT NULL,
    to_seq            TINYINT NOT NULL,
    allocation_status ENUM('held','confirmed','cancelled') NOT NULL,
    hold_expires_at   DATETIME NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_alloc_lookup (schedule_id, seat_id),
    CONSTRAINT fk_alloc_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id),
    CONSTRAINT fk_alloc_seat     FOREIGN KEY (seat_id)     REFERENCES seats(seat_id),
    CONSTRAINT fk_alloc_user     FOREIGN KEY (user_id)     REFERENCES users(user_id),
    CONSTRAINT fk_alloc_booking  FOREIGN KEY (booking_id)  REFERENCES bookings(booking_id)
);

-- One list of stops for every schedule: real stops if defined, otherwise origin + destination.
CREATE OR REPLACE VIEW v_schedule_stops AS
    SELECT schedule_id, station_id, stop_seq, arrival_datetime, departure_datetime, fare_from_origin
      FROM schedule_stops
    UNION ALL
    SELECT s.schedule_id, s.origin_station_id, 1, NULL, s.departure_datetime, 0.00
      FROM schedules s
     WHERE NOT EXISTS (SELECT 1 FROM schedule_stops x WHERE x.schedule_id = s.schedule_id)
    UNION ALL
    SELECT s.schedule_id, s.destination_station_id, 2, s.arrival_datetime, NULL, s.base_fare
      FROM schedules s
     WHERE NOT EXISTS (SELECT 1 FROM schedule_stops x WHERE x.schedule_id = s.schedule_id);
