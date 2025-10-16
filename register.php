<?php
session_start();
include 'conn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "⚠️ Please fill in all fields.";
        goto end_register;
    }

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
        goto end_register;
    }

    if (strlen($password) < 6) {
        $message = "❌ Password must be at least 6 characters long.";
        goto end_register;
    }

    $sql = "SELECT ID FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "❌ Email already registered.";
        $stmt->close();
        goto end_register;
    }

    $sql = "INSERT INTO users (FullName, Email, Password, Role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullname, $email, $password);

    if (!$stmt->execute()) {
        $message = "❌ Error creating account. Please try again.";
        $stmt->close();
        goto end_register;
    }

    $user_id = $stmt->insert_id;
    $stmt->close();

    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'customer';

    header("Location: /client/index.php");
    exit;

    end_register:
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Mechakeys</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <?php if (!empty($message)): ?>
                <div class="<?php echo strpos($message, '✅') !== false ? 'success-message' : 'error-message'; ?>">
                    <i class="fas <?php echo strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form">
                <div class="input-group">
                    <label for="fullname">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <div style="position: relative;">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="fullname" name="fullname" placeholder=" " required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <div style="position: relative;">
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" placeholder=" " required>
                    </div>
                </div>

                <div class="input-group password-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div style="position: relative;">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                </div>

                <div class="input-group password-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <div style="position: relative;">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    <i class="fas fa-user-plus"></i>
                    Sign up
                </button>
            </form>

            <div class="register-link">
                <p class="register-link-text">Already have an account?</p>
                <button type="button" class="register-button" onclick="window.location.href='login.php'">
                    Sign In
                </button>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>

</html>
