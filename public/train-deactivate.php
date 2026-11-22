<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/TrainController.php';

$controller = new TrainController($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: trains.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: trains.php');
    exit;
}

$id = filter_input(INPUT_POST, 'train_id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['error'] = 'Invalid train ID.';
    header('Location: trains.php');
    exit;
}

$train = $controller->show($id);

if (!$train) {
    $_SESSION['error'] = 'Train record not found.';
} elseif ($train['status'] === 'inactive') {
    $_SESSION['error'] = 'This train is already inactive.';
} elseif ($controller->hasActiveBookings($id)) {
    $_SESSION['error'] = 'Cannot deactivate this train because it has pending or confirmed bookings.';
} else {
    try {
        $controller->deactivate($id);
        $_SESSION['success'] = 'Train deactivated successfully.';
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = 'Unable to deactivate this train.';
    }
}

header('Location: trains.php');
exit;