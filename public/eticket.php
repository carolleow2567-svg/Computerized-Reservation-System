<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

// Authentication check
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'passenger') {
    header('Location: test-login.php');
    exit;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Validate the ID: must be a positive integer
$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$ticket = null;
if ($bookingId) {
    $controller = new BookingController($pdo);
    $ticket = $controller->eticket($bookingId, (int)$_SESSION['user_id']);
}

// Not found and not-yours give the SAME response (no information leak)
if ($ticket === null) {
    http_response_code(403);
    exit('Access denied or ticket not available.');
}

// The QR contains only the random token, no personal data
$verifyUrl = 'http://' . $_SERVER['HTTP_HOST']
    . dirname($_SERVER['SCRIPT_NAME']) . '/verify.php?t=' . $ticket['verification_token'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket <?= e($ticket['booking_reference']) ?> | CRS</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        .ticket { max-width: 700px; border: 2px solid #333; padding: 20px; margin: 20px auto; }
        .ticket table { width: 100%; border-collapse: collapse; }
        .ticket th, .ticket td { text-align: left; padding: 6px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="no-print" style="text-align:center;">
    <a href="booking-history.php">&larr; Back to My Bookings</a>
    |
    <button onclick="window.print()">Print</button>
    <a href="download-ticket.php?id=<?= e($ticket['booking_id']) ?>">Download</a>
</div>

<div class="ticket">
    <h1>RailMY E-Ticket</h1>
    <p>Booking Reference: <strong><?= e($ticket['booking_reference']) ?></strong></p>
    <p>Status: <strong><?= e(ucfirst($ticket['booking_status'])) ?></strong></p>

    <?php if (!$ticket['qr_active']): ?>
        <p style="color:red;">
            <strong>
                Cancellation <?= e($ticket['cancel_status']) ?>.
                This ticket is no longer valid for travel.
            </strong>
        </p>
    <?php endif; ?>

    <table>
        <tr><th>Train</th><td><?= e($ticket['train_number']) ?> - <?= e($ticket['train_name']) ?> (<?= e($ticket['train_type']) ?>)</td></tr>
        <tr><th>From</th><td><?= e($ticket['origin_name']) ?></td></tr>
        <tr><th>To</th><td><?= e($ticket['destination_name']) ?></td></tr>
        <tr><th>Departure</th><td><?= e(date('d M Y, H:i', strtotime($ticket['departure_datetime']))) ?></td></tr>
        <tr><th>Arrival</th><td><?= e(date('d M Y, H:i', strtotime($ticket['arrival_datetime']))) ?></td></tr>
        <tr><th>Total Paid</th><td>RM <?= e(number_format((float)$ticket['total_amount'], 2)) ?></td></tr>
    </table>

    <h3>Passengers</h3>
    <table>
        <tr><th>Name</th><th>Seat</th></tr>
        <?php foreach ($ticket['passengers'] as $p): ?>
            <tr>
                <td><?= e($p['passenger_name']) ?></td>
                <td><?= e($p['seat_number']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($ticket['qr_active']): ?>
        <h3>Verification QR</h3>
        <div id="qrcode"></div>
        <p><small>Scan at the station gate. This code contains no personal information.</small></p>
    <?php endif; ?>
</div>

<?php if ($ticket['qr_active']): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($verifyUrl) ?>,
        width: 160,
        height: 160
    });
</script>
<?php endif; ?>

</body>
</html>