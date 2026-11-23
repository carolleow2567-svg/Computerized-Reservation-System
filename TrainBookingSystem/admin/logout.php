<?php
session_start();

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

// Validate logout CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    !isset($_SESSION["admin_logout_csrf"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["admin_logout_csrf"], $token)
) {
    http_response_code(403);
    exit("Invalid logout request.");
}

// Clear admin session
unset(
    $_SESSION["admin_id"],
    $_SESSION["admin_username"],
    $_SESSION["admin_logout_csrf"]
);

session_regenerate_id(true);

header("Location: login.php");
exit();
?>