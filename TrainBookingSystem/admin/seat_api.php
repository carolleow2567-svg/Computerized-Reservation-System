
<?php
require_once "../db.php";

header("Content-Type: application/json");

$allowedStatuses = [
    "Available",
    "Occupied",
    "Reserved",
    "Unavailable"
];
//loading all seats

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $sql = "SELECT id, train_name, train_number,
            coach, seat_number, seat_class, seat_status
            FROM seats
            ORDER BY coach, seat_number";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to load seats."]);
        exit;
    }

    $seats = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $seats[] = $row;
    }

    echo json_encode([
        "success" => true,
        "seats" => $seats
    ]);

    exit;
}
//database
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

//updating status

    if ($action === "update_status") {

        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST["status"] ?? "";

        if (!$id || !in_array($status, $allowedStatuses, true)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid seat or status."]);
            exit;
        }

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE seats SET seat_status = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                "success" => true,
                "message" => "Seat status updated."
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Update failed."]);
        }

        mysqli_stmt_close($stmt);
        exit;
    }
//adding new coach

    if ($action === "add_coach") {

        $coachName = trim($_POST["name"] ?? "");
        $coachClass = trim($_POST["class"] ?? "");

        if (
            $coachName === "" ||
            strlen($coachName) > 20 ||
            !in_array($coachClass, ["Gold", "Silver"], true)
        ) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid coach details."]);
            exit;
        }

// Check duplicate coach
        $check = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) FROM seats WHERE train_number = 'ETS23' AND coach = ?"
        );

        mysqli_stmt_bind_param($check, "s", $coachName);
        mysqli_stmt_execute($check);
        mysqli_stmt_bind_result($check, $count);
        mysqli_stmt_fetch($check);
        mysqli_stmt_close($check);

        if ($count > 0) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "Coach already exists."]);
            exit;
        }

        $sql = "INSERT INTO seats
                (train_name, train_number, coach, seat_number, seat_class, seat_status)
                VALUES (?, 'ETS23', ?, ?, ?, 'Available')";

        $stmt = mysqli_prepare($conn, $sql);

        $trainName = "ETS 23 - KL Sentral to Butterworth";

        mysqli_begin_transaction($conn);

        try {

            foreach (range(1, 12) as $row) {

                foreach (["A", "B", "C", "D"] as $letter) {

                    $seatNumber = $row . $letter;

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssss",
                        $trainName,
                        $coachName,
                        $seatNumber,
                        $coachClass
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_stmt_error($stmt));
                    }
                }
            }

            mysqli_commit($conn);

            echo json_encode([
                "success" => true,
                "message" => "New coach added with 48 seats."
            ]);

        } catch (Exception $e) {

            mysqli_rollback($conn);

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "message" => "Failed to add coach."
            ]);
        }

        mysqli_stmt_close($stmt);
        exit;
    }

//editing the coach

    if ($action === "edit_coach") {

        $oldName = trim($_POST["old_name"] ?? "");
        $newName = trim($_POST["new_name"] ?? "");
        $newClass = trim($_POST["class"] ?? "");

        if (
            $oldName === "" ||
            $newName === "" ||
            strlen($newName) > 20 ||
            !in_array($newClass, ["Gold", "Silver"], true)
        ) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid coach details."]);
            exit;
        }

// Check duplicate name
        if ($oldName !== $newName) {

            $check = mysqli_prepare(
                $conn,
                "SELECT COUNT(*) FROM seats WHERE train_number = 'ETS23' AND coach = ?"
            );

            mysqli_stmt_bind_param($check, "s", $newName);
            mysqli_stmt_execute($check);
            mysqli_stmt_bind_result($check, $count);
            mysqli_stmt_fetch($check);
            mysqli_stmt_close($check);

            if ($count > 0) {
                http_response_code(409);
                echo json_encode(["success" => false, "message" => "Coach name already exists."]);
                exit;
            }
        }

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE seats SET coach = ?, seat_class = ?
             WHERE train_number = 'ETS23' AND coach = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $newName,
            $newClass,
            $oldName
        );

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {

            echo json_encode([
                "success" => true,
                "message" => "Coach updated successfully."
            ]);

        } else {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" => "Coach not found or no changes were made."
            ]);
        }

        mysqli_stmt_close($stmt);
        exit;
    }
}

//if invalid request occur

http_response_code(400);

echo json_encode([
    "success" => false,
    "message" => "Invalid request."
]);

mysqli_close($conn);
?>
