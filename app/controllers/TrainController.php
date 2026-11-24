<?php

require_once __DIR__ . '/../models/Train.php';

class TrainController
{
    private Train $trainModel;

    public function __construct(PDO $pdo)
    {
        $this->trainModel = new Train($pdo);
    }

    // Display all trains
    public function index(): array
    {
        return $this->trainModel->getAll();
    }

    // Extra read-only details shown on the train list (route, times, classes)
    public function listDetails(): array
    {
        return [
            'schedules' => $this->trainModel->getScheduleSummaries(),
            'classes'   => $this->trainModel->getClassSummaries(),
            'options'   => $this->trainModel->getClassOptions(),
        ];
    }

    // Display one train
    public function show(int $id): ?array
    {
        return $this->trainModel->getById($id);
    }

    // Create a train
    public function create(array $data): bool
    {
        return $this->trainModel->create($data);
    }

    // Update a train
    public function update(int $id, array $data): bool
    {
        return $this->trainModel->update($id, $data);
    }

    // Deactivate a train
    public function deactivate(int $id): bool
    {
        return $this->trainModel->deactivate($id);
    }

    // Check duplicate train number, excluding the current train
    public function existsByTrainNumber(string $number, ?int $excludeId = null): bool
    {
        return $this->trainModel->existsByTrainNumber($number, $excludeId);
    }

    // Check if a train has active bookings
    public function hasActiveBookings(int $id): bool
    {
        return $this->trainModel->hasActiveBookings($id);
    }
}