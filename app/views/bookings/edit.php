<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Booking | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css">
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
            <input type="text" value="<?= e($booking['booking_reference']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Booking ID</label>
            <input type="text" value="<?= e($booking['booking_id']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Booking Date</label>
            <input type="text" value="<?= e($booking['booking_date']) ?>" disabled>
        </div>

        <div class="form-group">
            <label>Total Amount (RM)</label>
            <input type="text" value="<?= e(number_format((float)$booking['total_amount'], 2)) ?>" disabled>
        </div>

        <form method="POST">

            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label>Booking Status</label>
                <select name="booking_status" required>
                    <?php foreach (['pending', 'confirmed', 'cancelled'] as $status): ?>
                        <option value="<?= e($status) ?>"
                            <?= $booking['booking_status'] === $status ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                Update Booking
            </button>

        </form>

    </section>

</div>

</body>
</html>