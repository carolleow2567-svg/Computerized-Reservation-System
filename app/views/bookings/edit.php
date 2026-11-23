<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Booking | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css">

    <style>
        .cancel-dialog {
            width: min(500px, 90%);
            border: none;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        }

        .cancel-dialog::backdrop {
            background: rgba(0, 0, 0, 0.55);
        }

        .cancel-dialog h2 {
            margin-top: 0;
        }

        .cancel-dialog textarea {
            width: 100%;
            min-height: 100px;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
        }

        .dialog-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel-confirm {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .btn-dialog-close {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            cursor: pointer;
        }

        .current-cancellation {
            margin-top: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="container">

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

    </section>

</div>

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
}
</script>

</body>
</html>