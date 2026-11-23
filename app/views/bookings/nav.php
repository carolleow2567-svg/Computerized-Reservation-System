<?php
// Show the logged-in user's real name.
// Use the session value if M1 stored it, otherwise read it from the users table.
$navName = $_SESSION['full_name'] ?? '';

if ($navName === '' && !empty($_SESSION['user_id']) && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT full_name FROM users WHERE user_id = :id');
    $stmt->execute(['id' => (int)$_SESSION['user_id']]);
    $navName = (string)($stmt->fetchColumn() ?: '');
}

if ($navName === '') {
    $navName = 'Passenger';
}

$initials = strtoupper(substr($navName, 0, 1));
?>

<header class="topbar">
    <div style="display:flex;align-items:center;">
        <a href="index.php" class="brand">Rail<span>MY</span></a>
        <nav>
            <a href="index.php">Home</a>
            <a href="#">Search Trains</a>
            <a href="booking-history.php" class="active">My Bookings</a>
            <a href="#">Help</a>
        </nav>
    </div>
    <div class="user">
        <span class="avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
        <?= htmlspecialchars($navName, ENT_QUOTES, 'UTF-8') ?>
    </div>
</header>