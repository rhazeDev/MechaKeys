<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../conn.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $conn->prepare("SELECT FullName, Email, Contact, Address FROM users WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);

        $update_stmt = $conn->prepare("UPDATE users SET Contact = ?, Address = ? WHERE ID = ?");
        $update_stmt->bind_param("ssi", $contact, $address, $user_id);

        if ($update_stmt->execute()) {
            $message = 'Profile updated successfully!';
            $user['Contact'] = $contact;
            $user['Address'] = $address;

            if (!empty($contact) && !empty($address) && isset($_GET['from']) && $_GET['from'] === 'checkout') {
                header('Location: checkout.php');
                exit;
            }
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
        $update_stmt->close();
    }

    if ($_POST['action'] === 'change_password') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $pass_stmt = $conn->prepare("SELECT Password FROM users WHERE ID = ?");
        $pass_stmt->bind_param("i", $user_id);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $pass_data = $pass_result->fetch_assoc();
        $pass_stmt->close();

        if ($pass_data['Password'] !== $old_password) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $update_pass_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE ID = ?");
            $update_pass_stmt->bind_param("si", $new_password, $user_id);

            if ($update_pass_stmt->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
            $update_pass_stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/profile.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="profile-main">
        <div class="profile-container">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-user-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3 class="profile-username"><?php echo htmlspecialchars($user['FullName']); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($user['Email']); ?></p>
                </div>

                <nav class="profile-nav">
                    <a href="#profile-info" class="profile-nav-item active" data-tab="profile-info">
                        <i class="fas fa-user"></i>
                        <span>Profile Information</span>
                    </a>
                    <a href="#change-password" class="profile-nav-item" data-tab="change-password">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="cart.php" class="profile-nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>My Cart</span>
                    </a>
                    <a href="../logout.php" class="profile-nav-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="profile-content">
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Information Tab -->
                <div class="profile-section active" id="profile-info">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Profile Information
                        </h2>
                        <p class="section-subtitle">Update your personal information and contact details</p>
                    </div>

                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="fullname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" id="fullname" name="fullname" class="form-input"
                                    value="<?php echo htmlspecialchars($user['FullName']); ?>" readonly>
                                <small class="form-hint">Name cannot be changed</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input"
                                    value="<?php echo htmlspecialchars($user['Email']); ?>" readonly>
                                <small class="form-hint">Email cannot be changed</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="contact" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Number
                                </label>
                                <input type="text" id="contact" name="contact" class="form-input"
                                    value="<?php echo htmlspecialchars($user['Contact']); ?>"
                                    placeholder="Enter your contact number" required>
                            </div>

                            <div class="form-group full-width">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address
                                </label>
                                <textarea id="address" name="address" class="form-input form-textarea" rows="4"
                                    placeholder="Enter your complete address"
                                    required><?php echo htmlspecialchars($user['Address']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="profile-section" id="change-password">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-lock"></i>
                            Change Password
                        </h2>
                        <p class="section-subtitle">Ensure your account is using a strong password</p>
                    </div>

                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="old_password" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Current Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="old_password" name="old_password" class="form-input"
                                        placeholder="Enter your current password" required>
                                    <button type="button" class="toggle-password" data-target="old_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="new_password" name="new_password" class="form-input"
                                        placeholder="Enter your new password" minlength="6" required>
                                    <button type="button" class="toggle-password" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-hint">Must be at least 6 characters</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-check-circle"></i>
                                    Confirm New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="form-input" placeholder="Confirm your new password" minlength="6"
                                        required>
                                    <button type="button" class="toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll('.profile-nav-item[data-tab]').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = item.getAttribute('data-tab');

                document.querySelectorAll('.profile-nav-item').forEach(nav => nav.classList.remove('active'));
                document.querySelectorAll('.profile-section').forEach(section => section.classList.remove('active'));

                item.classList.add('active');
                document.getElementById(targetTab).classList.add('active');

                window.location.hash = targetTab;
            });
        });

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const targetNav = document.querySelector(`[data-tab="${hash}"]`);
                if (targetNav) {
                    targetNav.click();
                }
            }

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (profileDropdown && !profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>

</html>