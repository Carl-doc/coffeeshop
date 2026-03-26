<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");
include(__DIR__ . "/../includes/send_email.php");

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = mysqli_real_escape_string($conn, trim($_POST["full_name"]));
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {

        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered.";
        } else {

            $code = rand(100000, 999999);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // SAVE TEMP DATA IN SESSION
            $_SESSION["reg_name"] = $full_name;
            $_SESSION["reg_email"] = $email;
            $_SESSION["reg_password"] = $hashed_password;
            $_SESSION["verification_code"] = $code;

            // SEND EMAIL
            if (sendVerificationEmail($email, $code)) {
                header("Location: verify.php");
                exit();
            } else {
                $error = "Failed to send verification email.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Cafe Cruise</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-card">

            <div class="login-side">
                <img src="../assets/logo/logo.jpg" alt="Cafe Cruise Logo">
            </div>

            <div class="login-form">
                <div class="login-logo">
                    <h2>Register</h2>
                    <p>Create your customer account</p>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($message)) : ?>
                    <div class="success-message"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" id="full_name" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>

                    <button type="submit" class="login-btn">Register</button>
                </form>

                <p class="login-footer">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
            </div>

        </div>
    </div>

</body>
</html>