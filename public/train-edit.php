<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/TrainController.php';

$controller = new TrainController($pdo);

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: trains.php');
    exit;
}

$train = $controller->show($id);

if (!$train) {
    header('Location: trains.php');
    exit;
}

$errors = [];

$data = [
    'train_number' => $train['train_number'],
    'train_name' => $train['train_name'],
    'train_type' => $train['train_type'],
    'status' => $train['status']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'train_number' => trim($_POST['train_number'] ?? ''),
        'train_name' => trim($_POST['train_name'] ?? ''),
        'train_type' => trim($_POST['train_type'] ?? ''),
        'status' => $_POST['status'] ?? ''
    ];

    if (
        $data['train_number'] === '' ||
        strlen($data['train_number']) > 20
    ) {
        $errors[] = 'Train number is required (maximum 20 characters).';
    }

    if (
        $data['train_name'] === '' ||
        strlen($data['train_name']) > 100
    ) {
        $errors[] = 'Train name is required (maximum 100 characters).';
    }

    if (
        $data['train_type'] === '' ||
        strlen($data['train_type']) > 50
    ) {
        $errors[] = 'Train type is required (maximum 50 characters).';
    }

    if (!in_array($data['status'], ['active', 'inactive'], true)) {
        $errors[] = 'Invalid train status.';
    }

    if (empty($errors)) {

        if ($controller->existsByTrainNumber($data['train_number'], $id)) {
            $errors[] = 'This train number is already used by another train.';
        } else {

            try {
                $controller->update($id, $data);

                $_SESSION['success'] = 'Train updated successfully.';
                header('Location: trains.php');
                exit;

            } catch (PDOException $e) {
                error_log($e->getMessage());
                $errors[] = 'Unable to update train. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../app/views/trains/edit.php';