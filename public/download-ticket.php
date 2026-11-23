<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'passenger') {
    header('Location: test-login.php');
    exit;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$ticket = null;
if ($bookingId) {
    $controller = new BookingController($pdo);
    $ticket = $controller->eticket($bookingId, (int)$_SESSION['user_id']);
}

// Same response whether the ticket is missing or belongs to someone else
if ($ticket === null) {
    http_response_code(403);
    exit('Access denied or ticket not available.');
}

// A ticket under cancellation cannot be downloaded
if (!$ticket['qr_active']) {
    http_response_code(403);
    exit('This ticket is no longer valid and cannot be downloaded.');
}

$verifyUrl = 'http://' . $_SERVER['HTTP_HOST']
    . dirname($_SERVER['SCRIPT_NAME']) . '/verify.php?t=' . $ticket['verification_token'];

// Force a file download; the filename is built from a safe reference
$safeRef = preg_replace('/[^A-Za-z0-9_-]/', '', $ticket['booking_reference']);
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="ticket-' . $safeRef . '.html"');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Ticket <?= e($ticket['booking_reference']) ?></title>
    <style>
        .ticket { max-width: 700px; border: 2px solid #333; padding: 20px; margin: 20px auto; font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 6px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
<div class="ticket">
    <h1>CRS E-Ticket</h1>
    <p>Booking Reference: <strong><?= e($ticket['booking_reference']) ?></strong></p>

    <table>
        <tr><th>Train</th><td><?= e($ticket['train_number']) ?> - <?= e($ticket['train_name']) ?></td></tr>
        <tr><th>From</th><td><?= e($ticket['origin_name']) ?></td></tr>
        <tr><th>To</th><td><?= e($ticket['destination_name']) ?></td></tr>
        <tr><th>Departure</th><td><?= e(date('d M Y, H:i', strtotime($ticket['departure_datetime']))) ?></td></tr>
        <tr><th>Arrival</th><td><?= e(date('d M Y, H:i', strtotime($ticket['arrival_datetime']))) ?></td></tr>
    </table>

    <h3>Passengers</h3>
    <table>
        <tr><th>Name</th><th>Seat</th></tr>
        <?php foreach ($ticket['passengers'] as $p): ?>
            <tr><td><?= e($p['passenger_name']) ?></td><td><?= e($p['seat_number']) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Verification QR</h3>
    <div id="qrcode"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($verifyUrl) ?>,
        width: 160,
        height: 160
    });
</script>
</body>
</html>