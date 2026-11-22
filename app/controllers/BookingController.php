<?php

require_once __DIR__ . '/../models/Booking.php';

class BookingController
{
    private Booking $booking;

    public function __construct(PDO $pdo)
    {
        $this->booking = new Booking($pdo);
    }

    public function history(int $userId, array $input): array
    {
        // Server-side validation: only whitelisted values are accepted
        $allowedStatus = ['pending', 'confirmed', 'cancelled'];
        $allowedPeriod = ['upcoming', 'past'];

        $filters = [
            'keyword' => mb_substr(trim((string)($input['keyword'] ?? '')), 0, 20),
            'status'  => in_array($input['status'] ?? '', $allowedStatus, true) ? $input['status'] : '',
            'period'  => in_array($input['period'] ?? '', $allowedPeriod, true) ? $input['period'] : '',
        ];

        $bookings = $this->booking->getByUser($userId, $filters);

        // Decide in PHP whether the Cancel button should be shown
        foreach ($bookings as &$b) {
            $b['can_cancel'] =
                $b['booking_status'] === 'confirmed'
                && strtotime($b['departure_datetime']) > time()
                && ($b['cancel_status'] === null || $b['cancel_status'] === 'rejected');
        }
        unset($b);

        return ['bookings' => $bookings, 'filters' => $filters];
    }
}