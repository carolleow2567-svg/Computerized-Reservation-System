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

// Mask IC number, showing only the final four characters.
function maskIC($ic): string
{
    $ic = (string)$ic;

    if (strlen($ic) <= 4) {
        return str_repeat('*', strlen($ic));
    }

    return str_repeat('*', strlen($ic) - 4) . substr($ic, -4);
}

$search = trim($_GET['search'] ?? '');

$statusFilter = $_GET['status'] ?? '';
$paymentFilter = $_GET['payment_status'] ?? '';
$dateFilter = $_GET['departure_date'] ?? '';

$allowedStatuses = ['pending', 'confirmed', 'cancelled'];
$allowedPaymentStatuses = ['unpaid', 'paid', 'failed', 'refunded'];

// Validate filter values.
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

if (!in_array($paymentFilter, $allowedPaymentStatuses, true)) {
    $paymentFilter = '';
}

// Accept only a valid YYYY-MM-DD date.
if (
    $dateFilter !== '' &&
    (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter) ||
        !DateTime::createFromFormat('!Y-m-d', $dateFilter)
    )
) {
    $dateFilter = '';
}

// Apply search and all filters together.
$bookings = array_filter($bookings, function ($booking) use (
    $search,
    $statusFilter,
    $paymentFilter,
    $dateFilter
) {
    $matchesSearch = true;
    $matchesStatus = true;
    $matchesPayment = true;
    $matchesDate = true;

    if ($search !== '') {
        $matchesSearch =
            stripos($booking['booking_reference'] ?? '', $search) !== false ||
            stripos($booking['full_name'] ?? '', $search) !== false ||
            stripos($booking['email'] ?? '', $search) !== false ||
            stripos($booking['passenger_details'] ?? '', $search) !== false ||
            stripos($booking['passenger_emails'] ?? '', $search) !== false ||
            stripos($booking['train_number'] ?? '', $search) !== false;
    }

    if ($statusFilter !== '') {
        $matchesStatus =
            $booking['booking_status'] === $statusFilter;
    }

    if ($paymentFilter !== '') {
        $matchesPayment =
            $booking['payment_status'] === $paymentFilter;
    }

    if ($dateFilter !== '') {
        $matchesDate =
            date('Y-m-d', strtotime($booking['departure_datetime'])) === $dateFilter;
    }

    return $matchesSearch &&
           $matchesStatus &&
           $matchesPayment &&
           $matchesDate;
});

$totalBookings = count($bookings);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Bookings | CRS Admin</title>

    <link rel="stylesheet" href="assets/css/trains.css?v=<?= (int) @filemtime(__DIR__ . '/assets/css/trains.css') ?>">

</head>

<body>

