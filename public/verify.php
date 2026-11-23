<?php

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$controller = new BookingController($pdo);
$result = $controller->verify((string)($_GET['t'] ?? ''));
$info = $result['info'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Verification | CRS</title>
</head>
<body>

<h1>Ticket Verification</h1>

<?php if ($result['valid']): ?>
    <h2 style="color:green;">VALID</h2>
<?php else: ?>
    <h2 style="color:red;">NOT VALID</h2>
<?php endif; ?>

<p><?= e($result['reason']) ?></p>

<?php if ($info): ?>
    <table>
        <tr><th>Reference</th><td><?= e($info['booking_reference']) ?></td></tr>
        <tr><th>Train</th><td><?= e($info['train_number']) ?></td></tr>
        <tr><th>Route</th><td><?= e($info['origin_name']) ?> &rarr; <?= e($info['destination_name']) ?></td></tr>
        <tr><th>Departure</th><td><?= e(date('d M Y, H:i', strtotime($info['departure_datetime']))) ?></td></tr>
    </table>
<?php endif; ?>

</body>
</html>