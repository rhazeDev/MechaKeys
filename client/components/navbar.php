<?php
$isLoggedIn = isset($_SESSION['user_id']);
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            ⌨️ MechaKeys
        </a>
        
        <div class="navbar-search">
            <input type="text" class="search-input" placeholder="Search keyboards, brands...">
        </div>

        <div class="navbar-actions">
            <button class="navbar-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($isLoggedIn): ?>
                    <!-- <span class="navbar-badge">2</span> -->
                <?php endif; ?>
            </button>

            <div class="navbar-user">
                <?php if ($isLoggedIn): ?>
                    <div class="profile-dropdown">
                        <button class="navbar-icon profile-trigger">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <i class="fas fa-user-circle"></i>
                                <span class="user-email"><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : 'user@example.com'; ?></span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Sign out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="../../login.php" class="btn-login">Login</a>
                        <a href="../../register.php" class="btn-register">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
