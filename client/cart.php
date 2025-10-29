<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/cart.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="main-content">
        <div class="cart-container">
            <div class="cart-header">
                <h1>Your cart</h1>
            </div>

            <div id="cartContent" class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading cart...
            </div>
        </div>
    </main>

    <script>
        let cartData = null;

        document.addEventListener('DOMContentLoaded', function () {
            loadCart();

            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (!profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        });

        function loadCart() {
            fetch('api/get_cart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartData = data;
                        renderCart(data);
                    } else {
                        showError('Failed to load cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to load cart. Please refresh the page.');
                });
        }

        function renderCart(data) {
            const container = document.getElementById('cartContent');

            if (data.cart_items.length === 0) {
                container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Your cart is empty</h2>
                        <p>Add some amazing mechanical keyboards to your cart!</p>
                        <a href="index.php" class="btn-continue">
                            Continue Shopping
                        </a>
                    </div>
                `;
                return;
            }

            let hasIssues = data.has_issues;
            let issuesHTML = '';

            if (hasIssues) {
                issuesHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention:</strong> Some items in your cart have stock issues. Disabled items cannot be checked out.
                    </div>
                `;
            }

            let itemsHTML = data.cart_items.map(item => {
                let itemClass = item.is_disabled ? 'disabled' : '';

                return `
                    <div class="cart-item ${itemClass}" data-cart-id="${item.cart_id}">
                        <img src="../${item.image}" alt="${item.product_name}" class="cart-item-image" 
                             onerror="this.src='../products/placeholder.png'">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.product_name}</div>
                            <div class="cart-item-variations">
                                <div class="cart-item-variation"><strong>Color:</strong> ${item.color}</div>
                                <div class="cart-item-variation"><strong>Switch:</strong> ${item.switch_type}</div>
                                <div class="cart-item-variation"><strong>Keyboard Version:</strong> ${item.layout}</div>
                            </div>
                            <div class="cart-item-actions">
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity - 1}, ${item.stock_quantity})" 
                                            ${item.quantity <= 1 || item.is_disabled ? 'disabled' : ''}>
                                        −
                                    </button>
                                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.stock_quantity}" 
                                           onchange="updateQuantity(${item.cart_id}, this.value, ${item.stock_quantity})"
                                           ${item.is_disabled ? 'disabled' : ''}>
                                    <button class="qty-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity + 1}, ${item.stock_quantity})" 
                                            ${item.quantity >= item.stock_quantity || item.is_disabled ? 'disabled' : ''}>
                                        +
                                    </button>
                                </div>
                                <button class="btn-remove" onclick="removeItem(${item.cart_id})" title="Remove item">
                                    <i class="fas fa-trash-alt" id="delete-cart-item"></i>
                                </button>
                            </div>
                        </div>
                        <div class="cart-item-price">₱${item.price.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `
                ${issuesHTML}
                <div class="cart-layout">
                    <div class="cart-items">
                        ${itemsHTML}
                    </div>
                    <div class="cart-summary">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₱${data.total_amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                        </div>
                        <div class="summary-total">
                            <span>Total:</span>
                            <span>₱${data.total_amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                        </div>
                        <button class="btn-checkout" onclick="checkout()" ${hasIssues ? 'disabled' : ''}>
                            Checkout
                        </button>
                        <a href="index.php" class="btn-continue">
                            <i class="fas fa-arrow-left"></i>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>
            `;
        }

        function updateQuantity(cartId, newQuantity, maxStock) {
            newQuantity = parseInt(newQuantity);

            if (newQuantity < 1) {
                if (confirm('Remove this item from cart?')) {
                    removeItem(cartId);
                }
                return;
            }

            if (newQuantity > maxStock) {
                alert(`Cannot add more than ${maxStock} items (available stock limit)`);
                loadCart();
                return;
            }

            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', newQuantity);

            fetch('api/update_cart.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                    } else {
                        alert('Error: ' + data.message);
                        loadCart();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update cart. Please try again.');
                    loadCart();
                });
        }

        function removeItem(cartId) {
            if (!confirm('Are you sure you want to remove this item?')) {
                return;
            }

            const formData = new FormData();
            formData.append('cart_id', cartId);

            fetch('api/remove_from_cart.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove item. Please try again.');
                });
        }

        function checkout() {
            if (cartData && cartData.has_issues) {
                alert('Please resolve stock issues before checkout');
                return;
            }

            window.location.href = 'checkout.php';
        }

        function showError(message) {
            document.getElementById('cartContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                </div>
            `;
        }
    </script>
</body>

</html>