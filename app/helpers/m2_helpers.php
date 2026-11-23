<?php
require_once __DIR__ . '/../config/m2.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

/** Escape text before printing it in HTML (XSS protection). */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Uses the same session key as the rest of the project: csrf_token */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(403);
        exit('Invalid or expired form. Please go back, refresh the page and try again.');
    }
}

/**
 * Passenger-only guard. M1's login must set:
 *   $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['role'] = 'passenger'
 */
function require_passenger(PDO $pdo): int
{
    if (empty($_SESSION['user_id']) && DEV_LOGIN) {
        $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE email = ? AND role = 'passenger'");
        $stmt->execute([DEV_EMAIL]);
        $demo = $stmt->fetch();
        if (!$demo) {
            exit('Demo passenger not found. Import database/04_m2_demo_data.sql first.');
        }
        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $demo['user_id'];
        $_SESSION['user_name'] = $demo['full_name'];
        $_SESSION['role']      = 'passenger';
    }
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'passenger') {
        header('Location: ' . URL_LOGIN);
        exit;
    }
    return (int) $_SESSION['user_id'];
}

function duration_text(int $mins): string
{
    return intdiv($mins, 60) . 'h ' . str_pad((string) ($mins % 60), 2, '0', STR_PAD_LEFT) . 'm';
}

function money($amount): string
{
    return 'RM ' . number_format((float) $amount, 2);
}
