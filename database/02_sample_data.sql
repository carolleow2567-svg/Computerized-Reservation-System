USE computerized_reservation_system;

-- ==========================================
-- 1. SAMPLE STATIONS
-- ==========================================

INSERT INTO stations (station_code, station_name, city, status)
VALUES
('KL', 'Kuala Lumpur Sentral', 'Kuala Lumpur', 'active'),
('JB', 'Johor Bahru Sentral', 'Johor Bahru', 'active'),
('IP', 'Ipoh Railway Station', 'Ipoh', 'active'),
('PG', 'Padang Besar Station', 'Padang Besar', 'active'),
('SP', 'Sungai Petani Station', 'Sungai Petani', 'active'),
('BP', 'Batu Pahat Station', 'Batu Pahat', 'active');

-- ==========================================
-- 2. SAMPLE TRAINS
-- ==========================================

INSERT INTO trains (train_number, train_name, train_type, status)
VALUES
('TR1001', 'Southern Express', 'ETS', 'active'),
('TR1002', 'Northern Star', 'ETS', 'active'),
('TR1003', 'Coastal Runner', 'Intercity', 'active'),
('TR1004', 'Metro Link', 'Intercity', 'active'),
('TR1005', 'Highland Express', 'ETS', 'active');

-- ==========================================
-- 3. SAMPLE SCHEDULES
-- ==========================================

INSERT INTO schedules
(
    train_id,
    origin_station_id,
    destination_station_id,
    departure_datetime,
    arrival_datetime,
    base_fare,
    status
)
VALUES
(1, 1, 3, '2026-12-01 08:00:00', '2026-12-01 11:00:00', 35.00, 'scheduled'),
(2, 3, 4, '2026-12-01 09:30:00', '2026-12-01 13:00:00', 45.00, 'scheduled'),
(3, 2, 1, '2026-12-01 14:00:00', '2026-12-01 18:30:00', 50.00, 'scheduled'),
(4, 1, 5, '2026-12-02 10:00:00', '2026-12-02 12:30:00', 25.00, 'scheduled'),
(5, 5, 6, '2026-12-02 15:00:00', '2026-12-02 18:00:00', 30.00, 'scheduled');