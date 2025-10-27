<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT FullName, Email, Contact, Address FROM users WHERE ID = ?");
$user_stmt->bind_param("i", $customer_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$has_contact = !empty($user['Contact']);
$has_address = !empty($user['Address']);
$profile_incomplete = !$has_contact || !$has_address;

$cart_sql = "SELECT 
                c.CartID,
                c.ProductID,
                c.VariationID,
                c.Quantity,
                c.Price,
                p.Brand,
                p.Model,
                pv.Layout,
                pv.SwitchType,
                pv.Color,
                pv.StockQuantity,
                pi.Path as ImagePath
            FROM Carts c
            INNER JOIN Products p ON c.ProductID = p.ProductID
            INNER JOIN ProductVariations pv ON c.VariationID = pv.VariationID
            LEFT JOIN ProductImages pi ON p.ProductImageID = pi.ProductImageID
            WHERE c.CustomerID = ?
            GROUP BY c.CartID
            ORDER BY c.DateCreated DESC";

$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$cart_items = [];
$subtotal = 0;
$has_stock_issues = false;

while ($row = $cart_result->fetch_assoc()) {
    $item_total = $row['Price'] * $row['Quantity'];
    $is_out_of_stock = $row['StockQuantity'] < $row['Quantity'];
    
    if ($is_out_of_stock) {
        $has_stock_issues = true;
    }
    
        $imagePath = $row['ImagePath'];
    if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
        $imagePath = substr($imagePath, strlen('mechakeys/'));
    }
    
    $cart_items[] = [
        'cart_id' => $row['CartID'],
        'product_id' => $row['ProductID'],
        'variation_id' => $row['VariationID'],
        'product_name' => $row['Brand'] . ' ' . $row['Model'],
        'layout' => $row['Layout'],
        'switch_type' => $row['SwitchType'],
        'color' => $row['Color'],
        'quantity' => $row['Quantity'],
        'price' => $row['Price'],
        'item_total' => $item_total,
        'stock_quantity' => $row['StockQuantity'],
        'is_out_of_stock' => $is_out_of_stock,
        'image' => $imagePath
    ];
    
    if (!$is_out_of_stock) {
        $subtotal += $item_total;
    }
}

$cart_stmt->close();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

if ($has_stock_issues) {
    $_SESSION['checkout_error'] = 'Some items in your cart are out of stock. Please update your cart.';
    header('Location: cart.php');
    exit;
}

$shipping_fee = 0; $total = $subtotal + $shipping_fee;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/checkout.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="main-content">
        <div class="checkout-container">
            <div class="checkout-header">
                <h1><i class="fas fa-shopping-bag"></i> Checkout</h1>
                <div class="checkout-steps">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <div class="step-label">Review Order</div>
                    </div>
                    <div class="step-divider"></div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-label">Place Order</div>
                    </div>
                    <div class="step-divider"></div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-label">Order Confirmed</div>
                    </div>
                </div>
            </div>

            <?php if ($profile_incomplete): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Complete Your Profile</strong>
                        <p>Please add your contact number and delivery address before placing an order.</p>
                        <a href="profile.php" class="btn-link">Go to Profile <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                <!-- Customer Details Section -->
                <div class="checkout-main">
                    <div class="checkout-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Customer Information
                        </h2>
                        <div class="customer-info-card">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-user-circle"></i> Full Name
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($user['FullName']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i> Email
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($user['Email']); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-phone"></i> Contact Number
                                </div>
                                <div class="info-value <?php echo !$has_contact ? 'missing' : ''; ?>">
                                    <?php echo $has_contact ? htmlspecialchars($user['Contact']) : 'Not provided'; ?>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-map-marker-alt"></i> Delivery Address
                                </div>
                                <div class="info-value <?php echo !$has_address ? 'missing' : ''; ?>">
                                    <?php echo $has_address ? nl2br(htmlspecialchars($user['Address'])) : 'Not provided'; ?>
                                </div>
                            </div>
                            <?php if ($profile_incomplete): ?>
                                <div class="info-actions">
                                    <a href="profile.php" class="btn-edit">
                                        <i class="fas fa-edit"></i> Update Information
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Items Section -->
                    <div class="checkout-section">
                        <h2 class="section-title">
                            <i class="fas fa-box"></i>
                            Order Items (<?php echo count($cart_items); ?>)
                        </h2>
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                         class="item-image"
                                         onerror="this.src='../products/placeholder.png'">
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="item-variations">
                                            <span><i class="fas fa-palette"></i> <?php echo htmlspecialchars($item['color']); ?></span>
                                            <span><i class="fas fa-keyboard"></i> <?php echo htmlspecialchars($item['switch_type']); ?></span>
                                            <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($item['layout']); ?>%</span>
                                        </div>
                                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="item-price">
                                        ₱<?php echo number_format($item['item_total'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="checkout-section">
                        <h2 class="section-title">
                            <i class="fas fa-credit-card"></i>
                            Payment Method
                        </h2>
                        <div class="payment-methods">
                            <div class="payment-option selected">
                                <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                <label for="cod">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">Cash on Delivery</div>
                                        <div class="payment-desc">Pay when you receive your order</div>
                                    </div>
                                    <div class="payment-check">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="payment-note">
                            <i class="fas fa-info-circle"></i>
                            Please prepare exact amount for faster transaction.
                        </div>
                    </div>

                    <!-- Order Notes Section -->
                    <div class="checkout-section">
                        <h2 class="section-title">
                            <i class="fas fa-comment-alt"></i>
                            Order Notes (Optional)
                        </h2>
                        <textarea 
                            id="order_notes" 
                            class="order-notes-input" 
                            placeholder="Add any special instructions for your order (e.g., preferred delivery time, landmark, etc.)"
                            rows="4"
                        ></textarea>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="checkout-sidebar">
                    <div class="order-summary-card">
                        <h2 class="summary-title">Order Summary</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?php echo count($cart_items); ?> items)</span>
                            <span>₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping Fee</span>
                            <span class="text-success">
                                <?php echo $shipping_fee > 0 ? '₱' . number_format($shipping_fee, 2) : 'FREE'; ?>
                            </span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-total">
                            <span>Total Amount</span>
                            <span>₱<?php echo number_format($total, 2); ?></span>
                        </div>

                        <div class="summary-note">
                            <i class="fas fa-truck"></i>
                            Estimated delivery: 3-5 business days
                        </div>

                        <button 
                            class="btn-place-order" 
                            onclick="placeOrder()"
                            <?php echo $profile_incomplete ? 'disabled' : ''; ?>
                        >
                            <i class="fas fa-check-circle"></i>
                            Place Order
                        </button>

                        <a href="cart.php" class="btn-back-cart">
                            <i class="fas fa-arrow-left"></i>
                            Back to Cart
                        </a>

                        <div class="security-badges">
                            <div class="badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure Checkout</span>
                            </div>
                            <div class="badge">
                                <i class="fas fa-lock"></i>
                                <span>Safe Payment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
                    if (profileDropdown && !profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        function placeOrder() {
                        <?php if ($profile_incomplete): ?>
                alert('Please complete your profile information before placing an order.');
                window.location.href = 'profile.php';
                return;
            <?php endif; ?>

                        const btn = document.querySelector('.btn-place-order');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                        const orderNotes = document.getElementById('order_notes').value.trim();

                        const formData = new FormData();
            formData.append('payment_method', 'cod');
            formData.append('order_notes', orderNotes);

                        fetch('process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'order_success.php?order_id=' + data.order_id;
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to place order. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
            });
        }
    </script>
</body>

</html>
