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

        <div class="navbar-search" style="display:flex;align-items:center;gap:8px;position:relative;">
            <input id="site-search" type="text" class="search-input" placeholder="Search keyboards, brands..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                autocomplete="off">
            <button id="site-search-btn" class="btn-primary" type="button" style="padding:10px 12px;font-size:14px;"
                aria-label="Search">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
            <div id="search-suggestions" class="search-suggestions"
                style="display:none; position:absolute; left:0; right:0; top:calc(100% + 6px); background:#fff; border:1px solid #ddd; z-index:1000; max-height:360px; overflow:auto; box-shadow:0 6px 12px rgba(0,0,0,0.08);">
                <!-- populated dynamically -->
            </div>
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
        const suggestionsBox = document.getElementById('search-suggestions');
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
            clearSuggestions();
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

        let debounceTimer = null;
        function debounce(func, wait) {
            return function (...args) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function clearSuggestions() {
            if (!suggestionsBox) return;
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }

        function showSuggestions(items) {
            if (!suggestionsBox) return;
            suggestionsBox.innerHTML = '';
            if (!items || items.length === 0) {
                clearSuggestions();
                return;
            }
            items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'search-suggestion-item';
                div.style.padding = '10px';
                div.style.display = 'flex';
                div.style.gap = '10px';
                div.style.alignItems = 'center';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #f1f1f1';
                div.innerHTML = `<div style="width:48px;height:48px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:#f7f7f7;border-radius:6px;">
                        ${item.image ? `<img src="${item.image}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">` : '<span style="font-size:18px">⌨️</span>'}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(item.name)}</div>
                        <div style="font-size:12px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(item.specs)} • ${escapeHtml(item.price)}</div>
                    </div>`;
                div.addEventListener('click', function (ev) {
                    ev.stopPropagation();
                    window.location.href = 'product.php?id=' + item.id;
                });
                suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = 'block';
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"'`]/g, function (s) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '`': '&#96;'
                })[s];
            });
        }

        async function fetchSuggestions(query) {
            if (!query || query.length < 2) {
                clearSuggestions();
                return;
            }
            try {
                let url = new URL('api/get_products_clean.php', window.location.href);
                const params = url.searchParams;
                params.set('search', query);
                url.search = params.toString();
                const res = await fetch(url.toString());
                if (!res.ok) {
                    clearSuggestions();
                    return;
                }
                const data = await res.json();

                if (data.success) {
                    showSuggestions(data.products.slice(0, 6));
                } else {
                    clearSuggestions();
                }
            } catch (err) {
                clearSuggestions();
            }
        }

        const debouncedFetch = debounce(function () {
            fetchSuggestions(searchInput.value.trim());
        }, 300);

        searchInput.addEventListener('input', function () {
            debouncedFetch();
        });

        document.addEventListener('click', function (e) {
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                clearSuggestions();
            }
        });
    });
</script>