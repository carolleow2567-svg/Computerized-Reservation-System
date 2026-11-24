<?php

class Train
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Retrieve all trains
    public function getAll(): array
    {
        $sql = "SELECT * FROM trains
                ORDER BY train_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Next upcoming (otherwise latest) non-cancelled schedule of every train,
    // indexed by train_id. Read-only; used for the extra columns on the train list.
    public function getScheduleSummaries(): array
    {
        $sql = "SELECT s.train_id,
                       o.station_name AS origin_name,
                       d.station_name AS destination_name,
                       s.departure_datetime,
                       s.arrival_datetime
                FROM schedules s
                INNER JOIN stations o ON o.station_id = s.origin_station_id
                INNER JOIN stations d ON d.station_id = s.destination_station_id
                WHERE s.schedule_id = (
                    SELECT s2.schedule_id
                    FROM schedules s2
                    WHERE s2.train_id = s.train_id
                      AND s2.status <> 'cancelled'
                    ORDER BY (s2.departure_datetime >= NOW()) DESC,
                             IF(s2.departure_datetime >= NOW(), s2.departure_datetime, NULL) ASC,
                             s2.departure_datetime DESC
                    LIMIT 1
                )";

        $rows = $this->db->query($sql)->fetchAll();
        $summaries = [];

        foreach ($rows as $row) {
            $summaries[(int) $row['train_id']] = $row;
        }

        return $summaries;
    }

    // Seat classes offered by each train (from the M2 coaches table), indexed by train_id.
    // Returns an empty array when the M2 seat tables are not installed.
    public function getClassSummaries(): array
    {
        try {
            $sql = "SELECT c.train_id,
                           GROUP_CONCAT(DISTINCT c.class_name
                                        ORDER BY tc.sort_order SEPARATOR ', ') AS classes
                    FROM coaches c
                    INNER JOIN travel_classes tc ON tc.class_name = c.class_name
                    GROUP BY c.train_id";

            $classes = [];

            foreach ($this->db->query($sql)->fetchAll() as $row) {
                $classes[(int) $row['train_id']] = $row['classes'];
            }

            return $classes;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Retrieve one train by ID
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM trains
                WHERE train_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $train = $stmt->fetch();

        return $train ?: null;
    }

    // Add a train
    public function create(array $data): bool
    {
        $sql = "INSERT INTO trains
                (train_number, train_name, train_type, status)
                VALUES
                (:number, :name, :type, :status)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'number' => $data['train_number'],
            'name' => $data['train_name'],
            'type' => $data['train_type'],
            'status' => $data['status']
        ]);
    }

    // Update a train
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE trains
                SET train_number = :number,
                    train_name = :name,
                    train_type = :type,
                    status = :status
                WHERE train_id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'number' => $data['train_number'],
            'name' => $data['train_name'],
            'type' => $data['train_type'],
            'status' => $data['status'],
            'id' => $id
        ]);
    }

    // Deactivate a train instead of permanently deleting it
    public function deactivate(int $id): bool
    {
        $sql = "UPDATE trains
                SET status = 'inactive'
                WHERE train_id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    // Check duplicate train number, excluding the current train
    public function existsByTrainNumber(string $number, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM trains
             WHERE train_number = :number";

        if ($excludeId !== null) {
            $sql .= " AND train_id != :id";
        }

        $stmt = $this->db->prepare($sql);

        $params = ['number' => $number];

        if ($excludeId !== null) {
        $params['id'] = $excludeId;
        }

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    // Check whether a train has pending or confirmed bookings
    public function hasActiveBookings(int $id): bool
    {
        $sql = "SELECT COUNT(*)
                FROM bookings b
                INNER JOIN schedules s
                    ON b.schedule_id = s.schedule_id
                WHERE s.train_id = :id
                AND b.booking_status IN ('pending', 'confirmed')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}