<?php

session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/ScheduleController.php';

$controller = new ScheduleController($pdo);
$schedules = $controller->index();

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css?v=<?= (int) @filemtime(__DIR__ . '/assets/css/trains.css') ?>">
</head>

<body>

<?php
$adminActive = 'schedules';
$adminCrumb  = 'Manage Schedules';
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
            <h1>Manage Train Schedules</h1>
            <p>View and manage train departure schedules.</p>
        </div>

        <a href="schedule-create.php" class="btn-primary">
            + Add Schedule
        </a>
    </header>

    <section class="table-section">

        <div class="table-heading">
            <h2>Schedule Records</h2>
            <span>Total Schedules: <?= count($schedules) ?></span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Schedule ID</th>
                        <th>Train</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Fare (RM)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No schedule records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= e($schedule['schedule_id']) ?></td>
                            <td>
                                <?= e($schedule['train_number']) ?><br>
                                <?= e($schedule['train_name']) ?>
                            </td>
                            <td><?= e($schedule['origin_name']) ?></td>
                            <td><?= e($schedule['destination_name']) ?></td>
                            <td><?= e($schedule['departure_datetime']) ?></td>
                            <td><?= e($schedule['arrival_datetime']) ?></td>
                            <td><?= e(number_format((float)$schedule['base_fare'], 2)) ?></td>
                            <td><span class="schedule-status <?= e($schedule['status']) ?>">
                                <?= e(ucfirst($schedule['status'])) ?></span></td>
                            <td><a href="schedule-edit.php?id=<?= e($schedule['schedule_id']) ?>"
                                    class="btn-edit">Edit</a>
                                    
                                <?php if (in_array($schedule['status'], ['scheduled', 'delayed'], true)): ?>
                                        
                                    <form method="POST"
                                          action="schedule-cancel.php"
                                          style="display:inline;"
                                          onsubmit="return confirm('Cancel this schedule?');">
                                              
                                        <input type="hidden" name="schedule_id" value="<?= e($schedule['schedule_id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn-deactivate">
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

<?php require __DIR__ . '/../app/views/partials/admin_end.php'; ?>

</body>
</html>