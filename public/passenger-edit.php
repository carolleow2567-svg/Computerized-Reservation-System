<?php

$pdo = require __DIR__ . '/../app/config/database.php';

require_once __DIR__ . '/../app/models/Passenger.php';

$passengerModel = new Passenger($pdo);

// Temporary test ID
$passengerId = 1;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $identity = trim($_POST['identity'] ?? '');

    if ($fullname === '' || $email === '') {
        $error = 'Full name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $passengerModel->updateProfile(
                $passengerId,
                $fullname,
                $email,
                $phone,
                $identity
            );

            header('Location: passenger-profile.php?updated=1');
            exit;

        } catch (PDOException $e) {
            $error = 'Unable to update profile. Please check whether the email is already registered.';
        }
    }
}

$passenger = $passengerModel->findById($passengerId);

if (!$passenger) {
    die('Passenger not found.');
}

require __DIR__ . '/../app/views/passengers/edit.php';
