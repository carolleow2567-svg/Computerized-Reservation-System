<?php

session_start();

$pdo = require_once __DIR__ . '/../app/config/database.php';

require_once __DIR__ . '/../app/controllers/TrainController.php';

$controller = new TrainController($pdo);

$errors = [];

$trainNumber = '';
$trainName = '';
$trainType = '';
$status = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and clean submitted values
    $trainNumber = strtoupper(trim($_POST['train_number'] ?? ''));
    $trainName = trim($_POST['train_name'] ?? '');
    $trainType = trim($_POST['train_type'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Validate train number
    if ($trainNumber === '') {
        $errors[] = 'Train number is required.';
    } elseif (!preg_match('/^[A-Z0-9-]{2,20}$/', $trainNumber)) {
        $errors[] = 'Train number must contain 2-20 letters, numbers or hyphens.';
    }

    // Validate train name
    if ($trainName === '') {
        $errors[] = 'Train name is required.';
    } elseif (mb_strlen($trainName) > 100) {
        $errors[] = 'Train name cannot exceed 100 characters.';
    }

    // Validate train type
    $allowedTypes = ['ETS', 'Intercity'];

    if (!in_array($trainType, $allowedTypes, true)) {
        $errors[] = 'Please select a valid train type.';
    }

    // Validate status
    $allowedStatuses = ['active', 'inactive'];

    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid status.';
    }

    // Insert into database
    if (empty($errors)) {

        try {

            $controller->create([
                'train_number' => $trainNumber,
                'train_name' => $trainName,
                'train_type' => $trainType,
                'status' => $status
            ]);

            header('Location: trains.php?success=created');
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $errors[] = 'This train number already exists.';
            } else {
                error_log('Create train error: ' . $e->getMessage());
                $errors[] = 'Unable to save the train. Please try again.';
            }
        }
    }
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Train | CRS Admin</title>

    <link rel="stylesheet" href="assets/css/trains.css">
</head>

<body>

<div class="container">

    <header class="page-header">
        <div>
            <h1>Add New Train</h1>
            <p>Enter the train information below.</p>
        </div>

        <a href="trains.php" class="btn-secondary">
            Back to Trains
        </a>
    </header>

    <section class="form-section">

        <?php if (!empty($errors)): ?>

            <div class="error-message">
                <strong>Please correct the following:</strong>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="train_number">Train Number</label>

                <input
                    type="text"
                    id="train_number"
                    name="train_number"
                    maxlength="20"
                    value="<?= e($trainNumber) ?>"
                    placeholder="Example: TR1006"
                    required
                >
            </div>

            <div class="form-group">
                <label for="train_name">Train Name</label>

                <input
                    type="text"
                    id="train_name"
                    name="train_name"
                    maxlength="100"
                    value="<?= e($trainName) ?>"
                    placeholder="Enter train name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="train_type">Train Type</label>

                <select id="train_type" name="train_type" required>
                    <option value="">Select train type</option>

                    <option value="ETS"
                        <?= $trainType === 'ETS' ? 'selected' : '' ?>>
                        ETS
                    </option>

                    <option value="Intercity"
                        <?= $trainType === 'Intercity' ? 'selected' : '' ?>>
                        Intercity
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>

                <select id="status" name="status" required>
                    <option value="active"
                        <?= $status === 'active' ? 'selected' : '' ?>>
                        Active
                    </option>

                    <option value="inactive"
                        <?= $status === 'inactive' ? 'selected' : '' ?>>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="form-actions">

                <a href="trains.php" class="btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn-primary">
                    Save Train
                </button>

            </div>

        </form>

    </section>

</div>

</body>
</html>