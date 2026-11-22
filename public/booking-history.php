<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

// Session authentication: only logged-in passengers may enter.
// TODO: change test-login.php to M1's real login page when it is ready.
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'passenger') {
    header('Location: test-login.php');
    exit;
}

// Same CSRF token approach as M7's pages
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$controller = new BookingController($pdo);
$data = $controller->history((int)$_SESSION['user_id'], $_GET);
$bookings = $data['bookings'];
$filters = $data['filters'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | CRS</title>
</head>
<body>

<div class="container">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message"><?= e($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <header class="page-header">
        <h1>My Bookings</h1>
        <p>View your tickets and manage your bookings.</p>
    </header>

    <!-- Search and filter -->
    <form method="GET" action="booking-history.php">
        <input type="text" name="keyword" placeholder="Booking reference"
               value="<?= e($filters['keyword']) ?>">

        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'confirmed', 'cancelled'] as $s): ?>
                <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                    <?= e(ucfirst($s)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="period">
            <option value="">All trips</option>
            <option value="upcoming" <?= $filters['period'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
            <option value="past" <?= $filters['period'] === 'past' ? 'selected' : '' ?>>Past</option>
        </select>

        <button type="submit">Search</button>
        <a href="booking-history.php">Reset</a>
    </form>

    <section class="table-section">
        <div class="table-heading">
            <h2>Booking Records</h2>
            <span>Total: <?= count($bookings) ?></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Train</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Amount (RM)</th>
                    <th>Status</th>
                    <th>Cancellation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="8" class="empty">No bookings found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= e($b['booking_reference']) ?></td>
                        <td><?= e($b['train_number']) ?> - <?= e($b['train_name']) ?></td>
                        <td><?= e($b['origin_name']) ?> &rarr; <?= e($b['destination_name']) ?></td>
                        <td><?= e(date('d M Y, H:i', strtotime($b['departure_datetime']))) ?></td>
                        <td><?= e(number_format((float)$b['total_amount'], 2)) ?></td>
                        <td>
                            <span class="status <?= e($b['booking_status']) ?>">
                                <?= e(ucfirst($b['booking_status'])) ?>
                            </span>
                        </td>
                        <td><?= $b['cancel_status'] ? e(ucfirst($b['cancel_status'])) : '-' ?></td>
                        <td>
                            <?php if ($b['booking_status'] === 'confirmed'): ?>
                                <a href="eticket.php?id=<?= e($b['booking_id']) ?>">View Ticket</a>
                            <?php endif; ?>

                            <?php if ($b['can_cancel']): ?>
                                <form method="POST" action="cancel-booking.php" style="display:inline;"
                                      onsubmit="return confirm('Request cancellation for this booking?');">
                                    <input type="hidden" name="booking_id" value="<?= e($b['booking_id']) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                    <button type="submit">Request Cancellation</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
    </section>

</div>

</body>
</html>