<?php
session_start();
include 'conn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === 'true';

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "⚠️ Please fill in all fields.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if (strlen($password) < 8 || strlen($password) > 20) {
        $message = "❌ Password must be between 8 and 20 characters long.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $message = "❌ Password must contain at least one uppercase letter.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if (!preg_match('/[a-z]/', $password)) {
        $message = "❌ Password must contain at least one lowercase letter.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if (!preg_match('/[0-9]/', $password)) {
        $message = "❌ Password must contain at least one number.";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $message = "❌ Password must contain at least one special character (!@#$%^&*(),.?\":{}|<>).";
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
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
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    $hashed_password = hash_password($password);

    $sql = "INSERT INTO users (FullName, Email, Password, Role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);

    if (!$stmt->execute()) {
        $message = "❌ Error creating account. Please try again.";
        $stmt->close();
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        goto end_register;
    }

    $user_id = $stmt->insert_id;
    $stmt->close();

    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'customer';

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => '/mechakeys/client/index.php']);
        exit;
    }

    header("Location: /mechakeys/client/index.php");
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
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <?php if (!empty($message)): ?>
                <div class="<?php echo strpos($message, '✅') !== false ? 'success-message' : 'error-message'; ?>">
                    <i
                        class="fas <?php echo strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
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
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                        Must be 8-20 characters with uppercase, lowercase, number, and special character
                    </small>
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
                        <button type="button" class="password-toggle"
                            onclick="togglePassword('confirm_password', 'toggleIcon2')">
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

        document.querySelector('form').addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password.length < 8 || password.length > 20) {
                e.preventDefault();
                alert('❌ Password must be between 8 and 20 characters long.');
                return false;
            }

            if (!/[A-Z]/.test(password)) {
                e.preventDefault();
                alert('❌ Password must contain at least one uppercase letter.');
                return false;
            }

            if (!/[a-z]/.test(password)) {
                e.preventDefault();
                alert('❌ Password must contain at least one lowercase letter.');
                return false;
            }

            if (!/[0-9]/.test(password)) {
                e.preventDefault();
                alert('❌ Password must contain at least one number.');
                return false;
            }

            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                e.preventDefault();
                alert('❌ Password must contain at least one special character (!@#$%^&*(),.?":{}|<>).');
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('❌ Passwords do not match.');
                return false;
            }
        });
    </script>
</body>

</html>