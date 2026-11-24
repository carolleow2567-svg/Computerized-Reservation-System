<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Booking | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css?v=<?= (int) @filemtime(__DIR__ . '/../../../public/assets/css/trains.css') ?>">

</head>

<body>

<?php
$adminActive = 'bookings';
$adminCrumb  = 'Manage Booking';
require __DIR__ . '/../partials/admin_start.php';
?>

    <header class="page-header">
        <div>
            <h1>Manage Booking</h1>
            <p>Review booking information and status.</p>
        </div>

        <a href="bookings.php" class="btn-edit">Back to Bookings</a>
    </header>

    <section class="table-section">

        <?php if ($errors): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Booking Information</h2>

        <div class="form-group">
            <label>Booking Reference</label>
            <input type="text"
                   value="<?= e($booking['booking_reference']) ?>"
                   disabled>
        </div>

        <div class="form-group">
            <label>Booking ID</label>
            <input type="text"
                   value="<?= e($booking['booking_id']) ?>"
                   disabled>
        </div>

        <div class="form-group">
            <label>Booking Date</label>
            <input type="text"
                   value="<?= e($booking['booking_date']) ?>"
                   disabled>
        </div>

        <div class="form-group">
            <label>Total Amount (RM)</label>
            <input type="text"
                   value="<?= e(number_format((float) $booking['total_amount'], 2)) ?>"
                   disabled>
        </div>

        <div class="form-group">
            <label>Current Booking Status</label>
            <input type="text"
                   value="<?= e(ucfirst($booking['booking_status'])) ?>"
                   disabled>
        </div>

        <?php if ($booking['booking_status'] === 'cancelled'): ?>

            <div class="current-cancellation">
                <h3>Cancellation Information</h3>

                <p>
                    <strong>Cancellation Date:</strong>
                    <?= e($booking['cancellation_date'] ?? 'Not recorded') ?>
                </p>

                <p>
                    <strong>Cancellation Reason:</strong>
                    <?= e($booking['cancellation_reason'] ?? 'Not recorded') ?>
                </p>
            </div>

            <p>This booking has been cancelled and its status cannot be changed.</p>

        <?php else: ?>

            <form method="POST" id="bookingForm">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="booking_status">Booking Status</label>

                    <select name="booking_status"
                            id="booking_status"
                            required>

                        <?php
                        if ($booking['booking_status'] === 'pending') {
                            $availableStatuses = ['pending', 'confirmed', 'cancelled'];
                        } else {
                            $availableStatuses = ['confirmed', 'cancelled'];
                        }
                        ?>

                        <?php foreach ($availableStatuses as $status): ?>
                            <option value="<?= e($status) ?>"
                                <?= $booking['booking_status'] === $status ? 'selected' : '' ?>>
                                <?= e(ucfirst($status)) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <input type="hidden"
                       name="cancellation_reason"
                       id="cancellation_reason">

                <button type="submit"
                        class="btn-primary"
                        id="updateButton">
                    Update Booking
                </button>

                <dialog id="cancelDialog" class="cancel-dialog">

                    <h2>Confirm Booking Cancellation</h2>

                    <p>
                        Are you sure you want to cancel booking
                        <strong><?= e($booking['booking_reference']) ?></strong>?
                    </p>

                    <p>
                        This action cannot be reversed. The cancellation
                        date and reason will be recorded.
                    </p>

                    <div class="form-group">
                        <label for="reasonInput">
                            Cancellation Reason
                        </label>

                        <textarea
                            id="reasonInput"
                            maxlength="255"
                            placeholder="Enter the reason for cancelling this booking..."
                        ></textarea>
                    </div>

                    <div class="dialog-actions">

                        <button type="button"
                                class="btn-dialog-close"
                                id="closeDialog">
                            Go Back
                        </button>

                        <button type="button"
                                class="btn-cancel-confirm"
                                id="confirmCancel">
                            Confirm Cancellation
                        </button>

                    </div>

                </dialog>

            </form>

        <?php endif; ?>

        <hr>

        <h2>Payment Management</h2>

        <div class="form-group">
            <label>Current Payment Status</label>
            <input type="text"
                value="<?= e(ucfirst($booking['payment_status'])) ?>"
                disabled>
        </div>

        <?php if (
            $booking['booking_status'] !== 'cancelled' &&
            in_array($booking['payment_status'], ['unpaid', 'failed'], true)
        ): ?>

            <form method="POST">

                <input type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>">

                <input type="hidden"
                    name="action"
                    value="payment_status">

                <div class="form-group">
                    <label for="payment_status">Update Payment Status</label>

                    <select name="payment_status"
                            id="payment_status"
                            required>

                        <?php if ($booking['payment_status'] === 'unpaid'): ?>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        <?php else: ?>
                            <option value="paid">Paid</option>
                        <?php endif; ?>

                    </select>
                </div>

                <button type="submit" class="btn-primary">
                    Update Payment
                </button>

            </form>

        <?php elseif ($booking['payment_status'] === 'refunded'): ?>

            <p>Payment has been refunded.</p>

        <?php elseif ($booking['payment_status'] === 'paid'): ?>

            <p>Payment has been completed successfully.</p>

        <?php elseif ($booking['booking_status'] === 'cancelled'): ?>

            <p>Payment cannot be updated because this booking is cancelled.</p>

        <?php endif; ?>

        <hr>

        <h2>Refund Management</h2>

        <div class="form-group">
            <label>Current Refund Status</label>
            <input type="text"
                value="<?= e(ucfirst($booking['refund_status'])) ?>"
                disabled>
        </div>

        <div class="form-group">
            <label>Refund Amount (RM)</label>
            <input type="text"
                value="<?= e(number_format((float) $booking['refund_amount'], 2)) ?>"
                disabled>
        </div>

        <?php if (
            $booking['booking_status'] === 'cancelled' &&
            $booking['payment_status'] === 'paid' &&
            $booking['refund_status'] === 'none'
        ): ?>

            <h3>Request Refund</h3>

            <form method="POST">

                <input type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>">

                <input type="hidden"
                    name="action"
                    value="refund">

                <input type="hidden"
                    name="refund_status"
                    value="pending">

                <div class="form-group">
                    <label for="refund_amount">Refund Amount (RM)</label>

                    <input type="number"
                        name="refund_amount"
                        id="refund_amount"
                        min="0.01"
                        max="<?= e($booking['total_amount']) ?>"
                        step="0.01"
                        required>
                </div>

                <button type="submit" class="btn-primary">
                    Submit Refund Request
                </button>

            </form>

        <?php elseif (
            $booking['refund_status'] === 'pending'
        ): ?>

            <h3>Refund Pending</h3>

            <p>A refund request has been recorded and is awaiting processing.</p>

            <form method="POST">

                <input type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']) ?>">

                <input type="hidden"
                    name="action"
                    value="refund">

                <input type="hidden"
                    name="refund_amount"
                    value="<?= e($booking['refund_amount']) ?>">

                <div class="dialog-actions">

                    <button type="submit"
                            name="refund_status"
                            value="processed"
                            class="btn-primary">
                        Process Refund
                    </button>

                    <button type="submit"
                            name="refund_status"
                            value="rejected"
                            class="btn-cancel-confirm">
                        Reject Refund
                    </button>

                </div>

            </form>

        <?php elseif (
            $booking['refund_status'] === 'processed'
        ): ?>

            <p>Refund has been successfully processed.</p>

        <?php elseif (
            $booking['refund_status'] === 'rejected'
        ): ?>

            <p>The refund request was rejected.</p>

        <?php elseif (
            $booking['booking_status'] !== 'cancelled'
        ): ?>

            <p>A refund can only be requested after the booking is cancelled and payment is completed.</p>

        <?php elseif (
            $booking['payment_status'] !== 'paid'
        ): ?>

            <p>A refund is unavailable because the payment has not been completed.</p>

        <?php endif; ?>

    </section>

