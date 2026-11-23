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

// A ticket under cancellation cannot be downloaded or printed
if (!$ticket['qr_active']) {
    http_response_code(403);
    exit('This ticket is no longer valid and cannot be downloaded.');
}

$verifyUrl = 'http://' . $_SERVER['HTTP_HOST']
    . dirname($_SERVER['SCRIPT_NAME']) . '/verify.php?t=' . $ticket['verification_token'];

// print=1 : show the ticket in the browser and open the print dialog
// otherwise: force a file download
$printMode = isset($_GET['print']);
$safeRef = preg_replace('/[^A-Za-z0-9_-]/', '', $ticket['booking_reference']);

header('Content-Type: text/html; charset=UTF-8');
if (!$printMode) {
    header('Content-Disposition: attachment; filename="ticket-' . $safeRef . '.html"');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket <?= e($ticket['booking_reference']) ?> | RailMY</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap');

        /* Styles are inline because this file is opened outside the website */
        :root {
            --navy: #0b2a5b;
            --orange: #f5a31a;
            --border: #d9dee8;
            --muted: #5d6b82;
            --green-bg: #dcf5e6;
            --green: #17703c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Figtree', 'Segoe UI', Arial, sans-serif;
            background: #f4f3ee;
            color: var(--navy);
            padding: 32px 16px;
        }
        .toolbar {
            max-width: 700px; margin: 0 auto 14px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .toolbar a { color: var(--navy); font-size: 14px; font-weight: 600; text-decoration: none; }
        .toolbar a:hover { text-decoration: underline; }
        .toolbar button {
            background: #fff; color: var(--navy); border: 1px solid var(--border);
            border-radius: 8px; padding: 10px 18px; cursor: pointer;
            font-family: inherit; font-size: 14px; font-weight: 700;
        }
        .toolbar button:hover { background: #f1f4fa; }
        .ticket {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-top: 6px solid var(--navy);
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(11,42,91,.08);
        }
        h1 { font-size: 26px; margin-bottom: 4px; }
        .brand-line { height: 3px; width: 100%; background: var(--orange); margin: 10px 0 18px; }
        .ref { font-size: 14px; color: var(--muted); margin-bottom: 6px; }
        .ref strong { color: var(--navy); }
        .chip {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; background: var(--green-bg); color: var(--green);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { text-align: left; padding: 10px 6px; border-bottom: 1px solid var(--border); font-size: 14px; }
        th { color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; width: 35%; }
        td { font-weight: 600; }
        h3 { font-size: 13px; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); margin-top: 24px; }
        .passengers th { width: auto; }
        .qr-box { margin-top: 12px; }
        .qr-note { font-size: 12px; color: var(--muted); margin-top: 8px; }

        @media print {
            @page { margin: 12mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .ticket { box-shadow: none; }
        }
    </style>
</head>
<body>

<?php if ($printMode): ?>
    <div class="toolbar no-print">
        <a href="eticket.php?id=<?= e($ticket['booking_id']) ?>">&larr; Back to E-Ticket</a>
        <button type="button" onclick="window.print()">Print ticket</button>
    </div>
<?php endif; ?>

<div class="ticket">
    <h1>RailMY E-Ticket</h1>
    <div class="brand-line"></div>

    <p class="ref">Booking Reference: <strong><?= e($ticket['booking_reference']) ?></strong></p>
    <span class="chip">Confirmed</span>

    <table>
        <tr><th>Train</th><td><?= e($ticket['train_number']) ?> - <?= e($ticket['train_name']) ?> (<?= e($ticket['train_type']) ?>)</td></tr>
        <tr><th>From</th><td><?= e($ticket['origin_name']) ?></td></tr>
        <tr><th>To</th><td><?= e($ticket['destination_name']) ?></td></tr>
        <tr><th>Departure</th><td><?= e(date('d M Y, H:i', strtotime($ticket['departure_datetime']))) ?></td></tr>
        <tr><th>Arrival</th><td><?= e(date('d M Y, H:i', strtotime($ticket['arrival_datetime']))) ?></td></tr>
        <tr><th>Total Paid</th><td>RM <?= e(number_format((float)$ticket['total_amount'], 2)) ?></td></tr>
    </table>

    <h3>Passengers</h3>
    <table class="passengers">
        <tr><th>Name</th><th>Seat</th></tr>
        <?php foreach ($ticket['passengers'] as $p): ?>
            <tr>
                <td><?= e($p['passenger_name']) ?></td>
                <td><?= e($p['seat_number']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Verification QR</h3>
    <div class="qr-box" id="qrcode"></div>
    <p class="qr-note">Scan at the station gate. This code contains no personal information.</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: <?= json_encode($verifyUrl) ?>,
        width: 140,
        height: 140,
        colorDark: '#0b2a5b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });

    <?php if ($printMode): ?>
    // Open the print dialog once the page and QR are ready
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
    <?php endif; ?>
</script>

</body>
</html>