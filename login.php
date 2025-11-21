<?php
session_start();
include 'conn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === 'true';

    if (empty($email) || empty($password)) {
        $message = "⚠️ Please enter both email and password.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_login;
    }

    $sql = "SELECT ID, Email, Role, Password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows !== 1) {
        $message = "❌ Invalid email or password.";
        $stmt->close();
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_login;
    }

    $user = $result->fetch_assoc();
    $stored_password = $user['Password'];

    $is_hash = is_password_hash($stored_password);

    $login_ok = false;
    if ($is_hash && verify_password($password, $stored_password)) {
        $login_ok = true;
    } elseif ($stored_password === $password) {
        $login_ok = true;
        $new_hash = hash_password($password);
        $upd_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE ID = ?");
        $upd_stmt->bind_param("si", $new_hash, $user['ID']);
        $upd_stmt->execute();
        $upd_stmt->close();
        $stored_password = $new_hash;
    }

    if (!$login_ok) {
        $message = "❌ Invalid email or password.";
        $stmt->close();
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_login;
    }
    $_SESSION['user_id'] = $user['ID'];
    $_SESSION['email'] = $user['Email'];
    $_SESSION['role'] = $user['Role'];

    $stmt->close();

    $redirect_url = '/mechakeys/client/index.php';
    if ($user['Role'] === 'admin') {
        $redirect_url = '/mechakeys/admin/index.php';
    } elseif ($user['Role'] === 'delivery') {
        $redirect_url = '/mechakeys/delivery/index.php';
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => $redirect_url]);
        exit;
    }

    header("Location: $redirect_url");
    exit;

    end_login:
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Mechakeys</title>
    <link href="css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <?php if (!empty($message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="form">
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
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="register-link">
                <p class="register-link-text">Don't have an account?</p>
                <button type="button" class="register-button" onclick="window.location.href='register.php'">
                    Create Account
                </button>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

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