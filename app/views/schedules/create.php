<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Schedule | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/m7.css?v=<?= (int) @filemtime(__DIR__ . '/../../../public/assets/css/m7.css') ?>">
</head>
<body>

<?php
$adminActive = 'schedules';
$adminCrumb  = 'Add Schedule';
require __DIR__ . '/../partials/admin_start.php';
?>

    <header class="page-header">
        <div>
            <h1>Add Train Schedule</h1>
            <p>Create a new train journey schedule.</p>
        </div>
        <a href="schedules.php" class="btn-edit">Back to Schedules</a>
    </header>

    <section class="table-section">

        <?php if ($errors): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label>Train</label>
                <select name="train_id" required>
                    <option value="">Select Train</option>
                    <?php foreach ($trains as $train): ?>
                        <option value="<?= e($train['train_id']) ?>"
                            <?= (string)$data['train_id'] === (string)$train['train_id'] ? 'selected' : '' ?>>
                            <?= e($train['train_number'] . ' - ' . $train['train_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Origin Station</label>
                <select name="origin_station_id" required>
                    <option value="">Select Origin</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= e($station['station_id']) ?>"
                            <?= (string)$data['origin_station_id'] === (string)$station['station_id'] ? 'selected' : '' ?>>
                            <?= e($station['station_code'] . ' - ' . $station['station_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Destination Station</label>
                <select name="destination_station_id" required>
                    <option value="">Select Destination</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= e($station['station_id']) ?>"
                            <?= (string)$data['destination_station_id'] === (string)$station['station_id'] ? 'selected' : '' ?>>
                            <?= e($station['station_code'] . ' - ' . $station['station_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Departure Date & Time</label>
                <input type="datetime-local" name="departure_datetime"
                       required value="<?= e($data['departure_datetime']) ?>">
            </div>

            <div class="form-group">
                <label>Arrival Date & Time</label>
                <input type="datetime-local" name="arrival_datetime"
                       required value="<?= e($data['arrival_datetime']) ?>">
            </div>

            <div class="form-group">
                <label>Base Fare (RM)</label>
                <input type="number" name="base_fare"
                       min="0" step="0.01" required
                       value="<?= e($data['base_fare']) ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <?php foreach (['scheduled', 'delayed', 'cancelled', 'completed'] as $status): ?>
                        <option value="<?= e($status) ?>"
                            <?= $data['status'] === $status ? 'selected' : '' ?>>
                            <?= e(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">Save Schedule</button>

        </form>
    </section>
<?php require __DIR__ . '/../partials/admin_end.php'; ?>

</body>
</html>