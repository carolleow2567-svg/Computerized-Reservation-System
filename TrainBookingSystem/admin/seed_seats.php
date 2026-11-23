
<?php
require_once "../db.php";

// Check whether seats already exist
$check = mysqli_query($conn, "SELECT COUNT(*) AS total FROM seats");
$row = mysqli_fetch_assoc($check);

if ($row["total"] > 0) {
    die("Seats already exist! Please do not run this file again.");
}

// Coach information
$coaches = [
    ["Coach C", "Gold"],
    ["Coach D", "Gold"],
    ["Coach E", "Gold"],
    ["Coach F", "Silver"],
    ["Coach G", "Silver"]
];

// Create 240 seat statuses
$statuses = array_merge(
    array_fill(0, 119, "Available"),
    array_fill(0, 72, "Occupied"),
    array_fill(0, 23, "Reserved"),
    array_fill(0, 26, "Unavailable")
);

shuffle($statuses);

$trainName = "ETS 23 - KL Sentral to Butterworth";
$trainNumber = "ETS23";

$sql = "INSERT INTO seats
        (train_name, train_number, coach, seat_number, seat_class, seat_status)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("SQL preparation failed: " . mysqli_error($conn));
}

mysqli_begin_transaction($conn);

$index = 0;

try {
    foreach ($coaches as $coach) {

        $coachName = $coach[0];
        $seatClass = $coach[1];

        foreach (range(1, 12) as $number) {

            foreach (["A", "B", "C", "D"] as $letter) {

                $seatNumber = $number . $letter;
                $status = $statuses[$index];

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssss",
                    $trainName,
                    $trainNumber,
                    $coachName,
                    $seatNumber,
                    $seatClass,
                    $status
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                $index++;
            }
        }
    }

    mysqli_commit($conn);

    echo "SUCCESS! 240 seats inserted successfully.";

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "Error inserting seats: " . $e->getMessage();
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
