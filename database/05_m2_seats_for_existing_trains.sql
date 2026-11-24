-- =====================================================
-- M2 TEST SEATS for trains that have no coaches yet (e.g. TR1001-TR1005).
-- Final seat inventory belongs to M8; this just lets M2 be tested with
-- the schedules created on M7's pages. Safe to run more than once.
-- Run AFTER 03_m2_seat_tables.sql.
-- =====================================================
USE computerized_reservation_system;

-- One Gold and one Silver coach for each train that has none
INSERT IGNORE INTO coaches (train_id, coach_code, class_name, total_rows)
SELECT t.train_id, 'G1', 'Gold', 8
  FROM trains t
 WHERE NOT EXISTS (SELECT 1 FROM coaches c WHERE c.train_id = t.train_id);

INSERT IGNORE INTO coaches (train_id, coach_code, class_name, total_rows)
SELECT t.train_id, 'S1', 'Silver', 8
  FROM trains t
 WHERE NOT EXISTS (SELECT 1 FROM coaches c WHERE c.train_id = t.train_id AND c.coach_code = 'S1')
   AND t.train_number LIKE 'TR%';

-- Seats 1A..8D for every coach that has no seats yet
INSERT IGNORE INTO seats (coach_id, seat_number)
SELECT c.coach_id, CONCAT(r.n, l.letter)
  FROM coaches c
  JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
        UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) r
       ON r.n <= c.total_rows
  JOIN (SELECT 'A' letter UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D') l
 WHERE NOT EXISTS (SELECT 1 FROM seats s WHERE s.coach_id = c.coach_id);
