<?php

require_once __DIR__ . '/../models/Booking.php';

class BookingController
{
    private Booking $bookingModel;

    public function __construct(PDO $pdo)
    {
        $this->bookingModel = new Booking($pdo);
    }

    public function index(): array
    {
        return $this->bookingModel->getAll();
    }

    public function show(int $id): ?array
    {
        return $this->bookingModel->getById($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->bookingModel->updateStatus($id, $status);
    }
}