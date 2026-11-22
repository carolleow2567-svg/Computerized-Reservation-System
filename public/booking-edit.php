<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

$controller = new BookingController($pdo);

// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: bookings.php');
    exit;
}

$booking = $controller->show($id);

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
      $errors[] = 'Invalid form submission. Please try again.';
    }

    $status = $_POST['booking_status'] ?? '';

    $allowedStatuses = ['pending', 'confirmed', 'cancelled'];

    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Invalid booking status.';
    }

    if ($status === $booking['booking_status']) {
        $errors[] = 'The booking already has this status.';
    }

    if (empty($errors)) {
        try {
            $controller->updateStatus($id, $status);

            $_SESSION['success'] = 'Booking status updated successfully.';
            header('Location: bookings.php');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Unable to update booking status.';
        }
    }
}

require_once __DIR__ . '/../app/views/bookings/edit.php';