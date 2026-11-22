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

    // Retrieve ONE ticket. Ownership is enforced in the SQL itself:
    // the booking must belong to the logged-in user.
    public function getTicketForUser(int $bookingId, int $userId): ?array
    {
        $sql = "SELECT b.booking_id,
                       b.booking_reference,
                       b.booking_date,
                       b.total_amount,
                       b.booking_status,
                       b.verification_token,
                       s.departure_datetime,
                       s.arrival_datetime,
                       t.train_number,
                       t.train_name,
                       t.train_type,
                       o.station_name AS origin_name,
                       d.station_name AS destination_name,
                       u.full_name AS account_name
                FROM bookings b
                INNER JOIN schedules s ON b.schedule_id = s.schedule_id
                INNER JOIN trains t ON s.train_id = t.train_id
                INNER JOIN stations o ON s.origin_station_id = o.station_id
                INNER JOIN stations d ON s.destination_station_id = d.station_id
                INNER JOIN users u ON b.user_id = u.user_id
                WHERE b.booking_id = :bid
                  AND b.user_id = :uid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $bookingId, 'uid' => $userId]);

        $ticket = $stmt->fetch();

        return $ticket ?: null;
    }

    // Passengers and seats of a booking.
    // NOTE: temporary table; change this query when M3/M8 finalise theirs.
    public function getPassengers(int $bookingId): array
    {
        $sql = "SELECT passenger_name, seat_number
                FROM booking_passengers
                WHERE booking_id = :bid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['bid' => $bookingId]);

        return $stmt->fetchAll();
    }

    // Create the verification token once (used inside the QR code).
    // It is random, so it contains no personal information.
    public function ensureToken(int $bookingId, int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        $sql = "UPDATE bookings
                SET verification_token = :token
                WHERE booking_id = :bid
                  AND user_id = :uid
                  AND verification_token IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token, 'bid' => $bookingId, 'uid' => $userId]);

        $stmt = $this->db->prepare(
            "SELECT verification_token FROM bookings
             WHERE booking_id = :bid AND user_id = :uid"
        );
        $stmt->execute(['bid' => $bookingId, 'uid' => $userId]);

        return (string)$stmt->fetchColumn();
    }

    // Create a cancellation request after re-checking everything on the server.
    // Returns an error message, or null on success.
    public function requestCancellation(int $bookingId, int $userId, string $reason): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT b.booking_status, s.departure_datetime
             FROM bookings b
             INNER JOIN schedules s ON b.schedule_id = s.schedule_id
             WHERE b.booking_id = :bid AND b.user_id = :uid"
        );
        $stmt->execute(['bid' => $bookingId, 'uid' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return 'Booking not found.';
        }
        if ($row['booking_status'] !== 'confirmed') {
            return 'Only confirmed bookings can be cancelled.';
        }
        if (strtotime($row['departure_datetime']) <= time()) {
            return 'This trip has already departed.';
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM cancellation_requests
             WHERE booking_id = :bid AND request_status IN ('pending', 'approved', 'refunded')"
        );
        $stmt->execute(['bid' => $bookingId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'A cancellation request already exists for this booking.';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO cancellation_requests (booking_id, user_id, reason)
             VALUES (:bid, :uid, :reason)"
        );
        $stmt->execute(['bid' => $bookingId, 'uid' => $userId, 'reason' => $reason]);

        return null;
    }
}