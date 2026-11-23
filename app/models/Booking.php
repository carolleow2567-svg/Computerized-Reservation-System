<?php

class Booking
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Retrieve all bookings with account, passenger, train,
    // seat, payment and refund information.
    public function getAll(): array
    {
        $sql = "SELECT
                    b.booking_id,
                    b.booking_reference,
                    b.booking_date,
                    b.total_amount,
                    b.booking_status,
                    b.payment_status,
                    b.refund_amount,
                    b.refund_status,
                    b.cancellation_date,
                    b.cancellation_reason,

                    -- Registered account holder (booked by)
                    u.full_name,
                    u.email,
                    u.phone_number,

                    t.train_number,
                    t.train_name,

                    o.station_name AS origin_name,
                    d.station_name AS destination_name,

                    s.departure_datetime,
                    s.arrival_datetime,

                    -- All passengers under this booking
                    (
                        SELECT GROUP_CONCAT(
                            bp.passenger_name
                            ORDER BY bp.passenger_id
                            SEPARATOR ', '
                        )
                        FROM booking_passengers bp
                        WHERE bp.booking_id = b.booking_id
                    ) AS passenger_details,

                    (
                        SELECT COUNT(*)
                        FROM booking_passengers bp
                        WHERE bp.booking_id = b.booking_id
                    ) AS passenger_count,

                    -- All seat assignments
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(
                                bs.coach_number,
                                '-',
                                bs.seat_number,
                                ' (',
                                bs.class_name,
                                ')'
                            )
                            ORDER BY bs.seat_id
                            SEPARATOR ', '
                        )
                        FROM booking_seats bs
                        WHERE bs.booking_id = b.booking_id
                    ) AS seat_details

                FROM bookings b

                INNER JOIN users u
                    ON b.user_id = u.user_id

                INNER JOIN schedules s
                    ON b.schedule_id = s.schedule_id

                INNER JOIN trains t
                    ON s.train_id = t.train_id

                INNER JOIN stations o
                    ON s.origin_station_id = o.station_id

                INNER JOIN stations d
                    ON s.destination_station_id = d.station_id

                ORDER BY b.booking_date DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve complete booking information.
    public function getById(int $id): ?array
    {
        $sql = "SELECT
                    b.*,

                    -- Registered account holder
                    u.full_name AS account_name,
                    u.email AS account_email,
                    u.phone_number AS account_phone,

                    t.train_number,
                    t.train_name,

                    o.station_name AS origin_name,
                    d.station_name AS destination_name,

                    s.departure_datetime,
                    s.arrival_datetime,
                    s.status AS schedule_status

                FROM bookings b

                INNER JOIN users u
                    ON b.user_id = u.user_id

                INNER JOIN schedules s
                    ON b.schedule_id = s.schedule_id

                INNER JOIN trains t
                    ON s.train_id = t.train_id

                INNER JOIN stations o
                    ON s.origin_station_id = o.station_id

                INNER JOIN stations d
                    ON s.destination_station_id = d.station_id

                WHERE b.booking_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return null;
        }

        // Retrieve every passenger and their assigned seat.
        $passengerSql = "SELECT
                            bp.passenger_id,
                            bp.passenger_name,
                            bp.passenger_email,
                            bp.passenger_phone,
                            bp.passenger_ic,

                            bs.seat_id,
                            bs.coach_number,
                            bs.seat_number,
                            bs.class_name

                        FROM booking_passengers bp

                        LEFT JOIN booking_seats bs
                            ON bp.passenger_id = bs.passenger_id
                            AND bp.booking_id = bs.booking_id

                        WHERE bp.booking_id = :id

                        ORDER BY bp.passenger_id ASC";

        $stmt = $this->db->prepare($passengerSql);
        $stmt->execute(['id' => $id]);

        $booking['passengers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retrieve all seats, including any not yet assigned
        // to an individual passenger.
        $seatSql = "SELECT
                        seat_id,
                        passenger_id,
                        coach_number,
                        seat_number,
                        class_name
                    FROM booking_seats
                    WHERE booking_id = :id
                    ORDER BY seat_id ASC";

        $stmt = $this->db->prepare($seatSql);
        $stmt->execute(['id' => $id]);

        $booking['seats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $booking;
    }

    // Update booking status.
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE bookings
             SET booking_status = :status
             WHERE booking_id = :id"
        );

        return $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }
}