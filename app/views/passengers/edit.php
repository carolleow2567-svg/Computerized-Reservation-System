
<?php
$passenger = $passenger ?? [];

$e = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Passenger Profile | CRS</title>
    <link rel="stylesheet" href="assets/css/trains.css">
</head>
<body>

<div class="container">

    <div class="page-header">
        <h1>Edit Passenger Profile</h1>
        <p>Update your personal information.</p>
    </div>

    <div class="form-section">

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?= $e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="passenger-edit.php">

            <input type="hidden" name="id" value="<?= $e($passenger['id'] ?? '') ?>">

            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    value="<?= $e($passenger['fullname'] ?? '') ?>"
                    required
                    maxlength="100">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= $e($passenger['email'] ?? '') ?>"
                    required
                    maxlength="100">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="<?= $e($passenger['phone'] ?? '') ?>"
                    maxlength="20">
            </div>

            <div class="form-group">
                <label for="identity">Identity Number</label>
                <input
                    type="text"
                    id="identity"
                    name="identity"
                    value="<?= $e($passenger['identity'] ?? '') ?>"
                    maxlength="30">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>

                <a href="passenger-profile.php" class="btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>
