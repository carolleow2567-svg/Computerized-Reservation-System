<?php
/**
 * M2 data layer: train search, seat map, seat holding.
 * Every query uses PDO prepared statements.
 */
class SeatModel
{
    /** Seat is blocked for a journey when another allocation overlaps its segment. */
    private const OVERLAP = "a.from_seq < ? AND a.to_seq > ?";

    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec("SET time_zone = '+08:00'");
    }

    /* ---------------- lookups ---------------- */
    public function stations(): array
    {
        return $this->pdo->query(
            "SELECT station_id, station_name AS name FROM stations WHERE status = 'active' ORDER BY station_name"
        )->fetchAll();
    }

    public function classes(): array
    {
        return array_column(
            $this->pdo->query('SELECT class_name FROM travel_classes ORDER BY sort_order')->fetchAll(),
            'class_name'
        );
    }

    /* ---------------- search ---------------- */
    /** Trains running from -> to that depart on $date, with free seats per class for that segment. */
    public function search(int $from, int $to, string $date, int $userId): array
    {
        $sql = "
          SELECT s.schedule_id, t.train_number, t.train_name,
                 f.departure_datetime AS dep, tt.arrival_datetime AS arr,
                 (tt.fare_from_origin - f.fare_from_origin) AS seg_fare,
                 c.class_name, tc.fare_multiplier,
                 (SELECT COUNT(*)
                    FROM seats se JOIN coaches co ON co.coach_id = se.coach_id
                   WHERE co.train_id = s.train_id AND co.class_name = c.class_name AND se.status = 'available'
                     AND NOT EXISTS (
                          SELECT 1 FROM seat_allocations a
                           WHERE a.schedule_id = s.schedule_id AND a.seat_id = se.seat_id
                             AND a.from_seq < tt.stop_seq AND a.to_seq > f.stop_seq
                             AND (a.allocation_status = 'confirmed'
                                  OR (a.allocation_status = 'held' AND a.hold_expires_at > NOW() AND a.user_id <> :u))
                     )) AS free
            FROM schedules s
            JOIN trains t ON t.train_id = s.train_id AND t.status = 'active'
            JOIN v_schedule_stops f  ON f.schedule_id  = s.schedule_id AND f.station_id  = :f
            JOIN v_schedule_stops tt ON tt.schedule_id = s.schedule_id AND tt.station_id = :t
            JOIN (SELECT DISTINCT train_id, class_name FROM coaches) c ON c.train_id = s.train_id
            JOIN travel_classes tc ON tc.class_name = c.class_name
           WHERE s.status IN ('scheduled','delayed')
             AND f.stop_seq < tt.stop_seq
             AND DATE(f.departure_datetime) = :d
             AND f.departure_datetime > NOW()
           ORDER BY s.schedule_id, tc.sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':u' => $userId, ':f' => $from, ':t' => $to, ':d' => $date]);

        $trains = [];
        foreach ($stmt->fetchAll() as $r) {
            $id = (int) $r['schedule_id'];
            $trains[$id] ??= [
                'schedule_id' => $id,
                'no'       => $r['train_number'],
                'name'     => $r['train_name'],
                'dep'      => substr($r['dep'], 11, 5),
                'arr'      => substr($r['arr'], 11, 5),
                'mins'     => (int) ((strtotime($r['arr']) - strtotime($r['dep'])) / 60),
                'next_day' => substr($r['arr'], 0, 10) > substr($r['dep'], 0, 10),
                'options'  => [],
            ];
            $trains[$id]['options'][$r['class_name']] = [
                'class' => $r['class_name'],
                'fare'  => round((float) $r['seg_fare'] * (float) $r['fare_multiplier'], 2),
                'free'  => (int) $r['free'],
            ];
        }
        return array_values($trains);
    }

    /** One schedule between two stations (must still be bookable), or null. */
    public function segment(int $scheduleId, int $from, int $to): ?array
    {
        $sql = "
          SELECT s.schedule_id, s.train_id, t.train_number AS train_no, t.train_name AS name,
                 f.stop_seq AS fs, tt.stop_seq AS ts,
                 f.departure_datetime AS dep, tt.arrival_datetime AS arr,
                 (tt.fare_from_origin - f.fare_from_origin) AS seg_fare
            FROM schedules s
            JOIN trains t ON t.train_id = s.train_id AND t.status = 'active'
            JOIN v_schedule_stops f  ON f.schedule_id  = s.schedule_id AND f.station_id  = ?
            JOIN v_schedule_stops tt ON tt.schedule_id = s.schedule_id AND tt.station_id = ?
           WHERE s.schedule_id = ? AND s.status IN ('scheduled','delayed')
             AND f.stop_seq < tt.stop_seq AND f.departure_datetime > NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$from, $to, $scheduleId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['dep_time'] = substr($row['dep'], 11, 8);
        $row['arr_time'] = substr($row['arr'], 11, 8);
        return $row;
    }

    /** [class => display price] for the classes a train offers on this segment. */
    public function classPrices(int $trainId, float $segFare): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT tc.class_name, tc.fare_multiplier, tc.sort_order
               FROM coaches c JOIN travel_classes tc ON tc.class_name = c.class_name
              WHERE c.train_id = ? ORDER BY tc.sort_order'
        );
        $stmt->execute([$trainId]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[$r['class_name']] = round($segFare * (float) $r['fare_multiplier'], 2);
        }
        return $out;
    }

    /**
     * Server-side fare for ONE passenger (for M3 to use at checkout; never trust browser prices).
     * Returns null if the schedule/stations/class are not valid.
     */
    public function fareFor(int $scheduleId, int $from, int $to, string $class): ?float
    {
        $seg = $this->segment($scheduleId, $from, $to);
        if (!$seg) {
            return null;
        }
        $prices = $this->classPrices((int) $seg['train_id'], (float) $seg['seg_fare']);
        return $prices[$class] ?? null;
    }

    /* ---------------- seat map ---------------- */
    /**
     * State per seat: av = available, sel = held by this passenger,
     * oc = occupied on an overlapping segment, un = unavailable (admin).
     * Returns [coachCode => ['rows' => int, 'free' => int, 'seats' => [seatNo => ['id'=>, 'state'=>]]]]
     */
    public function seatMap(int $scheduleId, int $trainId, string $class, int $fs, int $ts, int $userId): array
    {
        $sql = "
          SELECT se.seat_id, se.seat_number, se.status, co.coach_code, co.total_rows,
            EXISTS (SELECT 1 FROM seat_allocations a
                     WHERE a.schedule_id = ? AND a.seat_id = se.seat_id AND " . self::OVERLAP . "
                       AND a.allocation_status = 'held' AND a.hold_expires_at > NOW() AND a.user_id = ?) AS mine,
            EXISTS (SELECT 1 FROM seat_allocations a
                     WHERE a.schedule_id = ? AND a.seat_id = se.seat_id AND " . self::OVERLAP . "
                       AND (a.allocation_status = 'confirmed'
                            OR (a.allocation_status = 'held' AND a.hold_expires_at > NOW() AND a.user_id <> ?))) AS busy
            FROM seats se JOIN coaches co ON co.coach_id = se.coach_id
           WHERE co.train_id = ? AND co.class_name = ?
           ORDER BY co.coach_code, CAST(se.seat_number AS UNSIGNED), se.seat_number";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$scheduleId, $ts, $fs, $userId, $scheduleId, $ts, $fs, $userId, $trainId, $class]);

        $coaches = [];
        foreach ($stmt->fetchAll() as $r) {
            $code = $r['coach_code'];
            $coaches[$code] ??= ['rows' => (int) $r['total_rows'], 'free' => 0, 'seats' => []];
            if ($r['status'] !== 'available') {
                $state = 'un';
            } elseif ($r['busy']) {
                $state = 'oc';
            } elseif ($r['mine']) {
                $state = 'sel';
            } else {
                $state = 'av';
            }
            if ($state === 'av' || $state === 'sel') {
                $coaches[$code]['free']++;
            }
            $coaches[$code]['seats'][$r['seat_number']] = ['id' => (int) $r['seat_id'], 'state' => $state];
        }
        return $coaches;
    }

    /* ---------------- hold seats (transaction) ---------------- */
    /**
     * Re-checks everything on the server and holds the seats.
     * Returns ['ok' => true, 'segment' => [...], 'labels' => [...]]
     *      or ['ok' => false, 'message' => '...', 'to' => 'back'|'search'].
     */
    public function holdSeats(int $userId, int $scheduleId, int $from, int $to, string $class, array $ids): array
    {
        $seg = $this->segment($scheduleId, $from, $to);
        if (!$seg) {
            return ['ok' => false, 'to' => 'search',
                    'message' => 'This train is no longer available for your journey. Please search again.'];
        }

        $in = implode(',', array_fill(0, count($ids), '?'));
        try {
            $this->pdo->beginTransaction();

            // housekeeping: expired holds, and this passenger's earlier unfinished hold
            $this->pdo->exec("DELETE FROM seat_allocations WHERE allocation_status = 'held' AND hold_expires_at <= NOW()");
            $this->pdo->prepare("DELETE FROM seat_allocations WHERE allocation_status = 'held' AND user_id = ?")
                      ->execute([$userId]);

            // lock the seat rows; they must belong to this train + class and be available
            $stmt = $this->pdo->prepare(
                "SELECT se.seat_id, co.coach_code, se.seat_number
                   FROM seats se JOIN coaches co ON co.coach_id = se.coach_id
                  WHERE se.seat_id IN ($in) AND co.train_id = ? AND co.class_name = ? AND se.status = 'available'
                  FOR UPDATE"
            );
            $stmt->execute([...$ids, (int) $seg['train_id'], $class]);
            $valid = $stmt->fetchAll();
            if (count($valid) !== count($ids)) {
                $this->pdo->rollBack();
                return ['ok' => false, 'to' => 'back', 'message' => 'One of the seats you chose is not available. Please choose again.'];
            }

            // overlap check for this segment
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM seat_allocations a
                  WHERE a.schedule_id = ? AND a.seat_id IN ($in) AND " . self::OVERLAP . "
                    AND (a.allocation_status = 'confirmed'
                         OR (a.allocation_status = 'held' AND a.hold_expires_at > NOW()))"
            );
            $stmt->execute([$scheduleId, ...$ids, (int) $seg['ts'], (int) $seg['fs']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['ok' => false, 'to' => 'back', 'message' => 'Sorry, someone just booked one of those seats. Please choose different seats.'];
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO seat_allocations (schedule_id, seat_id, user_id, from_seq, to_seq, allocation_status, hold_expires_at)
                 VALUES (?, ?, ?, ?, ?, 'held', DATE_ADD(NOW(), INTERVAL " . (int) HOLD_MINUTES . " MINUTE))"
            );
            foreach ($ids as $id) {
                $insert->execute([$scheduleId, $id, $userId, (int) $seg['fs'], (int) $seg['ts']]);
            }
            $this->pdo->commit();
        } catch (PDOException $ex) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('SeatModel::holdSeats ' . $ex->getMessage());
            return ['ok' => false, 'to' => 'back', 'message' => 'We could not hold your seats. Please try again.'];
        }

        return [
            'ok'      => true,
            'segment' => $seg,
            'labels'  => array_map(fn($r) => $r['coach_code'] . '-' . $r['seat_number'], $valid),
        ];
    }
}
