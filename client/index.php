<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <?php 
    $hasCategory = isset($_GET['category']) && !empty($_GET['category']);
    
    if (!$hasCategory): 
    ?>
        <?php include 'components/hero.php'; ?>
    <?php endif; ?>

    <main class="main-content">
        <?php if ($hasCategory): ?>
            <?php include 'components/all_products.php'; ?>
        <?php else: ?>
            <?php include 'components/collection.php'; ?>
            <?php include 'components/popular_products.php'; ?>
            <?php include 'components/brands.php'; ?>
        <?php endif; ?>
    </main>

    <?php // include 'components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        function addToCart(productId) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                if (typeof showAuthModal === 'function') {
                    showAuthModal('login');
                } else {
                    window.location.href = '../login.php';
                }
                return;
            }

            window.location.href = 'product.php?id=' + productId;
        }

        document.querySelectorAll('.btn-wishlist').forEach((btn, index) => {
            btn.addEventListener('click', function() {
                const productId = index + 1;
                addToWishlist(productId, this);
            });
        });

        document.querySelectorAll('.collection-card').forEach(card => {
            card.addEventListener('click', function() {
                alert('Navigating to collection...');
            });
        });
    </script>
</body>

</html>