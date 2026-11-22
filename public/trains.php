<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/TrainController.php';

$controller = new TrainController($pdo);
// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$trains = $controller->index();

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

    <title>Manage Trains | CRS Admin</title>

    <link rel="stylesheet" href="assets/css/trains.css">
</head>

<body>

<div class="container">
    
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message">
            <?= e($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message">
            <?= e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <header class="page-header">
        <div>
            <h1>Manage Trains</h1>
            <p>Manage train information and records.</p>
        </div>

        <a href="train-create.php" class="btn-primary">
            + Add New Train
        </a>
    </header>
    <?php if (($_GET['success'] ?? '') === 'created'): ?>

    <div class="success-message">
        Train record added successfully!
    </div>

<?php endif; ?>
    <section class="table-section">

        <div class="table-heading">
            <h2>Train Records</h2>
            <span>Total Trains: <?= count($trains) ?></span>
        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Train ID</th>
                        <th>Train Number</th>
                        <th>Train Name</th>
                        <th>Train Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($trains)): ?>

                    <tr>
                        <td colspan="6" class="empty">
                            No train records found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($trains as $train): ?>

                        <tr>

                            <td><?= e($train['train_id']) ?></td>

                            <td><?= e($train['train_number']) ?></td>

                            <td><?= e($train['train_name']) ?></td>

                            <td><?= e($train['train_type']) ?></td>

                            <td>
                                <span class="status <?= e($train['status']) ?>">
                                    <?= e(ucfirst($train['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <a
                                    href="train-edit.php?id=<?= e($train['train_id']) ?>"
                                    class="btn-edit">
                                    Edit
                                </a>

                                <?php if ($train['status'] === 'active'): ?>
                                    <form method="POST"
                                          action="train-deactivate.php"
                                          style="display:inline;"
                                          onsubmit="return confirm('Are you sure you want to deactivate this train?');">

                                        <input type="hidden" name="train_id"value="<?= e($train['train_id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn-deactivate">Deactivate</button>
                                    </form>
                                    
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>