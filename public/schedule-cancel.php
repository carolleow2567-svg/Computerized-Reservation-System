<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/ScheduleController.php';

$controller = new ScheduleController($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: schedules.php');
    exit;
}

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')
) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: schedules.php');
    exit;
}

$id = filter_var($_POST['schedule_id'] ?? '', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['error'] = 'Invalid schedule ID.';
    header('Location: schedules.php');
    exit;
}

$schedule = $controller->show($id);

if (!$schedule) {
    $_SESSION['error'] = 'Schedule not found.';
} elseif (!in_array($schedule['status'], ['scheduled', 'delayed'], true)) {
    $_SESSION['error'] = 'This schedule cannot be cancelled.';
} elseif ($controller->hasConfirmedBookings($id)) {
    $_SESSION['error'] = 'Cancellation blocked: this schedule has confirmed bookings.';
} else {
    try {
        if ($controller->cancel($id)) {
            $_SESSION['success'] = 'Schedule cancelled successfully.';
        } else {
            $_SESSION['error'] = 'Schedule could not be cancelled.';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = 'Unable to cancel schedule.';
    }
}

header('Location: schedules.php');
exit;