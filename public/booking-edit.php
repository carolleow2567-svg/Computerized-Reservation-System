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

    $action = $_POST['action'] ?? 'booking_status';

    if (empty($errors)) {

        try {

            // =========================================
            // ACTION 1: UPDATE BOOKING STATUS
            // =========================================
            if ($action === 'booking_status') {

                $status = $_POST['booking_status'] ?? '';
                $reason = trim($_POST['cancellation_reason'] ?? '');

                $allowedStatuses = [
                    'pending',
                    'confirmed',
                    'cancelled'
                ];

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
                        $errors[] =
                            'Cancellation reason cannot exceed 255 characters.';
                    }
                }

                if (empty($errors)) {

                    $updated = $controller->updateStatus(
                        $id,
                        $status,
                        $reason
                    );

                    if ($updated) {

                        $_SESSION['success'] =
                            'Booking status updated successfully.';

                        if ($status === 'cancelled') {
                            $_SESSION['success'] =
                                'Booking cancelled successfully. The cancellation date and reason have been recorded.';
                        }

                        header('Location: bookings.php');
                        exit;
                    }

                    $errors[] =
                        'Unable to update this booking. The status transition may not be permitted, or the booking may have already been changed.';
                }

                $booking['cancellation_reason'] = $reason;
            }

            // =========================================
            // ACTION 2: UPDATE PAYMENT STATUS
            // =========================================
            elseif ($action === 'payment_status') {

                $paymentStatus = $_POST['payment_status'] ?? '';

                if (!in_array(
                    $paymentStatus,
                    ['paid', 'failed'],
                    true
                )) {
                    $errors[] = 'Invalid payment status.';
                }

                if (empty($errors)) {

                    $updated = $controller->updatePaymentStatus(
                        $id,
                        $paymentStatus
                    );

                    if ($updated) {

                        $_SESSION['success'] =
                            'Payment status updated successfully.';

                        header('Location: booking-edit.php?id=' . $id);
                        exit;
                    }

                    $errors[] =
                        'Unable to update payment status. This booking may not be eligible for the selected change.';
                }
            }

            // =========================================
            // ACTION 3: UPDATE REFUND
            // =========================================
            elseif ($action === 'refund') {

                $refundStatus = $_POST['refund_status'] ?? '';
                $refundAmountInput = trim(
                    $_POST['refund_amount'] ?? ''
                );

                $allowedRefundStatuses = [
                    'pending',
                    'processed',
                    'rejected'
                ];

                if (!in_array(
                    $refundStatus,
                    $allowedRefundStatuses,
                    true
                )) {
                    $errors[] = 'Invalid refund status.';
                }

                if ($refundStatus === 'pending') {

                    if (
                        $refundAmountInput === '' ||
                        !is_numeric($refundAmountInput)
                    ) {
                        $errors[] = 'Please enter a valid refund amount.';
                    } else {

                        $refundAmount = (float) $refundAmountInput;

                        if ($refundAmount <= 0) {
                            $errors[] =
                                'Refund amount must be greater than RM0.';
                        } elseif (
                            $refundAmount > (float) $booking['total_amount']
                        ) {
                            $errors[] =
                                'Refund amount cannot exceed the booking total.';
                        }
                    }

                } else {

                    // Processing or rejecting an existing refund
                    // uses the amount already stored in the database.
                    $refundAmount =
                        (float) $booking['refund_amount'];
                }

                if (empty($errors)) {

                    $updated = $controller->updateRefund(
                        $id,
                        $refundStatus,
                        $refundAmount
                    );

                    if ($updated) {

                        $_SESSION['success'] =
                            'Refund status updated successfully.';

                        header('Location: booking-edit.php?id=' . $id);
                        exit;
                    }

                    $errors[] =
                        'Unable to update refund. Check the booking payment status, cancellation status, and current refund status.';
                }
            }

            // =========================================
            // INVALID ACTION
            // =========================================
            else {
                $errors[] = 'Invalid booking action.';
            }

        } catch (PDOException $exception) {

            error_log($exception->getMessage());

            $errors[] =
                'A database error occurred. Unable to update booking.';
        }
    }
}

require_once __DIR__ . '/../app/views/bookings/edit.php';