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
        $booking = $this->bookingModel->getById($id);

        if (!$booking) {return false;}

        $currentStatus = $booking['booking_status'];

        // Prevent changes to cancelled bookings
        if ($currentStatus === 'cancelled') {
            return false;
        }

        // Prevent invalid status transitions
        if ($currentStatus === 'pending') {
            if (!in_array($status, ['confirmed', 'cancelled'], true)) {
                return false;
            }
        } elseif ($currentStatus === 'confirmed') {
            if ($status !== 'cancelled') {
                return false;
            }
        } else {return false;}

        // Prevent confirmation of cancelled schedules
        if ($status === 'confirmed'
            && $booking['schedule_status'] === 'cancelled') {
            return false;
        }

        return $this->bookingModel->updateStatus($id, $status);
    }
}