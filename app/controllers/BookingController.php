<?php

require_once __DIR__ . '/../models/Booking.php';

class BookingController
{
    private Booking $bookingModel;

    public function __construct(PDO $pdo)
    {
        $this->bookingModel = new Booking($pdo);
    }

    // Retrieve all bookings.
    public function index(): array
    {
        return $this->bookingModel->getAll();
    }

    // Retrieve one booking with passenger and seat details.
    public function show(int $id): ?array
    {
        return $this->bookingModel->getById($id);
    }

    // Update booking status with transition safeguards.
    public function updateStatus(int $id, string $status): bool
    {
        $booking = $this->bookingModel->getById($id);

        if (!$booking) {
            return false;
        }

        $currentStatus = $booking['booking_status'];

        // Prevent changes to cancelled bookings.
        if ($currentStatus === 'cancelled') {
            return false;
        }

        // Pending bookings can be confirmed or cancelled.
        if ($currentStatus === 'pending') {

            if (!in_array($status, ['confirmed', 'cancelled'], true)) {
                return false;
            }

        // Confirmed bookings can only be cancelled.
        } elseif ($currentStatus === 'confirmed') {

            if ($status !== 'cancelled') {
                return false;
            }

        } else {
            return false;
        }

        // Prevent confirmation of cancelled schedules.
        if (
            $status === 'confirmed' &&
            $booking['schedule_status'] === 'cancelled'
        ) {
            return false;
        }

        return $this->bookingModel->updateStatus($id, $status);
    }
}