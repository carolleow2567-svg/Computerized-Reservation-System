
<?php

// ==========================================
// CRS - Passenger Profile Controller
// ==========================================

// Database connection
$pdo = require __DIR__ . '/../app/config/database.php';

// Load M2 authentication helpers
require_once __DIR__ . '/../app/helpers/m2_helpers.php';

// Load Passenger model
require_once __DIR__ . '/../app/models/Passenger.php';

// Create Passenger object
$passengerModel = new Passenger($pdo);

// Verify passenger login and get passenger ID
$passengerId = require_passenger($pdo);

// Retrieve passenger information
$passenger = $passengerModel->findById($passengerId);

// Check whether passenger record exists
if (!$passenger) {
    http_response_code(404);
    exit('Passenger profile not found.');
}

// Display Passenger Profile view
require __DIR__ . '/../app/views/passengers/profile.php';

