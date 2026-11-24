<?php
/** Receives the seats chosen on seat-selection.php, holds them, then goes to M3's page. */
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/m2_helpers.php';
require_once __DIR__ . '/../app/controllers/SeatController.php';

$userId = require_passenger($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: train-search.php');
    exit;
}
csrf_check();

$ctl = new SeatController($pdo);
header('Location: ' . $ctl->hold($_POST, $userId));
exit;
