
<?php

session_start();

// Only logged-in passengers can access this page
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'passenger'
) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passenger Home - CRS</title>
</head>
<body>

    <h1>Welcome to Computerized Reservation System!</h1>

    <p>
        Hello,
        <?= htmlspecialchars($_SESSION['full_name']) ?>!
    </p>

    <p>You are successfully logged in as a passenger.</p>

    <h2>Passenger Dashboard</h2>

    <ul>
        <li>View Train Schedules</li>
        <li>Manage Your Bookings</li>
        <li>View Your Profile</li>
    </ul>

    <a href="logout.php">Logout</a>

</body>
</html>
