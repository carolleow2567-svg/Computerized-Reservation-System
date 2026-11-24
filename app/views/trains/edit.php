<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Train | CRS Admin</title>
    <link rel="stylesheet" href="assets/css/trains.css?v=<?= (int) @filemtime(__DIR__ . '/../../../public/assets/css/trains.css') ?>">
</head>

<body>

<?php
$adminActive = 'trains';
$adminCrumb  = 'Edit Train';
require __DIR__ . '/../partials/admin_start.php';
?>

    <header class="page-header">
        <div>
            <h1>Edit Train</h1>
            <p>Update train information.</p>
        </div>

        <a href="trains.php" class="btn-edit">Back to Trains</a>
    </header>

    <section class="table-section">

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Train Number</label>
                <input type="text"
                       name="train_number"
                       maxlength="20"
                       required
                       value="<?= e($data['train_number']) ?>">
            </div>

            <div class="form-group">
                <label>Train Name</label>
                <input type="text"
                       name="train_name"
                       maxlength="100"
                       required
                       value="<?= e($data['train_name']) ?>">
            </div>

            <div class="form-group">
                <label>Train Type</label>
                <input type="text"
                       name="train_type"
                       maxlength="50"
                       required
                       value="<?= e($data['train_type']) ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="active"
                        <?= $data['status'] === 'active' ? 'selected' : '' ?>>
                        Active
                    </option>

                    <option value="inactive"
                        <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>
                        Inactive
                    </option>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                Update Train
            </button>

        </form>

    </section>

<?php require __DIR__ . '/../partials/admin_end.php'; ?>

</body>
</html>