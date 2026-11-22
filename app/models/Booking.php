<?php

class Booking
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Retrieve all bookings of ONE passenger, with optional filters.
    // user_id always comes from the session, never from the URL.
    public function getByUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT b.booking_id,
                       b.booking_reference,
                       b.booking_date,
                       b.total_amount,
                       b.booking_status,
                       s.departure_datetime,
                       s.arrival_datetime,
                       t.train_number,
                       t.train_name,
                       o.station_name AS origin_name,
                       d.station_name AS destination_name,
                       (SELECT cr.request_status
                          FROM cancellation_requests cr
                         WHERE cr.booking_id = b.booking_id
                         ORDER BY cr.request_id DESC
                         LIMIT 1) AS cancel_status
                FROM bookings b
                INNER JOIN schedules s ON b.schedule_id = s.schedule_id
                INNER JOIN trains t ON s.train_id = t.train_id
                INNER JOIN stations o ON s.origin_station_id = o.station_id
                INNER JOIN stations d ON s.destination_station_id = d.station_id
                WHERE b.user_id = :uid";

        $params = ['uid' => $userId];

        if (!empty($filters['keyword'])) {
            $sql .= " AND b.booking_reference LIKE :keyword";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.booking_status = :status";
            $params['status'] = $filters['status'];
        }

        if ($filters['period'] === 'upcoming') {
            $sql .= " AND s.departure_datetime >= NOW()";
        } elseif ($filters['period'] === 'past') {
            $sql .= " AND s.departure_datetime < NOW()";
        }

        $sql .= " ORDER BY s.departure_datetime DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}