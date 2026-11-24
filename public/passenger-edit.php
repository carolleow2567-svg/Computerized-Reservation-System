<?php

$pdo = require __DIR__ . '/../app/config/database.php';

// This helper starts the session and provides the passenger and CSRF guards.
require_once __DIR__ . '/../app/helpers/m2_helpers.php';
require_once __DIR__ . '/../app/models/Passenger.php';

$passengerModel = new Passenger($pdo);
$passengerId = require_passenger($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $action = $_POST['action'] ?? 'update_profile';
    $length = static function ($value): int {
        return function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
    };

    if ($action === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Please complete all password fields.';
        } elseif ($length($newPassword) < 8) {
            $error = 'Your new password must contain at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'The new password and confirmation do not match.';
        } else {
            try {
                if ($passengerModel->changePassword($passengerId, $currentPassword, $newPassword)) {
                    session_regenerate_id(true);
                    $success = 'Your password has been changed successfully.';
                } else {
                    $error = 'Your current password is incorrect.';
                }
            } catch (PDOException $e) {
                $error = 'Unable to change your password. Please try again.';
            }
        }
    } else {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($fullname === '' || $email === '') {
            $error = 'Full name and email are required.';
        } elseif ($length($fullname) > 100 || $length($email) > 100 || $length($phone) > 20) {
            $error = 'Please keep your name and email within 100 characters and phone number within 20 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $passengerModel->updateProfile($passengerId, $fullname, $email, $phone);
                header('Location: passenger-profile.php?updated=1');
                exit;
            } catch (PDOException $e) {
                $error = 'Unable to update profile. Please check whether the email is already registered.';
            }
        }
    }
}

$passenger = $passengerModel->findById($passengerId);

if (!$passenger) {
    http_response_code(404);
    exit('Passenger profile not found.');
}

require __DIR__ . '/../app/views/passengers/edit.php';