<?php
$adminActive = 'bookings';
$adminCrumb  = 'Manage Bookings';
require __DIR__ . '/../app/views/partials/admin_start.php';
?>

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

    <section class="table-section" aria-label="Search and filters">

        <form method="GET" class="filter-form">

            <div class="filter-field filter-field-grow">
                <label for="bk-search">Search</label>
                <input
                    id="bk-search"
                    type="text"
                    name="search"
                    placeholder="Reference, passenger, email or train"
                    value="<?= e($search) ?>"
                >
            </div>

            <div class="filter-field">
                <label for="bk-status">Booking status</label>
                <select id="bk-status" name="status">
                    <option value="">All Booking Statuses</option>
                    <?php foreach ($allowedStatuses as $status): ?>
                        <option value="<?= e($status) ?>"
                            <?= $status === $statusFilter ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="bk-payment">Payment status</label>
                <select id="bk-payment" name="payment_status">
                    <option value="">All Payment Statuses</option>
                    <?php foreach ($allowedPaymentStatuses as $payment): ?>
                        <option  value="<?= e($payment) ?>"
                            <?= $payment === $paymentFilter ? 'selected' : '' ?>>
                            <?= e(ucfirst($payment)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="bk-date">Departure date</label>
                <input
                    id="bk-date"
                    type="date"
                    name="departure_date"
                    value="<?= e($dateFilter) ?>">
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn-primary">Apply Filters</button>

                <a href="bookings.php" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="filter-count"><?= count($bookings) ?> booking(s) found</div>

    </section>

    <section class="table-section card-flush" aria-label="Booking records">

        <div class="table-heading">
            <h2>Booking Records</h2>
            <span>Total Bookings: <?= count($bookings) ?></span>
        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Booking Ref.</th>
                        <th>Passenger</th>
                        <th>Train</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Seats-Coach Class</th>
                        <th>Payment</th>
                        <th>Booking</th>
                        <th>Cancellation & Refund</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($bookings)): ?>

                    <tr>
                        <td colspan="10" class="empty">
                            No booking records found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($bookings as $booking): ?>

                        <?php
                        $dialogId = 'booking-dialog-' . (int)$booking['booking_id'];
                        ?>

                        <tr>

                            <td class="b">
                                <?= e($booking['booking_reference']) ?>
                            </td>

                            <td>
                                <?php if (!empty($booking['passenger_details'])): ?>

                                    <?= e($booking['passenger_details']) ?>

                                    <br>

                                    <small>
                                        <?= (int)$booking['passenger_count'] ?>
                                        passenger(s)
                                    </small>

                                <?php else: ?>

                                    <span>Passenger details not recorded</span>

                                <?php endif; ?>
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

                            <td>
                                <?= e($booking['departure_datetime']) ?>
                            </td>

                            <td>
                                <?= !empty($booking['seat_details']) ? e($booking['seat_details']) : 'Not assigned' ?>
                            </td>

                            <td>
                                <span class="schedule-status pay-<?= e($booking['payment_status']) ?>">
                                    <?= e(ucfirst($booking['payment_status'])) ?>
                                </span>
                            </td>

                            <td>
                                <span class="schedule-status <?= e($booking['booking_status']) ?>">
                                    <?= e(ucfirst($booking['booking_status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= e(number_format((float)$booking['refund_amount'], 2)) ?>
                            </td>

                            <td>
                                <div class="row-actions">
                                <button
                                    type="button"
                                    class="btn-view"
                                    onclick="document.getElementById('booking-dialog-<?= (int)$booking['booking_id'] ?>').showModal()">
                                    View
                                </button>

                                <a
                                    href="booking-edit.php?id=<?= e($booking['booking_id']) ?>"
                                    class="btn-edit">
                                    Edit
                                </a>

                                <?php if ($booking['booking_status'] !== 'cancelled'): ?>
                                    <a
                                        href="booking-cancel.php?id=<?= (int)$booking['booking_id'] ?>&action=cancel"
                                        class="btn-edit danger"
                                        onclick="return confirm('Are you sure you want to cancel this booking?');">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

<?php require __DIR__ . '/../app/views/partials/admin_end.php'; ?>


<?php if (!empty($bookings)): ?>

    <?php foreach ($bookings as $booking): ?>

        <?php
        $details = $controller->show((int)$booking['booking_id']);

        if (!$details) {
            continue;
        }

        $dialogId = 'booking-dialog-' . (int)$booking['booking_id'];
        ?>

        <dialog id="<?= e($dialogId) ?>" class="booking-dialog">

            <div class="booking-dialog-header">

                <h2>
                    Booking Details:
                    <?= e($details['booking_reference']) ?>
                </h2>

                <span class="schedule-status <?= e($details['booking_status']) ?>">
                    <?= e(ucfirst($details['booking_status'])) ?>
                </span>

            </div>

            <div class="booking-section">

                <h3>Booking Information</h3>

                <dl class="dl">

                    <dt>Booking Reference</dt>
<dd><?= e($details['booking_reference']) ?></dd>

                    <dt>Booking Date</dt>
<dd><?= e($details['booking_date']) ?></dd>

                    <dt>Booked By</dt>
<dd><?= e($details['account_name']) ?></dd>

                    <dt>Account Email</dt>
<dd><?= e($details['account_email']) ?></dd>

                    <dt>Account Phone</dt>
<dd><?= e($details['account_phone']) ?></dd>

                </dl>

            </div>

            <div class="booking-section">

                <h3>Passenger Details</h3>

                <?php if (!empty($details['passengers'])): ?>

                    <?php foreach ($details['passengers'] as $index => $passenger): ?>

                        <div class="booking-passenger">

                            <strong>
                                Passenger <?= $index + 1 ?>:
                                <?= e($passenger['passenger_name']) ?>
                            </strong>

                            <dl class="dl">

                                <dt>Email</dt>
<dd><?= e($passenger['passenger_email']) ?></dd>

                                <dt>Phone</dt>
<dd><?= e($passenger['passenger_phone']) ?></dd>

                                <dt>IC Number (Masked)</dt>
<dd><?= e(maskIC($passenger['passenger_ic'])) ?></dd>

                                <dt>Seat Assignment</dt>
<dd><?php if (!empty($passenger['seat_number'])): ?>

                                        <?= e($passenger['coach_number']) ?>
                                        -
                                        <?= e($passenger['seat_number']) ?>

                                    <?php else: ?>

                                        Not assigned

                                    <?php endif; ?></dd>

                                <dt>Coach Class</dt>
<dd><?= !empty($passenger['class_name'])
                                        ? e($passenger['class_name'])
                                        : 'Not assigned' ?></dd>

                            </dl>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No passenger records found for this booking.</p>

                <?php endif; ?>

            </div>

            <div class="booking-section">

                <h3>Train and Journey Details</h3>

                <dl class="dl">

                    <dt>Train Number</dt>
<dd><?= e($details['train_number']) ?></dd>

                    <dt>Train Name</dt>
<dd><?= e($details['train_name']) ?></dd>

                    <dt>Route</dt>
<dd><?= e($details['origin_name']) ?>
                        →
                        <?= e($details['destination_name']) ?></dd>

                    <dt>Departure</dt>
<dd><?= e($details['departure_datetime']) ?></dd>

                    <dt>Arrival</dt>
<dd><?= e($details['arrival_datetime']) ?></dd>

                </dl>

            </div>

            <div class="booking-section">

                <h3>Payment and Refund Information</h3>

                <dl class="dl">

                    <dt>Total Fare</dt>
<dd>RM <?= e(number_format((float)$details['total_amount'], 2)) ?></dd>

                    <dt>Payment Status</dt>
<dd><span class="schedule-status pay-<?= e($details['payment_status']) ?>"><?= e(ucfirst($details['payment_status'])) ?></span></dd>

                    <dt>Refund Amount</dt>
<dd>RM <?= e(number_format((float)$details['refund_amount'], 2)) ?></dd>

                    <dt>Refund Status</dt>
<dd><?= e(ucfirst($details['refund_status'])) ?></dd>

                </dl>

            </div>

            <div class="booking-section">

                <h3>Cancellation Information</h3>

                <dl class="dl">

                    <dt>Cancellation Status</dt>
<dd><?= $details['booking_status'] === 'cancelled'
                            ? 'Cancelled'
                            : 'Not cancelled' ?></dd>

                    <dt>Cancellation Date</dt>
<dd><?= !empty($details['cancellation_date'])
                            ? e($details['cancellation_date'])
                            : 'Not applicable' ?></dd>

                    <dt>Cancellation Reason</dt>
<dd><?= !empty($details['cancellation_reason'])
                            ? e($details['cancellation_reason'])
                            : 'Not provided' ?></dd>

                </dl>

            </div>

            <div class="booking-dialog-actions">

                <button
                    type="button"
                    class="btn-close"
                    onclick="this.closest('dialog').close()"
                >
                    Close
                </button>

                <a
                    href="booking-edit.php?id=<?= e($details['booking_id']) ?>"
                    class="btn-primary"
                >
                    Edit Booking
                </a>

            </div>

        </dialog>

    <?php endforeach; ?>

<?php endif; ?>


</body>
</html>