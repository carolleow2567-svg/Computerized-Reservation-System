
<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController($pdo);

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $user = $authController->login($email, $password);

        if ($user !== null) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header('Location: home.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Passenger Login - CRS</title>

    <link rel="stylesheet" href="/Computerized-Reservation-System/public/assets/css/auth.css">
</head>

<body>

    <div class="login-layout">

        <!-- LEFT PANEL -->
        <section class="login-left">

            <h1>CRS<span>.</span></h1>

            <h2>Welcome to Computerized Reservation System</h2>

            <p>
                Your journey starts here.
                Log in to manage your train reservations
                and travel information.
            </p>

        </section>

        <!-- RIGHT PANEL -->
        <section class="login-right">

            <div class="login-card">

                <h2>Passenger Login</h2>

                <p>
                    Please enter your account details to continue.
                </p>

                <?php if ($error !== ''): ?>
                    <p class="login-error">
                        <?= htmlspecialchars($error) ?>
                    </p>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                    <button type="submit">Login</button>

                </form>

            </div>

        </section>

    </div>

</body>
</html>
