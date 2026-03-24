<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_code = trim($_POST["code"]);

    if (!isset($_SESSION["verification_code"])) {
        $error = "Session expired. Please register again.";
    } elseif ($input_code == $_SESSION["verification_code"]) {

        $name = $_SESSION["reg_name"];
        $email = $_SESSION["reg_email"];
        $password = $_SESSION["reg_password"];
        $code = $_SESSION["verification_code"];

        $query = "INSERT INTO users (full_name, email, password, role, is_verified, verification_code)
                  VALUES ('$name', '$email', '$password', 'customer', 1, '$code')";

        if (mysqli_query($conn, $query)) {

            session_unset();
            session_destroy();

            header("Location: login.php");
            exit();

        } else {
            $error = "Failed to create account.";
        }

    } else {
        $error = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify | Cafe Cruise</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-card">

        <div class="login-side">
            <img src="../assets/logo/logo.jpg">
        </div>

        <div class="login-form">
            <div class="login-logo">
                <h2>Email Verification</h2>
                <p>Enter the code sent to your email</p>
            </div>

            <?php if (!empty($error)) : ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Verification Code</label>
                    <input type="text" name="code" required>
                </div>

                <button type="submit" class="login-btn">Verify</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>