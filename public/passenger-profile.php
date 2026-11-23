
<?php

// Database connection
$pdo = require __DIR__ . '/../app/config/database.php';

// Load Passenger model
require_once __DIR__ . '/../app/models/Passenger.php';

// Create Passenger object
$passengerModel = new Passenger($pdo);

// Temporary test passenger ID
$passenger = $passengerModel->findById(1);

// Check passenger record
if (!$passenger) {
    die('Passenger not found.');
}

// Display profile page
require __DIR__ . '/../app/views/passengers/profile.php';

