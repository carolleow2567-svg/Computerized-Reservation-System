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
}