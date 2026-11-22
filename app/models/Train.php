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