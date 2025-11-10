<?php
$isLoggedIn = isset($_SESSION['user_id']);

$cartCount = 0;
$userCoins = 0;
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

    $coins_stmt = $conn->prepare("SELECT Coins FROM users WHERE ID = ?");
    $coins_stmt->bind_param("i", $customer_id);
    $coins_stmt->execute();
    $coins_result = $coins_stmt->get_result();
    $coins_row = $coins_result->fetch_assoc();
    $userCoins = $coins_row['Coins'] ?? 0;
    $coins_stmt->close();
}
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">
            <img src="images/logo.png" width="250px" height="40px">
        </a>

        <div class="navbar-search" style="display:flex;align-items:center;gap:8px;">
            <input id="site-search" type="text" class="search-input" placeholder="Search keyboards, brands..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button id="site-search-btn" class="btn-primary" type="button" style="padding:10px 12px;font-size:14px;"
                aria-label="Search">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
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
                                <span
                                    class="user-email"><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : 'user@example.com'; ?></span>
                            </div>
                            <?php if ($userCoins > 0): ?>
                                <div class="dropdown-coins" title="Available coins for discounts">
                                    <i class="fas fa-coins"></i>
                                    <span class="coins-value">₱<?php echo number_format($userCoins, 2); ?></span>
                                </div>
                            <?php endif; ?>
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
                    <a href="index.php"
                        class="category-link <?php echo (!isset($_GET['category']) || $_GET['category'] == '') ? 'active' : ''; ?>">
                        <span>All Products</span>
                    </a>
                    <a href="index.php?category=keyboard"
                        class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'keyboard') ? 'active' : ''; ?>">
                        <span>Keyboards</span>
                    </a>
                    <a href="index.php?category=switches"
                        class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'switches') ? 'active' : ''; ?>">
                        <span>Switches</span>
                    </a>
                    <a href="index.php?category=keycaps"
                        class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'keycaps') ? 'active' : ''; ?>">
                        <span>Keycaps</span>
                    </a>
                    <a href="index.php?category=accessories"
                        class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == 'accessories') ? 'active' : ''; ?>">
                        <span>Accessories</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</nav>

<?php include __DIR__ . '/auth_modal.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('site-search');
        if (!searchInput) return;

        function navigateWithSearch(q) {
            const url = new URL(window.location.href);
            const params = url.searchParams;
            if (q && q.length > 0) {
                params.set('search', q);
            } else {
                params.delete('search');
            }
            const target = window.location.pathname.split('/').pop() || 'index.php';
            const qs = params.toString();
            window.location.href = target + (qs ? ('?' + qs) : '');
        }

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                navigateWithSearch(this.value.trim());
            }
        });

        const searchBtn = document.getElementById('site-search-btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', function () {
                navigateWithSearch(searchInput.value.trim());
            });
        }
    });
</script>