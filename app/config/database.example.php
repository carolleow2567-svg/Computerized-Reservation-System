<?php

// ==========================================
// CRS - Database Configuration
// ==========================================

$host = 'localhost';
$dbname = 'computerized_reservation_system';
$username = 'root';
$password = '000000'; // Default XAMPP password, if unchanged

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log('CRS Database Connection Error: ' . $e->getMessage());

    throw new RuntimeException(
        'Unable to connect to the CRS database.'
    );
}

return $pdo;