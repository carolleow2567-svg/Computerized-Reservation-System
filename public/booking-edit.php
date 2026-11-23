<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

$controller = new BookingController($pdo);

// Generate CSRF token if it does not exist.
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
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation.
    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $status = $_POST['booking_status'] ?? '';
    $reason = trim($_POST['cancellation_reason'] ?? '');

    $allowedStatuses = ['pending', 'confirmed', 'cancelled'];

    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Invalid booking status.';
    }

    if ($status === $booking['booking_status']) {
        $errors[] = 'The booking already has this status.';
    }

    if ($status === 'cancelled') {

        if ($reason === '') {
            $errors[] = 'Please provide a cancellation reason.';
        } elseif (strlen($reason) > 255) {
            $errors[] = 'Cancellation reason cannot exceed 255 characters.';
        }
    }

    if (empty($errors)) {
        try {

            $updated = $controller->updateStatus(
                $id,
                $status,
                $reason
            );

            if ($updated) {
                $_SESSION['success'] = 'Booking status updated successfully.';

                if ($status === 'cancelled') {
                    $_SESSION['success'] =
                        'Booking cancelled successfully. The cancellation date and reason have been recorded.';
                }

                header('Location: bookings.php');
                exit;
            }

            $errors[] =
                'Unable to update this booking. The status transition may not be permitted, or the booking may have already been changed.';

        } catch (PDOException $exception) {

            error_log($exception->getMessage());

            $errors[] = 'A database error occurred. Unable to update booking.';
        }
    }

    // Keep the selected form values after validation errors.
    $booking['cancellation_reason'] = $reason;
}

require_once __DIR__ . '/../app/views/bookings/edit.php';