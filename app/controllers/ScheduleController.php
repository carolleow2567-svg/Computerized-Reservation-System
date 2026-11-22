<?php

require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController
{
    private Schedule $scheduleModel;

    public function __construct(PDO $pdo)
    {
        $this->scheduleModel = new Schedule($pdo);
    }

    public function index(): array
    {
        return $this->scheduleModel->getAll();
    }

    public function getActiveTrains(): array
    {
        return $this->scheduleModel->getActiveTrains();
    }

    public function getActiveStations(): array
    {
        return $this->scheduleModel->getActiveStations();
    }

    public function create(array $data): bool
    {
        return $this->scheduleModel->create($data);
    }

    public function isActiveTrain(int $id): bool
    {
        return $this->scheduleModel->isActiveTrain($id);
    }

    public function isActiveStation(int $id): bool
    {
        return $this->scheduleModel->isActiveStation($id);
    }

    public function show(int $id): ?array
    {
        return $this->scheduleModel->getById($id);
    }

    public function update(int $id, array $data): bool
    {
        return $this->scheduleModel->update($id, $data);
    }
}