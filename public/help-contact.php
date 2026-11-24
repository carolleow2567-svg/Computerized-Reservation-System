
<?php

// ==========================================
// CRS - Help & Contact Controller
// ==========================================

// Database connection
$pdo = require __DIR__ . '/../app/config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Form messages
$formMessage = '';
$formMessageType = '';

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (
        $fullname === '' ||
        $email === '' ||
        $subject === '' ||
        $message === ''
    ) {
        $formMessage = 'Please complete all required fields.';
        $formMessageType = 'error';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $formMessage = 'Please enter a valid email address.';
        $formMessageType = 'error';

    } else {

        try {
            // Get logged-in passenger ID, if available
            $userId = $_SESSION['user_id'] ?? null;

            $sql = "INSERT INTO enquiries
                    (user_id, fullname, email, subject, message)
                    VALUES
                    (:user_id, :fullname, :email, :subject, :message)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'user_id' => $userId,
                'fullname' => $fullname,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

            $formMessage = 'Your enquiry has been submitted successfully!';
            $formMessageType = 'success';

        } catch (PDOException $e) {

            $formMessage = 'Sorry, something went wrong. Please try again.';
            $formMessageType = 'error';
        }
    }
}

// Display Help & Contact page
require __DIR__ . '/../app/views/help/index.php';
