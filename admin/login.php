<?php
session_start();
include(__DIR__ . "/../includes/dbConnect.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $password = trim($_POST["password"]);

    $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Cafe Cruise</title>
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
                    <h2>Admin Login</h2>
                    <p>Login to access the dashboard</p>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleBtn = document.querySelector(".toggle-password");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                passwordInput.type = "password";
                toggleBtn.textContent = "Show";
            }
        }
    </script>

</body>
</html>