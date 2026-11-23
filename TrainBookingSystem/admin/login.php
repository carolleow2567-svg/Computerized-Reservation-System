
<?php
session_start();
require_once "../db.php";

$error = "";

// Generate CSRF token
if (empty($_SESSION["admin_login_csrf"])) {
    $_SESSION["admin_login_csrf"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validate CSRF token
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["admin_login_csrf"], $token)
    ) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        // Find admin account
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password_hash FROM admins WHERE username = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin["password_hash"])) {

            // Create secure session
            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];

            // Remove login CSRF token
            unset($_SESSION["admin_login_csrf"]);

            header("Location: manage_seats.php");
            exit();

        } else {
            $error = "Incorrect username or password.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | RailMY</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
                background:
            linear-gradient(
            rgba(10, 35, 75, 0.65),
            rgba(10, 35, 75, 0.80)
            ),
            url("train.jpg");
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-box {
            background: white;
            padding: 35px;
            width: 350px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #14213d;
        }

        p {
            text-align: center;
            color: #64748b;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 25px;
            background: #14213d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #ffa51f;
        }

        .error {
            color: #dc2626;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>RailMY Admin</h2>
    <p>Sign in to manage seat inventory</p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo htmlspecialchars($_SESSION["admin_login_csrf"]); ?>"
        >

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>
