<?php
require_once "../db.php";

$errors = [];
$success = "";

$fullname = "";
$email = "";
$phone = "";
$identity = "";
$password = "";
$confirm_password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $identity = trim($_POST["identity"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $terms = isset($_POST["terms"]);

    $identity_clean = preg_replace('/[\s-]/', '', $identity);
    $clean_phone = preg_replace('/[\s-]/', '', $phone);

    if (strlen($fullname) < 3) {
        $errors["fullname"] = "Enter your full name (min. 3 characters).";
    }

    if (empty($email)) {
        $errors["email"] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address.";
    }

    if (empty($phone)) {
        $errors["phone"] = "Phone number is required.";
    } elseif (!preg_match('/^(?:\+?60|0)1[0-9]{8,9}$/', $clean_phone)) {
        $errors["phone"] = "Enter a valid Malaysian number.";
    }

    if (empty($identity_clean)) {
        $errors["identity"] = "IC or passport number is required.";
    } elseif (!preg_match('/^\d{12}$|^[A-Za-z0-9]{6,9}$/', $identity_clean)) {
        $errors["identity"] = "Enter a valid IC or passport number.";
    }

    if (empty($password)) {
        $errors["password"] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors["password"] = "Password must contain at least 8 characters.";
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors["password"] = "Password must contain a letter and a number.";
    }

    if (empty($confirm_password)) {
        $errors["confirm_password"] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors["confirm_password"] = "Passwords do not match.";
    }

    if (!$terms) {
        $errors["terms"] = "You must accept the terms.";
    }

    if (empty($errors)) {

        $check = mysqli_prepare(
            $conn,
            "SELECT email, identity FROM passengers WHERE email = ? OR identity = ?"
        );

        mysqli_stmt_bind_param($check, "ss", $email, $identity_clean);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        mysqli_stmt_bind_result($check, $existing_email, $existing_identity);

        while (mysqli_stmt_fetch($check)) {

            if ($existing_email === $email) {
                $errors["email"] = "This email is already registered.";
            }

            if ($existing_identity === $identity_clean) {
                $errors["identity"] = "This IC/passport is already registered.";
            }
        }

        mysqli_stmt_close($check);

        if (empty($errors)) {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO passengers
                    (fullname, email, phone, identity, password_hash)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                $errors["database"] = "Database query failed.";
            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssss",
                    $fullname,
                    $email,
                    $phone,
                    $identity_clean,
                    $hashed_password
                );

                if (mysqli_stmt_execute($stmt)) {

                    $success = "Registration successful! Your RailMY account has been created.";

                    $fullname = "";
                    $email = "";
                    $phone = "";
                    $identity = "";
                    $password = "";
                    $confirm_password = "";

                } else {
                    $errors["database"] = "Registration failed: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

function fieldError($field, $errors) {
    return $errors[$field] ?? "";
}

function fieldClass($field, $errors) {
    return isset($errors[$field]) ? "invalid" : "";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Passenger Registration - RailMY</title>

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="register-page">

<div class="register-layout">


    <section class="register-left">

        <div class="brand">
            <span class="brand-icon">🚆</span>
            <span>RailMY</span>
        </div>

        <div class="left-content">
            <h1>Create your passenger<br>account.</h1>

            <p>
                Save your details once, then book ETS journeys,
                view e-tickets and manage cancellations from one place.
            </p>
        </div>

    </section>

    <section class="register-right">

        <div class="register-card">

            <div class="form-header">
                <h2>Create account</h2>
                <p>Fields marked <span>*</span> are required.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-summary">
                    Please fix the highlighted fields before creating your account.
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" novalidate>

                <div class="form-group">

                    <label for="fullname">
                        Full name (as on IC / passport) <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        value="<?php echo htmlspecialchars($fullname); ?>"
                        class="<?php echo fieldClass('fullname', $errors); ?>"
                        placeholder="Enter your full name"
                    >

                    <small class="field-message">
                        <?php echo fieldError('fullname', $errors); ?>
                    </small>

                </div>

                <div class="form-group">

                    <label for="email">
                        Email address <span>*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        class="<?php echo fieldClass('email', $errors); ?>"
                        placeholder="example@email.com"
                    >

                    <small class="field-message">
                        <?php echo fieldError('email', $errors); ?>
                    </small>

                </div>

                <div class="form-group">

                    <label for="phone">
                        Phone number <span>*</span>
                    </label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        class="<?php echo fieldClass('phone', $errors); ?>"
                        placeholder="+60 12-345 6789"
                    >

                    <small class="field-message">
                        <?php echo fieldError('phone', $errors); ?>
                    </small>

                </div>

                <div class="form-group">

                    <label for="identity">
                        IC or passport number <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="identity"
                        name="identity"
                        value="<?php echo htmlspecialchars($identity); ?>"
                        class="<?php echo fieldClass('identity', $errors); ?>"
                        placeholder="990101-14-5566 or A12345678">

                    <small class="field-message">
                        <?php echo fieldError('identity', $errors); ?>
                    </small>

                    <small class="helper-text">
                        Use an NRIC (123456-12-1234) or a passport number (6-9 letters/digits).
                    </small>

                    <small class="helper-text">
                        Used only to match your tickets and boarding. Shown masked after registration.
                    </small>

                </div>

                <div class="form-group">

                    <label for="password">
                        Password <span>*</span>
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="<?php echo fieldClass('password', $errors); ?>"
                            placeholder="Enter your password"
                        >

                        <button type="button" class="show-password" onclick="togglePassword('password', this)">
                            Show
                        </button>

                    </div>

                    <div class="password-strength">

                        <div class="strength-bars">
                            <span id="bar1"></span>
                            <span id="bar2"></span>
                            <span id="bar3"></span>
                        </div>

                        <small id="strengthText">Strength: Enter a password</small>

                    </div>

                    <small class="field-message">
                        <?php echo fieldError('password', $errors); ?>
                    </small>

                    <small class="helper-text">
                        Use at least 8 characters with a letter and a number.
                    </small>

                </div>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm password <span>*</span>
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="<?php echo fieldClass('confirm_password', $errors); ?>"
                            placeholder="Re-enter your password">

                        <button type="button" class="show-password" onclick="togglePassword('confirm_password', this)">
                            Show
                        </button>

                    </div>

                    <small class="field-message" id="confirmMessage">
                        <?php echo fieldError('confirm_password', $errors); ?>
                    </small>

                </div>

                <div class="terms-group">

                    <label class="terms-label">

                        <input
                            type="checkbox"
                            name="terms"
                            id="terms"
                            <?php echo isset($_POST["terms"]) ? "checked" : ""; ?>>

                        <span>
                            I agree to the
                            <a href="#">Terms &amp; Conditions</a>
                            and
                            <a href="#">Privacy Notice</a>.
                            <span class="required">*</span>
                        </span>

                    </label>

                    <small class="field-message terms-error">
                        <?php echo fieldError('terms', $errors); ?>
                    </small>

                </div>

                <button type="submit" class="create-account-btn">
                    Create account
                </button>


            </form>

            <div class="login-link">

                Already have an account?

                <a href="../index.php">Back to sign in</a>

            </div>

        </div>

    </section>

</div>

<script>

function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        button.textContent = "Hide";
    } else {
        input.type = "password";
        button.textContent = "Show";
    }

}

const passwordInput = document.getElementById("password");

passwordInput.addEventListener("input", function () {

    const password = this.value;

    const bar1 = document.getElementById("bar1");
    const bar2 = document.getElementById("bar2");
    const bar3 = document.getElementById("bar3");

    const strengthText = document.getElementById("strengthText");

    // Reset
    bar1.className = "";
    bar2.className = "";
    bar3.className = "";

    if (password.length === 0) {
        strengthText.textContent = "Strength: Enter a password";
        return;
    }

    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Za-z]/.test(password) && /[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password) || password.length >= 12) strength++;

    if (strength === 1) {

        bar1.classList.add("weak");
        strengthText.textContent = "Strength: Weak";

    } else if (strength === 2) {

        bar1.classList.add("medium");
        bar2.classList.add("medium");
        strengthText.textContent = "Strength: Medium";

    } else if (strength >= 3) {

        bar1.classList.add("strong");
        bar2.classList.add("strong");
        bar3.classList.add("strong");
        strengthText.textContent = "Strength: Strong";

    }

});

const confirmInput = document.getElementById("confirm_password");

confirmInput.addEventListener("input", function () {

    const password = document.getElementById("password").value;
    const confirm = this.value;
    const message = document.getElementById("confirmMessage");

    if (confirm === "") {
        message.textContent = "";
    } else if (password !== confirm) {
        message.textContent = "Passwords do not match.";
    } else {
        message.textContent = "";
    }

});

</script>

</body>
</html>