<?php require __DIR__ . '/../partials/admin_end.php'; ?>

<script>
const bookingForm = document.getElementById('bookingForm');

if (bookingForm) {

    const statusSelect = document.getElementById('booking_status');
    const dialog = document.getElementById('cancelDialog');
    const reasonInput = document.getElementById('reasonInput');
    const reasonHidden = document.getElementById('cancellation_reason');

    const originalStatus = <?= json_encode($booking['booking_status']) ?>;

    let cancellationConfirmed = false;

    // Open confirmation dialog when cancellation is selected.
    statusSelect.addEventListener('change', function () {

        if (this.value === 'cancelled') {
            cancellationConfirmed = false;
            reasonInput.value = '';
            dialog.showModal();
        }
    });

    // Close dialog and restore the original status.
    document.getElementById('closeDialog').addEventListener('click', function () {

        dialog.close();
        statusSelect.value = originalStatus;
        cancellationConfirmed = false;
    });

    // Confirm cancellation and submit the form.
    document.getElementById('confirmCancel').addEventListener('click', function () {

        const reason = reasonInput.value.trim();

        if (reason === '') {
            alert('Please enter a cancellation reason.');
            reasonInput.focus();
            return;
        }

        if (reason.length > 255) {
            alert('Cancellation reason cannot exceed 255 characters.');
            reasonInput.focus();
            return;
        }

        reasonHidden.value = reason;
        cancellationConfirmed = true;

        dialog.close();
        bookingForm.requestSubmit();
    });

    // Prevent accidental submission without confirmation.
    bookingForm.addEventListener('submit', function (event) {

        if (statusSelect.value === 'cancelled' && !cancellationConfirmed) {
            event.preventDefault();
            dialog.showModal();
        }
    });

    // Automatically open cancellation confirmation
    // when arriving through the Cancel action.
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('action') === 'cancel') {

        statusSelect.value = 'cancelled';

        statusSelect.dispatchEvent(
        new Event('change')
        );
    }
}
</script>

</body>
</html>