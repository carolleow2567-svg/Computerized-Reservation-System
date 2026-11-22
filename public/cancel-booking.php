<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

// Authentication check
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'passenger') {
    header('Location: test-login.php');
    exit;
}

// Only POST is accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('Invalid request.');
}

$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$bookingId) {
    $_SESSION['error'] = 'Invalid booking.';
    header('Location: booking-history.php');
    exit;
}

$controller = new BookingController($pdo);
$error = $controller->cancel($bookingId, (int)$_SESSION['user_id'], (string)($_POST['reason'] ?? ''));

if ($error === null) {
    $_SESSION['success'] = 'Cancellation request submitted. Please wait for approval.';
} else {
    $_SESSION['error'] = $error;
}

header('Location: booking-history.php');
exit;