<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

$controller = new BookingController($pdo);
$bookings = $controller->index();

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

if ($search !== '') {
    $bookings = array_filter($bookings, function ($booking) use ($search) {
        return stripos($booking['booking_reference'], $search) !== false
            || stripos($booking['full_name'], $search) !== false
            || stripos($booking['email'], $search) !== false;
    });
}

if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled'], true)) {
    $bookings = array_filter($bookings, function ($booking) use ($statusFilter) {
        return $booking['booking_status'] === $statusFilter;
    });
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css">
</head>

<body>

<div class="container">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message">
            <?= e($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message">
            <?= e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <header class="page-header">
        <div>
            <h1>Manage Bookings</h1>
            <p>View and manage passenger reservations.</p>
        </div>
    </header>

    <section class="table-section">

        <div class="table-heading">
            <h2>Booking Records</h2>
            <span>Total Bookings: <?= count($bookings) ?></span>
        </div>

        <form method="GET" class="filter-form">

            <input type="text"
                   name="search"
                   placeholder="Search reference, passenger or email"
                   value="<?= e($search) ?>">

            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <button type="submit" class="btn-primary">Filter</button>
            <a href="bookings.php" class="btn-edit">Reset</a>

        </form>

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>Booking Ref.</th>
                        <th>Passenger</th>
                        <th>Train</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Amount (RM)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No booking records found.
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= e($booking['booking_reference']) ?></td>

                            <td>
                                <?= e($booking['full_name']) ?><br>
                                <small><?= e($booking['email']) ?></small>
                            </td>

                            <td>
                                <?= e($booking['train_number']) ?><br>
                                <?= e($booking['train_name']) ?>
                            </td>

                            <td>
                                <?= e($booking['origin_name']) ?>
                                →
                                <?= e($booking['destination_name']) ?>
                            </td>

                            <td><?= e($booking['departure_datetime']) ?></td>

                            <td><?= e(number_format((float)$booking['total_amount'], 2)) ?></td>

                            <td>
                                <span class="schedule-status <?= e($booking['booking_status']) ?>">
                                    <?= e(ucfirst($booking['booking_status'])) ?>
                                </span>
                            </td>

                            <td>
                                <a href="booking-edit.php?id=<?= e($booking['booking_id']) ?>"
                                   class="btn-edit">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </section>

</div>

</body>
</html>