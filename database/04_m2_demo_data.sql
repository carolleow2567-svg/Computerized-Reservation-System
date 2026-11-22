-- =====================================================
-- M2 DEMO DATA (for testing only). Safe to run more than once.
-- Run AFTER 03_m2_seat_tables.sql.
-- =====================================================
USE computerized_reservation_system;

-- Demo passengers (dummy password, cannot log in; used by M2's DEV_LOGIN switch)
INSERT IGNORE INTO users (full_name, email, password, role) VALUES
    ('M2 Demo Passenger', 'm2demo@example.com',  'DEV_ONLY_NOT_A_HASH', 'passenger'),
    ('M2 Other Passenger','m2other@example.com', 'DEV_ONLY_NOT_A_HASH', 'passenger');

INSERT IGNORE INTO stations (station_code, station_name, city) VALUES
    ('BTW','Butterworth','Butterworth'), ('TPG','Taiping','Taiping'), ('IPH','Ipoh','Ipoh'),
    ('KLS','Kuala Lumpur','Kuala Lumpur'), ('SBN','Seremban','Seremban'), ('GMS','Gemas','Gemas');

INSERT IGNORE INTO trains (train_number, train_name, train_type) VALUES
    ('ETS 9','ETS Platinum','ETS'), ('ETS 23','ETS Gold','ETS'), ('ETS 31','ETS Gold','ETS'),
    ('ETS 10','ETS Platinum','ETS'), ('ETS 24','ETS Gold','ETS');

-- Schedules for the next 3 days (skips ones that already exist)
INSERT INTO schedules (train_id, origin_station_id, destination_station_id, departure_datetime, arrival_datetime, base_fare, status)
SELECT t.train_id, o.station_id, d.station_id,
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL n.k DAY), tr.dep),
       DATE_ADD(TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL n.k DAY), tr.dep), INTERVAL 345 MINUTE),
       56.40, 'scheduled'
  FROM (SELECT 'ETS 9' no, 'BTW' oc, 'GMS' dc, '07:30:00' dep
        UNION ALL SELECT 'ETS 23','BTW','GMS','09:05:00'
        UNION ALL SELECT 'ETS 31','BTW','GMS','12:20:00'
        UNION ALL SELECT 'ETS 10','GMS','BTW','15:40:00'
        UNION ALL SELECT 'ETS 24','GMS','BTW','18:10:00') tr
  JOIN (SELECT 1 k UNION ALL SELECT 2 UNION ALL SELECT 3) n
  JOIN trains   t ON t.train_number = tr.no
  JOIN stations o ON o.station_code = tr.oc
  JOIN stations d ON d.station_code = tr.dc
 WHERE NOT EXISTS (SELECT 1 FROM schedules s
                    WHERE s.train_id = t.train_id
                      AND s.departure_datetime = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL n.k DAY), tr.dep));

-- Intermediate stops for the demo schedules (S = Butterworth -> Gemas, N = Gemas -> Butterworth)
INSERT INTO schedule_stops (schedule_id, station_id, stop_seq, arrival_datetime, departure_datetime, fare_from_origin)
SELECT s.schedule_id, st.station_id, x.seq,
       IF(x.seq = 1, NULL, DATE_ADD(s.departure_datetime, INTERVAL x.mins MINUTE)),
       IF(x.seq = 6, NULL, DATE_ADD(s.departure_datetime, INTERVAL x.mins + 2 MINUTE)),
       x.fare
  FROM schedules s
  JOIN trains   t  ON t.train_id = s.train_id
  JOIN stations so ON so.station_id = s.origin_station_id
  JOIN (SELECT 'S' dr,'BTW' code,1 seq,0 mins, 0.00 fare UNION ALL SELECT 'S','TPG',2,55, 8.64
        UNION ALL SELECT 'S','IPH',3,110,18.96 UNION ALL SELECT 'S','KLS',4,230,39.36
        UNION ALL SELECT 'S','SBN',5,290,47.40 UNION ALL SELECT 'S','GMS',6,345,56.40
        UNION ALL SELECT 'N','GMS',1,0, 0.00 UNION ALL SELECT 'N','SBN',2,55, 9.00
        UNION ALL SELECT 'N','KLS',3,115,17.04 UNION ALL SELECT 'N','IPH',4,235,37.44
        UNION ALL SELECT 'N','TPG',5,290,47.76 UNION ALL SELECT 'N','BTW',6,345,56.40) x
       ON x.dr = IF(so.station_code = 'BTW', 'S', 'N')
  JOIN stations st ON st.station_code = x.code
 WHERE t.train_number IN ('ETS 9','ETS 23','ETS 31','ETS 10','ETS 24')
   AND NOT EXISTS (SELECT 1 FROM schedule_stops z WHERE z.schedule_id = s.schedule_id);

-- Coaches: Platinum P1; Gold G1 + G2; Silver S1
INSERT IGNORE INTO coaches (train_id, coach_code, class_name, total_rows)
SELECT train_id, 'P1', 'Platinum', 6 FROM trains WHERE train_number IN ('ETS 9','ETS 10');
INSERT IGNORE INTO coaches (train_id, coach_code, class_name, total_rows)
SELECT t.train_id, c.code, 'Gold', 8 FROM trains t
  JOIN (SELECT 'G1' code UNION ALL SELECT 'G2') c
 WHERE t.train_number IN ('ETS 9','ETS 23','ETS 31','ETS 10','ETS 24');
INSERT IGNORE INTO coaches (train_id, coach_code, class_name, total_rows)
SELECT train_id, 'S1', 'Silver', 8 FROM trains WHERE train_number IN ('ETS 23','ETS 31','ETS 24');

-- Seats 1A..ND for every coach
INSERT IGNORE INTO seats (coach_id, seat_number)
SELECT c.coach_id, CONCAT(r.n, l.letter)
  FROM coaches c
  JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
        UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) r
       ON r.n <= c.total_rows
  JOIN (SELECT 'A' letter UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D') l;

-- Seats the admin marked unavailable: coach G1 seats 1A and 1B on ETS 9
UPDATE seats se
  JOIN coaches co ON co.coach_id = se.coach_id
  JOIN trains  t  ON t.train_id = co.train_id
   SET se.status = 'unavailable'
 WHERE t.train_number = 'ETS 9' AND co.coach_code = 'G1' AND se.seat_number IN ('1A','1B');

-- Sample confirmed seats on tomorrow's ETS 9 (to test segment overlap), booked by the "other" passenger:
--   2A,3A-3D : Butterworth(1) -> Ipoh(3)       2B : Ipoh(3) -> Gemas(6)
INSERT INTO seat_allocations (schedule_id, seat_id, user_id, from_seq, to_seq, allocation_status)
SELECT s.schedule_id, se.seat_id, u.user_id,
       IF(se.seat_number = '2B', 3, 1), IF(se.seat_number = '2B', 6, 3), 'confirmed'
  FROM schedules s
  JOIN trains  t  ON t.train_id = s.train_id AND t.train_number = 'ETS 9'
  JOIN coaches co ON co.train_id = t.train_id AND co.coach_code = 'G1'
  JOIN seats   se ON se.coach_id = co.coach_id AND se.seat_number IN ('2A','2B','3A','3B','3C','3D')
  JOIN users   u  ON u.email = 'm2other@example.com'
 WHERE DATE(s.departure_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
   AND NOT EXISTS (SELECT 1 FROM seat_allocations a WHERE a.schedule_id = s.schedule_id AND a.seat_id = se.seat_id);
