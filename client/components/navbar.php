<?php
$isLoggedIn = isset($_SESSION['user_id']);

$cartCount = 0;
if ($isLoggedIn) {
    require_once __DIR__ . '/../../conn.php';
    $customer_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Carts WHERE CustomerID = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cartCount = $row['count'];
    $stmt->close();
}
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            <img src="images/logo.png" width="250px" height="40px">
        </a>
        
        <div class="navbar-search">
            <input type="text" class="search-input" placeholder="Search keyboards, brands...">
        </div>

        <div class="navbar-actions">
            <?php if ($isLoggedIn): ?>
                <a href="cart.php" class="navbar-icon" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="navbar-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <button class="navbar-icon" onclick="showAuthModal('login')">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            <?php endif; ?>

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
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="orders.php" class="dropdown-item">
                                <i class="fas fa-box"></i> Orders
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Sign out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <button type="button" class="btn-login" onclick="showAuthModal('login')">Login</button>
                        <button type="button" class="btn-register" onclick="showAuthModal('register')">Register</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    if ($currentPage === 'index.php'):
    ?>
    <div class="navbar-menu">
        <div class="navbar-container">
            <div class="category-nav">
                <a href="index.php" class="category-link <?php echo (!isset($_GET['category']) || $_GET['category'] == '') ? 'active' : ''; ?>">
                    <span>All Products</span>
                </a>
                <a href="index.php?category=keyboard" class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'keyboard') ? 'active' : ''; ?>">
                    <span>Keyboards</span>
                </a>
                <a href="index.php?category=switches" class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'switches') ? 'active' : ''; ?>">
                    <span>Switches</span>
                </a>
                <a href="index.php?category=keycaps" class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'keycaps') ? 'active' : ''; ?>">
                    <span>Keycaps</span>
                </a>
                <a href="index.php?category=accessories" class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'accessories') ? 'active' : ''; ?>">
                    <span>Accessories</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</nav>

<?php include __DIR__ . '/auth_modal.php'; ?>
