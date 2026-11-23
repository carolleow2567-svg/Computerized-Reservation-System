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

$dep = strtotime($ticket['departure_datetime']);
$arr = strtotime($ticket['arrival_datetime']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket <?= e($ticket['booking_reference']) ?> | RailMY</title>
    <link rel="stylesheet" href="assets/css/bookings.css">
</head>
<body>

<?php require __DIR__ . '/../app/views/bookings/nav.php'; ?>

<div class="container">

    <div class="page-header">
        <h1>E-Ticket</h1>
        <div class="ticket-actions no-print">
            <?php if ($ticket['qr_active']): ?>
                <a href="download-ticket.php?id=<?= e($ticket['booking_id']) ?>" class="btn btn-outline">Download ticket</a>
                <a href="download-ticket.php?id=<?= e($ticket['booking_id']) ?>&amp;print=1"
                   class="btn btn-outline">Print ticket</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$ticket['qr_active']): ?>
        <div class="invalid-box">
            Cancellation <?= e($ticket['cancel_status']) ?>.
            This ticket is no longer valid for travel.
        </div>
    <?php endif; ?>

    <div class="ticket">
        <div class="ticket-main">
            <div class="ticket-head">
                <span>RailMY &middot; <?= e($ticket['train_type']) ?> E-Ticket</span>
                <span class="chip <?= $ticket['qr_active'] ? 'confirmed' : 'pending' ?>">
                    Booking: <?= $ticket['qr_active'] ? 'Confirmed' : 'Cancellation ' . e($ticket['cancel_status']) ?>
                </span>
            </div>

            <div class="ticket-body">
                <div class="route">
                    <span class="city"><?= e($ticket['origin_name']) ?></span>
                    <span class="line"></span>
                    <span class="city"><?= e($ticket['destination_name']) ?></span>
                </div>
                <div class="route-sub">
                    <span>Departs <?= e(date('H:i', $dep)) ?></span>
                    <span>Arrives <?= e(date('H:i', $arr)) ?></span>
                </div>

                <dl class="details">
                    <div><dt>Booking reference</dt><dd><?= e($ticket['booking_reference']) ?></dd></div>
                    <div><dt>Travel date</dt><dd><?= e(date('d M Y', $dep)) ?></dd></div>
                    <div><dt>Train</dt><dd><?= e($ticket['train_number']) ?> &middot; <?= e($ticket['train_name']) ?></dd></div>
                    <div><dt>Amount paid</dt><dd>RM <?= e(number_format((float)$ticket['total_amount'], 2)) ?></dd></div>
                    <div><dt>Booked on</dt><dd><?= e(date('d M Y', strtotime($ticket['booking_date']))) ?></dd></div>
                </dl>

                <table>
                    <thead>
                        <tr><th>Passenger</th><th>Seat</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticket['passengers'] as $p): ?>
                            <tr>
                                <td><?= e($p['passenger_name']) ?></td>
                                <td><?= e($p['seat_number']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="ticket-note">
                    Please arrive 30 minutes before departure and present this QR code with a valid ID.
                </p>
            </div>
        </div>

        <div class="ticket-qr">
            <?php if ($ticket['qr_active']): ?>
                <div id="qrcode"></div>
                <div class="qr-ref"><?= e($ticket['booking_reference']) ?></div>
                <span class="chip confirmed">Confirmed</span>
                <div class="qr-note">Scan at the gate. This code contains no personal information.</div>
            <?php else: ?>
                <span class="chip cancelled">QR disabled</span>
                <div class="qr-note">This ticket cannot be used for travel.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ticket-footer-btns no-print">
        <a href="booking-history.php" class="btn btn-navy">Back to booking history</a>
        <a href="index.php" class="btn btn-orange">Return to home</a>
    </div>

</div>

<?php if ($ticket['qr_active']): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($verifyUrl) ?>,
        width: 180,
        height: 180,
        colorDark: '#0b2a5b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
<?php endif; ?>

</body>
</html>