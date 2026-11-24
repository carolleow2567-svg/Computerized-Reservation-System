
<?php
// ==========================================
// CRS - Passenger Profile
// ==========================================

// Passenger data will be provided by the controller.
$passenger = $passenger ?? null;

// Escape output to prevent HTML injection.
$escape = static function ($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Passenger Profile | CRS</title>

    <!-- Passenger Profile CSS -->
    <link rel="stylesheet" href="assets/css/trains.css">
</head>

<body>

<div class="container">

    <!-- Page Header -->
    <div class="page-header">

        <div>
            <h1>Passenger Profile</h1>
            <p>View and manage your personal information.</p>
        </div>

        <a href="javascript:history.back()" class="btn-secondary">
            Back
        </a>

    </div>


    <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>

        <div class="success-message">
            Profile updated successfully!
        </div>

    <?php endif; ?>


    <?php if (!empty($_SESSION['success'])): ?>

        <div class="success-message">
            <?= $escape($_SESSION['success']) ?>
        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <?php if (!empty($_SESSION['error'])): ?>

        <div class="error-message">
            <?= $escape($_SESSION['error']) ?>
        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <?php if ($passenger): ?>

        <!-- Profile Information -->
        <div class="table-section">

            <div class="table-heading">
                <h2>Personal Information</h2>
                <span>Passenger Account Details</span>
            </div>

            <div class="form-section">

                <div class="form-group">
                    <label>Passenger ID</label>

                    <input
                        type="text"
                        value="<?= $escape($passenger['id'] ?? '') ?>"
                        readonly
                    >
                </div>


                <div class="form-group">
                    <label>Full Name</label>

                    <input
                        type="text"
                        value="<?= $escape($passenger['fullname'] ?? '') ?>"
                        readonly
                    >
                </div>


                <div class="form-group">
                    <label>Email Address</label>

                    <input
                        type="email"
                        value="<?= $escape($passenger['email'] ?? '') ?>"
                        readonly
                    >
                </div>


                <div class="form-group">
                    <label>Phone Number</label>

                    <input
                        type="text"
                        value="<?= $escape($passenger['phone'] ?? '') ?>"
                        readonly
                    >
                </div>


                <div class="form-group">
                    <label>Identity Number</label>

                    <input
                        type="text"
                        value="<?= $escape($passenger['identity'] ?? '') ?>"
                        readonly
                    >
                </div>


                <div class="form-group">
                    <label>Registration Date</label>

                    <input
                        type="text"
                        value="<?= $escape($passenger['created_at'] ?? '') ?>"
                        readonly
                    >
                </div>


                <!-- Account Information -->
                <div class="form-group">
                    <label>Account Type</label>

                    <input
                        type="text"
                        value="Passenger"
                        readonly
                    >
                </div>

            </div>


            <!-- Profile Actions -->
            <div class="form-actions">

                <a href="passenger-edit.php" class="btn-edit">
                    Edit Profile
                </a>

            </div>

        </div>

    <?php else: ?>

        <!-- No Passenger Information -->
        <div class="table-section">

            <div class="empty">
                <h3>Passenger Profile Not Available</h3>

                <p>
                    Unable to retrieve passenger information.
                    Please log in to your account and try again.
                </p>
            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
