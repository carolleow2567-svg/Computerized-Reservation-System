<?php

class Booking
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Retrieve bookings with related passenger and schedule information
    public function getAll(): array
    {
        $sql = "SELECT
                    b.booking_id,
                    b.booking_reference,
                    b.booking_date,
                    b.total_amount,
                    b.booking_status,
                    u.full_name,
                    u.email,
                    t.train_number,
                    t.train_name,
                    o.station_name AS origin_name,
                    d.station_name AS destination_name,
                    s.departure_datetime,
                    s.arrival_datetime
                FROM bookings b
                INNER JOIN users u ON b.user_id = u.user_id
                INNER JOIN schedules s ON b.schedule_id = s.schedule_id
                INNER JOIN trains t ON s.train_id = t.train_id
                INNER JOIN stations o ON s.origin_station_id = o.station_id
                INNER JOIN stations d ON s.destination_station_id = d.station_id
                ORDER BY b.booking_date DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Retrieve a booking by ID
    // Retrieve a booking with its schedule status
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, s.status AS schedule_status
             FROM bookings b
             INNER JOIN schedules s ON b.schedule_id = s.schedule_id
             WHERE b.booking_id = :id"
        );

        $stmt->execute(['id' => $id]);

        $booking = $stmt->fetch();

        return $booking ?: null;
    }

    // Update booking status
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