<?php
$passenger = $passenger ?? [];

$e = static function ($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Passenger Profile | CRS</title>
    <link rel="stylesheet" href="assets/css/m5.css">
</head>
<body>

<div class="container">

    <div class="page-header">
        <h1>Edit Passenger Profile</h1>
        <p>Update your personal information and account password.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-message" role="alert"><?= $e($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-message" role="status"><?= $e($success) ?></div>
    <?php endif; ?>

    <div class="table-section">
        <div class="table-heading">
            <h2>Personal Information</h2>
        </div>

        <form method="POST" action="passenger-edit.php">
            <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">

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

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="passenger-profile.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="table-section">
        <div class="table-heading">
            <h2>Change Password</h2>
        </div>

        <form method="POST" action="passenger-edit.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    minlength="8"
                    required>
                <small>Use at least 8 characters.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    minlength="8"
                    required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Password</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
