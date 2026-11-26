
<?php

session_start();

// Protect this page: passenger must be logged in
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'passenger'
) {
    header('Location: login.php');
    exit;
}

$fullName = $_SESSION['full_name'] ?? 'Passenger';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Passenger Home - CRS</title>

    <link rel="stylesheet" href="/Computerized-Reservation-System/public/assets/css/auth.css">
</head>

<body class="home-page">

    <!-- NAVIGATION BAR -->
    <nav class="home-navbar">

        <div class="home-brand">
            CRS<span>.</span>
        </div>

        <a href="logout.php" class="logout-btn">
            Logout
        </a>

    </nav>

    <!-- MAIN CONTENT -->
    <main class="home-container">

        <!-- WELCOME SECTION -->
        <section class="welcome-card">

            <h1>
                Welcome, <?= htmlspecialchars($fullName) ?>!
            </h1>

            <p>
                You are successfully logged in to the
                Computerized Reservation System.
            </p>

            <p>
                <strong>Ready for your next journey?</strong>
                Explore the passenger dashboard below.
            </p>

        </section>

        <!-- DASHBOARD -->
        <h2 class="dashboard-title">
            Passenger Dashboard
        </h2>

        <div class="dashboard-grid">

            <section class="dashboard-card">
                <h3>Train Schedules</h3>

                <p>
                    Check available train schedules
                    and plan your journey.
                </p>
            </section>

            <section class="dashboard-card">
                <h3>My Bookings</h3>

                <p>
                    Manage your train reservations
                    and booking information.
                </p>
            </section>

            <section class="dashboard-card">
                <h3>My Profile</h3>

                <p>
                    View your passenger account
                    and personal information.
                </p>
            </section>

        </div>

    </main>

    <!-- FOOTER -->
    <footer class="home-footer">

        <p>
            &copy; <?= date('Y') ?>
            Computerized Reservation System.
        </p>

    </footer>

</body>
</html>
