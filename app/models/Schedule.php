<?php

class Schedule
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAll(): array
    {
        $sql = "SELECT s.*, t.train_number, t.train_name,
                       o.station_name AS origin_name,
                       d.station_name AS destination_name
                FROM schedules s
                INNER JOIN trains t ON s.train_id = t.train_id
                INNER JOIN stations o ON s.origin_station_id = o.station_id
                INNER JOIN stations d ON s.destination_station_id = d.station_id
                ORDER BY s.departure_datetime DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveTrains(): array
    {
        $sql = "SELECT train_id, train_number, train_name
                FROM trains
                WHERE status = 'active'
                ORDER BY train_number ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getActiveStations(): array
    {
        $sql = "SELECT station_id, station_code, station_name
                FROM stations
                WHERE status = 'active'
                ORDER BY station_name ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO schedules
                (train_id, origin_station_id, destination_station_id,
                 departure_datetime, arrival_datetime, base_fare, status)
                VALUES
                (:train_id, :origin, :destination,
                 :departure, :arrival, :fare, :status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'train_id' => $data['train_id'],
            'origin' => $data['origin_station_id'],
            'destination' => $data['destination_station_id'],
            'departure' => $data['departure_datetime'],
            'arrival' => $data['arrival_datetime'],
            'fare' => $data['base_fare'],
            'status' => $data['status']
        ]);
    }

    public function isActiveTrain(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM trains
            WHERE train_id = :id AND status = 'active'"
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function isActiveStation(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM stations
            WHERE station_id = :id AND status = 'active'"
        );
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
          "SELECT * FROM schedules WHERE schedule_id = :id"
        );
        $stmt->execute(['id' => $id]);

        $schedule = $stmt->fetch();

        return $schedule ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE schedules
                SET train_id = :train_id,
                    origin_station_id = :origin,
                    destination_station_id = :destination,
                    departure_datetime = :departure,
                    arrival_datetime = :arrival,
                    base_fare = :fare,
                    status = :status
                WHERE schedule_id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'train_id' => $data['train_id'],
            'origin' => $data['origin_station_id'],
            'destination' => $data['destination_station_id'],
            'departure' => $data['departure_datetime'],
            'arrival' => $data['arrival_datetime'],
            'fare' => $data['base_fare'],
            'status' => $data['status'],
            'id' => $id
        ]);
    }

    public function hasConfirmedBookings(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE schedule_id = :id
                AND booking_status IN ('confirmed','pending')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function cancel(int $id): bool
    {
        $sql = "UPDATE schedules
                SET status = 'cancelled'
                WHERE schedule_id = :id
                AND status IN ('scheduled', 'delayed')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}