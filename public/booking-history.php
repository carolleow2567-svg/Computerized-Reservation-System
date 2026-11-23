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
$counts = $data['counts'];

// Build a tab link that keeps the current search and sort
function tabUrl(string $tab, array $filters): string
{
    return 'booking-history.php?' . http_build_query([
        'tab' => $tab,
        'keyword' => $filters['keyword'],
        'sort' => $filters['sort'],
    ]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | RailMY</title>
    <link rel="stylesheet" href="assets/css/bookings.css">
</head>
<body>

<?php require __DIR__ . '/../app/views/bookings/nav.php'; ?>

<div class="container">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message"><?= e($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="page-header">
        <h1>My bookings</h1>
        <a href="#" class="btn btn-orange">Book a new trip</a>
    </div>

    <div class="tabs">
        <a href="<?= e(tabUrl('upcoming', $filters)) ?>" class="<?= $filters['tab'] === 'upcoming' ? 'active' : '' ?>">Upcoming (<?= (int)$counts['upcoming'] ?>)</a>
        <a href="<?= e(tabUrl('completed', $filters)) ?>" class="<?= $filters['tab'] === 'completed' ? 'active' : '' ?>">Completed (<?= (int)$counts['completed'] ?>)</a>
        <a href="<?= e(tabUrl('cancelled', $filters)) ?>" class="<?= $filters['tab'] === 'cancelled' ? 'active' : '' ?>">Cancelled (<?= (int)$counts['cancelled'] ?>)</a>
    </div>

    <form method="GET" action="booking-history.php" class="filter-bar">
        <input type="hidden" name="tab" value="<?= e($filters['tab']) ?>">

        <div>
            <label for="keyword">Search</label>
            <input type="text" id="keyword" name="keyword" maxlength="40"
                   placeholder="Reference, train or station"
                   value="<?= e($filters['keyword']) ?>">
        </div>

        <div>
            <label for="sort">Sort by</label>
            <select id="sort" name="sort">
                <option value="earliest" <?= $filters['sort'] === 'earliest' ? 'selected' : '' ?>>Journey date (earliest)</option>
                <option value="latest" <?= $filters['sort'] === 'latest' ? 'selected' : '' ?>>Journey date (latest)</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-navy">Search</button>
            <a href="booking-history.php?tab=<?= e($filters['tab']) ?>" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <?php if (empty($bookings)): ?>
        <div class="empty-box">No bookings found.</div>
    <?php else: ?>
        <?php foreach ($bookings as $b): ?>
            <?php
                $isCancelled = $b['booking_status'] === 'cancelled'
                    || in_array($b['cancel_status'], ['approved', 'refunded'], true);
                $chipClass = $isCancelled ? 'cancelled' : $b['booking_status'];
                $chipText = $isCancelled ? 'Cancelled' : ucfirst($b['booking_status']);
            ?>
            <div class="booking-card">
                <div>
                    <div class="booking-meta">
                        <span class="chip <?= e($chipClass) ?>"><?= e($chipText) ?></span>
                        <span>Ref <?= e($b['booking_reference']) ?></span>
                    </div>
                    <h2><?= e($b['origin_name']) ?> &rarr; <?= e($b['destination_name']) ?></h2>
                    <div class="sub">
                        <?= e(date('D, d M Y, H:i', strtotime($b['departure_datetime']))) ?>
                        &rarr; <?= e(date('H:i', strtotime($b['arrival_datetime']))) ?>
                    </div>
                    <div class="sub"><?= e($b['train_number']) ?> &middot; <?= e($b['train_name']) ?></div>

                    <?php if ($b['cancel_status'] === 'pending'): ?>
                        <div class="note">Cancellation request pending approval.</div>
                    <?php elseif ($b['cancel_status'] === 'rejected'): ?>
                        <div class="note">Your last cancellation request was rejected.</div>
                    <?php elseif ($b['cancel_status'] === 'refunded'): ?>
                        <div class="note">Cancelled and refunded.</div>
                    <?php elseif ($b['cancel_closed']): ?>
                        <div class="note">Cancellation closed: departs in less than 48 hours.</div>
                    <?php endif; ?>
                </div>

                <div class="booking-side">
                    <div class="fare">
                        <small>Total fare</small>
                        <strong>RM <?= e(number_format((float)$b['total_amount'], 2)) ?></strong>
                    </div>

                    <?php if ($b['booking_status'] === 'confirmed'): ?>
                        <a href="eticket.php?id=<?= e($b['booking_id']) ?>" class="btn btn-navy">View e-ticket</a>
                    <?php endif; ?>

                    <?php if ($b['can_cancel']): ?>
                        <form method="POST" action="cancel-booking.php"
                              onsubmit="return confirm('Request cancellation for this booking?');">
                            <input type="hidden" name="booking_id" value="<?= e($b['booking_id']) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="btn btn-danger">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p class="policy">
        Cancellation policy: free cancellation up to 48 hours before departure.
        Refunds are processed by the administrator after approval.
    </p>

</div>

</body>
</html>