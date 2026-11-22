<?php

session_start();

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
                            <td><?= e(ucfirst($schedule['status'])) ?></td>
                            <td><a href="schedule-edit.php?id=<?= e($schedule['schedule_id']) ?>"
                                class="btn-edit">Edit</a></td>
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