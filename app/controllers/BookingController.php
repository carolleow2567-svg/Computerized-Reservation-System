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
        $allowedTabs = ['upcoming', 'completed', 'cancelled'];
        $allowedSort = ['earliest', 'latest'];

        $filters = [
            'keyword' => mb_substr(trim((string)($input['keyword'] ?? '')), 0, 40),
            'tab'     => in_array($input['tab'] ?? '', $allowedTabs, true) ? $input['tab'] : 'upcoming',
            'sort'    => in_array($input['sort'] ?? '', $allowedSort, true) ? $input['sort'] : 'earliest',
        ];

        $all = $this->booking->getByUser($userId, [
            'keyword' => $filters['keyword'],
            'status'  => '',
            'period'  => '',
        ]);

        $groups = ['upcoming' => [], 'completed' => [], 'cancelled' => []];
        $now = time();

        foreach ($all as $b) {
            $departure = strtotime($b['departure_datetime']);
            $cancelled = $b['booking_status'] === 'cancelled'
                || in_array($b['cancel_status'], ['approved', 'refunded'], true);

            // Cancel allowed only: confirmed, no active request, and > 48h before departure
            $b['can_cancel'] =
                $b['booking_status'] === 'confirmed'
                && !$cancelled
                && $departure > $now + 48 * 3600
                && ($b['cancel_status'] === null || $b['cancel_status'] === 'rejected');

            $b['cancel_closed'] =
                $b['booking_status'] === 'confirmed'
                && !$cancelled
                && $departure > $now
                && $departure <= $now + 48 * 3600
                && ($b['cancel_status'] === null || $b['cancel_status'] === 'rejected');

            if ($cancelled) {
                $groups['cancelled'][] = $b;
            } elseif ($departure < $now) {
                $groups['completed'][] = $b;
            } else {
                $groups['upcoming'][] = $b;
            }
        }

        $counts = [
            'upcoming'  => count($groups['upcoming']),
            'completed' => count($groups['completed']),
            'cancelled' => count($groups['cancelled']),
        ];

        $bookings = $groups[$filters['tab']];

        usort($bookings, function ($a, $b) use ($filters) {
            $cmp = strtotime($a['departure_datetime']) <=> strtotime($b['departure_datetime']);
            return $filters['sort'] === 'latest' ? -$cmp : $cmp;
        });

        return ['bookings' => $bookings, 'filters' => $filters, 'counts' => $counts];
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