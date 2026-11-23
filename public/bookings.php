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

    <link rel="stylesheet" href="assets/css/trains.css">

    <style>
        .booking-dialog {
            width: min(850px, 92%);
            max-height: 85vh;
            overflow-y: auto;

            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            margin: 0;
            border: none;
            border-radius: 12px;
            padding: 28px;

            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        }

        .booking-dialog::backdrop {
            background: rgba(0, 0, 0, 0.55);
        }

        .booking-dialog-header {
            position: sticky;
            top: 0;
            background: #ffffff;
            padding-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .booking-dialog-header h2 {
            margin: 0;
        }

        .booking-section {
            margin-top: 22px;
        }

        .booking-section h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .booking-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .booking-info-item {
            padding: 10px;
            background: #f7f7f7;
            border-radius: 6px;
            overflow-wrap: anywhere;
        }

        .booking-info-item small {
            display: block;
            color: #666;
            margin-bottom: 5px;
        }

        .booking-passenger {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 12px;
        }

        .booking-dialog-actions {
            position: sticky;
            top: 0;
            background: #ffffff;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-view {
            display: inline-block;
            padding: 7px 12px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-close {
            position: sticky;
            padding: 8px 16px;
            background: #666;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .booking-info-grid {
                grid-template-columns: 1fr;
            }

            .booking-dialog {
                padding: 18px;
            }
        }
    </style>
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

            <input
                type="text"
                name="search"
                placeholder="Search reference, passenger, email or train"
                value="<?= e($search) ?>"
            >

            <select name="status">
                <option value="">All Booking Statuses</option>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option value="<?= e($status) ?>"
                        <?= $status === $statusFilter ? 'selected' : '' ?>>
                        <?= e(ucfirst($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="payment_status">
                <option value="">All Payment Statuses</option>
                <?php foreach ($allowedPaymentStatuses as $payment): ?>
                    <option  value="<?= e($payment) ?>"
                        <?= $payment === $paymentFilter ? 'selected' : '' ?>>
                        <?= e(ucfirst($payment)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input
                type="date"
                name="departure_date"
                value="<?= e($dateFilter) ?>">

            <button type="submit" class="btn-primary">Apply Filters</button>

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

                            <td>
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
                                <?= e(ucfirst($booking['payment_status'])) ?>
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
                                        class="btn-edit"
                                        onclick="return confirm('Are you sure you want to cancel this booking?');">
                                        Cancel
                                    </a>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>


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

                <div class="booking-info-grid">

                    <div class="booking-info-item">
                        <small>Booking Reference</small>
                        <?= e($details['booking_reference']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Booking Date</small>
                        <?= e($details['booking_date']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Booked By</small>
                        <?= e($details['account_name']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Account Email</small>
                        <?= e($details['account_email']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Account Phone</small>
                        <?= e($details['account_phone']) ?>
                    </div>

                </div>

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

                            <div class="booking-info-grid">

                                <div class="booking-info-item">
                                    <small>Email</small>
                                    <?= e($passenger['passenger_email']) ?>
                                </div>

                                <div class="booking-info-item">
                                    <small>Phone</small>
                                    <?= e($passenger['passenger_phone']) ?>
                                </div>

                                <div class="booking-info-item">
                                    <small>IC Number (Masked)</small>
                                    <?= e(maskIC($passenger['passenger_ic'])) ?>
                                </div>

                                <div class="booking-info-item">
                                    <small>Seat Assignment</small>

                                    <?php if (!empty($passenger['seat_number'])): ?>

                                        <?= e($passenger['coach_number']) ?>
                                        -
                                        <?= e($passenger['seat_number']) ?>

                                    <?php else: ?>

                                        Not assigned

                                    <?php endif; ?>
                                </div>

                                <div class="booking-info-item">
                                    <small>Coach Class</small>

                                    <?= !empty($passenger['class_name'])
                                        ? e($passenger['class_name'])
                                        : 'Not assigned' ?>
                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>No passenger records found for this booking.</p>

                <?php endif; ?>

            </div>

            <div class="booking-section">

                <h3>Train and Journey Details</h3>

                <div class="booking-info-grid">

                    <div class="booking-info-item">
                        <small>Train Number</small>
                        <?= e($details['train_number']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Train Name</small>
                        <?= e($details['train_name']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Route</small>
                        <?= e($details['origin_name']) ?>
                        →
                        <?= e($details['destination_name']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Departure</small>
                        <?= e($details['departure_datetime']) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Arrival</small>
                        <?= e($details['arrival_datetime']) ?>
                    </div>

                </div>

            </div>

            <div class="booking-section">

                <h3>Payment and Refund Information</h3>

                <div class="booking-info-grid">

                    <div class="booking-info-item">
                        <small>Total Fare</small>
                        RM <?= e(number_format((float)$details['total_amount'], 2)) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Payment Status</small>
                        <?= e(ucfirst($details['payment_status'])) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Refund Amount</small>
                        RM <?= e(number_format((float)$details['refund_amount'], 2)) ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Refund Status</small>
                        <?= e(ucfirst($details['refund_status'])) ?>
                    </div>

                </div>

            </div>

            <div class="booking-section">

                <h3>Cancellation Information</h3>

                <div class="booking-info-grid">

                    <div class="booking-info-item">
                        <small>Cancellation Status</small>
                        <?= $details['booking_status'] === 'cancelled'
                            ? 'Cancelled'
                            : 'Not cancelled' ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Cancellation Date</small>
                        <?= !empty($details['cancellation_date'])
                            ? e($details['cancellation_date'])
                            : 'Not applicable' ?>
                    </div>

                    <div class="booking-info-item">
                        <small>Cancellation Reason</small>
                        <?= !empty($details['cancellation_reason'])
                            ? e($details['cancellation_reason'])
                            : 'Not provided' ?>
                    </div>

                </div>

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
                    class="btn-edit"
                >
                    Edit Booking
                </a>

            </div>

        </dialog>

    <?php endforeach; ?>

<?php endif; ?>


</body>
</html>