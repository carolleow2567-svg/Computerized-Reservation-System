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

    // Returns ticket data, or null if the booking does not exist OR is not the user's.
    // Both cases look identical to the caller so booking IDs cannot be probed.
    public function eticket(int $bookingId, int $userId): ?array
    {
        $ticket = $this->booking->getTicketForUser($bookingId, $userId);

        if ($ticket === null || $ticket['booking_status'] !== 'confirmed') {
            return null;
        }

        $ticket['verification_token'] =
            $ticket['verification_token'] ?: $this->booking->ensureToken($bookingId, $userId);

            // The QR is valid only if there is no active cancellation request
            $ticket['qr_active'] = !in_array(
                $ticket['cancel_status'],
                ['pending', 'approved', 'refunded'],
                true
            );

        $ticket['passengers'] = $this->booking->getPassengers($bookingId);

        return $ticket;
    }

    public function cancel(int $bookingId, int $userId, string $reason): ?string
    {
        $reason = mb_substr(trim($reason), 0, 255);

        return $this->booking->requestCancellation($bookingId, $userId, $reason);
    }
    
        // Gate verification: returns a result with a reason, never personal data.
    public function verify(string $token): array
    {
        // A valid token is exactly 64 hex characters
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return ['valid' => false, 'reason' => 'Invalid ticket.'];
        }

        $row = $this->booking->getByToken($token);

        if ($row === null) {
            return ['valid' => false, 'reason' => 'Invalid ticket.'];
        }

        if ($row['booking_status'] !== 'confirmed') {
            return ['valid' => false, 'reason' => 'Booking is not confirmed.', 'info' => $row];
        }

        if (in_array($row['cancel_status'], ['pending', 'approved', 'refunded'], true)) {
            return ['valid' => false, 'reason' => 'Booking has been cancelled or is being cancelled.', 'info' => $row];
        }

        if (strtotime($row['arrival_datetime']) < time()) {
            return ['valid' => false, 'reason' => 'This trip has already finished.', 'info' => $row];
        }

        return ['valid' => true, 'reason' => 'Ticket is valid.', 'info' => $row];
    }

}