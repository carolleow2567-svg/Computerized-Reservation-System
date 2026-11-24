<?php

// Database connection
$pdo = require __DIR__ . '/../app/config/database.php';

// Starts the session and provides CSRF protection.
require_once __DIR__ . '/../app/helpers/m2_helpers.php';

$formMessage = '';
$formMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $length = static function ($value): int {
        return function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
    };

    if ($fullname === '' || $email === '' || $subject === '' || $message === '') {
        $formMessage = 'Please complete all required fields.';
        $formMessageType = 'error';
    } elseif (
        $length($fullname) > 100 ||
        $length($email) > 100 ||
        $length($subject) > 100 ||
        $length($message) > 5000
    ) {
        $formMessage = 'Please keep your name, email and subject within 100 characters and your message within 5,000 characters.';
        $formMessageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formMessage = 'Please enter a valid email address.';
        $formMessageType = 'error';
    } else {
        try {
            // Associate the enquiry only with a real passenger account.
            $userId = null;
            if (
                isset($_SESSION['user_id'], $_SESSION['role']) &&
                ($_SESSION['role'] === 'passenger') &&
                filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)
            ) {
                $userCheck = $pdo->prepare(
                    "SELECT user_id FROM users
                     WHERE user_id = :id AND role = 'passenger'
                     LIMIT 1"
                );
                $userCheck->execute(['id' => (int) $_SESSION['user_id']]);
                $verifiedUserId = $userCheck->fetchColumn();

                if ($verifiedUserId !== false) {
                    $userId = (int) $verifiedUserId;
                }
            }

            $stmt = $pdo->prepare(
                "INSERT INTO enquiries (user_id, fullname, email, subject, message)
                 VALUES (:user_id, :fullname, :email, :subject, :message)"
            );

            $stmt->execute([
                'user_id' => $userId,
                'fullname' => $fullname,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

            $formMessage = 'Your enquiry has been submitted successfully!';
            $formMessageType = 'success';
        } catch (PDOException $e) {
            $formMessage = 'Sorry, something went wrong. Please try again.';
            $formMessageType = 'error';
        }
    }
}

require __DIR__ . '/../app/views/help/index.php';
