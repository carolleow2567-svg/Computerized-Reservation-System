
<?php

session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/controllers/ScheduleController.php';

$controller = new ScheduleController($pdo);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$trains = $controller->getActiveTrains();
$stations = $controller->getActiveStations();

$errors = [];

$data = [
    'train_id' => '',
    'origin_station_id' => '',
    'destination_station_id' => '',
    'departure_datetime' => '',
    'arrival_datetime' => '',
    'base_fare' => '',
    'status' => 'scheduled'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'train_id' => $_POST['train_id'] ?? '',
        'origin_station_id' => $_POST['origin_station_id'] ?? '',
        'destination_station_id' => $_POST['destination_station_id'] ?? '',
        'departure_datetime' => $_POST['departure_datetime'] ?? '',
        'arrival_datetime' => $_POST['arrival_datetime'] ?? '',
        'base_fare' => $_POST['base_fare'] ?? '',
        'status' => $_POST['status'] ?? ''
    ];

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    }

    $trainId = filter_var($data['train_id'], FILTER_VALIDATE_INT);
    $originId = filter_var($data['origin_station_id'], FILTER_VALIDATE_INT);
    $destinationId = filter_var($data['destination_station_id'], FILTER_VALIDATE_INT);

    if (!$trainId || !$controller->isActiveTrain($trainId)) {
        $errors[] = 'Please select a valid active train.';
    }

    if (!$originId || !$controller->isActiveStation($originId)) {
        $errors[] = 'Please select a valid origin station.';
    }

    if (!$destinationId || !$controller->isActiveStation($destinationId)) {
        $errors[] = 'Please select a valid destination station.';
    }

    if ($originId && $destinationId && $originId === $destinationId) {
        $errors[] = 'Origin and destination must be different.';
    }

    try {
        $departure = new DateTime($data['departure_datetime']);
        $arrival = new DateTime($data['arrival_datetime']);

        if ($arrival <= $departure) {
            $errors[] = 'Arrival must be later than departure.';
        }
    } catch (Exception $e) {
        $errors[] = 'Please enter valid departure and arrival dates.';
    }

    if (!is_numeric($data['base_fare']) ||
        (float) $data['base_fare'] < 0) {
        $errors[] = 'Fare must be a valid non-negative amount.';
    }

    if (!in_array($data['status'], [
        'scheduled', 'delayed', 'cancelled', 'completed'
    ], true)) {
        $errors[] = 'Invalid schedule status.';
    }

    if (empty($errors)) {
        try {
            $data['departure_datetime'] = $departure->format('Y-m-d H:i:s');
            $data['arrival_datetime'] = $arrival->format('Y-m-d H:i:s');
            $data['base_fare'] = number_format((float) $data['base_fare'], 2, '.', '');

            $controller->create($data);

            $_SESSION['success'] = 'Schedule added successfully.';
            header('Location: schedules.php');
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Unable to add schedule. Please try again.';
        }
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../app/views/schedules/create.php';