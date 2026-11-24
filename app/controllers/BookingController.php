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
    public function updateStatus(
        int $id,
        string $status,
        ?string $reason = null
    ): bool {

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

        // A cancellation reason is mandatory.
        if ($status === 'cancelled') {
            $reason = trim((string) $reason);

            if ($reason === '' || strlen($reason) > 255) {
                return false;
            }
        }

        // Prevent confirmation of cancelled schedules.
        if (
            $status === 'confirmed' &&
            $booking['schedule_status'] === 'cancelled'
        ) {
            return false;
        }

        return $this->bookingModel->updateStatus(
            $id,
            $status,
            $reason
        );
    }

    public function updatePaymentStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['paid', 'failed'], true)) {
            return false;
        }

        $booking = $this->bookingModel->getById($id);

        if (!$booking) {
            return false;
        }

        return $this->bookingModel->updatePaymentStatus($id, $status);
    }

    public function updateRefund(
        int $id,
        string $status,
        float $amount
    ): bool {
        if (!in_array($status, ['pending', 'processed', 'rejected'], true)) {
            return false;
        }

        $booking = $this->bookingModel->getById($id);

        if (!$booking) {
            return false;
        }

        if ($status === 'pending') {
            if (
                $amount <= 0 ||
                $amount > (float) $booking['total_amount']
            ) {
                return false;
            }
        }

        return $this->bookingModel->updateRefund($id, $status, $amount);
    }
}