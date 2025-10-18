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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <?php include 'components/hero.php'; ?>

    <main class="main-content">
        <?php include 'components/collection.php'; ?>
        <?php include 'components/popular_products.php'; ?>
        <?php include 'components/brands.php'; ?>
    </main>

    <?php include 'components/footer.php'; ?>

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
                window.location.href = '../login.php';
                return;
            }

            alert('awan pay kas');
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