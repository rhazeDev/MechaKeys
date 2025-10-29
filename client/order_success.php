<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

$customer_id = $_SESSION['user_id'];

$order_sql = "SELECT 
                o.OrderID,
                o.TotalAmount,
                o.PlaceOrdered,
                o.TrackingID,
                t.DeliveryStatus,
                p.Status as PaymentStatus,
                u.FullName,
                u.Email,
                u.Contact,
                u.Address
            FROM Orders o
            INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
            INNER JOIN Payments p ON o.PaymentID = p.PaymentID
            INNER JOIN users u ON o.CustomerID = u.ID
            WHERE o.OrderID = ? AND o.CustomerID = ?";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

$items_sql = "SELECT 
                oi.Quantity,
                oi.SubTotal,
                p.Brand,
                p.Model,
                pv.Layout,
                pv.SwitchType,
                pv.Color,
                pi.Path as ImagePath
            FROM OrderItems oi
            INNER JOIN Products p ON oi.ProductID = p.ProductID
            LEFT JOIN ProductVariations pv ON p.ProductID = pv.ProductID
            LEFT JOIN ProductImages pi ON p.ProductImageID = pi.ProductImageID
            WHERE oi.OrderID = ?
            GROUP BY oi.OrderItemID";

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $imagePath = $row['ImagePath'];
    if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
        $imagePath = substr($imagePath, strlen('mechakeys/'));
    }
    $row['ImagePath'] = $imagePath;
    $order_items[] = $row;
}

$items_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/order_success.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="main-content">
        <div class="success-container">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="success-title">Order Placed Successfully!</h1>
                <p class="success-message">Thank you for your order. We'll start processing it right away.</p>
            </div>

            <div class="order-info-card">
                <div class="order-header">
                    <div class="order-number">
                        <span class="label">Order Number</span>
                        <span class="value">#<?php echo str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="order-date">
                        <span class="label">Order Date</span>
                        <span class="value"><?php echo date('M d, Y', strtotime($order['PlaceOrdered'])); ?></span>
                    </div>
                    <div class="order-status">
                        <span class="label">Status</span>
                        <span
                            class="value status-badge"><?php echo htmlspecialchars($order['DeliveryStatus']); ?></span>
                    </div>
                </div>
            </div>

            <div class="order-details-grid">
                <div class="details-section">
                    <h2 class="section-title">
                        <i class="fas fa-box"></i>
                        Order Items
                    </h2>
                    <div class="items-list">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <img src="../<?php echo htmlspecialchars($item['ImagePath']); ?>"
                                    alt="<?php echo htmlspecialchars($item['Brand'] . ' ' . $item['Model']); ?>"
                                    class="item-image" onerror="this.src='../products/placeholder.png'">
                                <div class="item-info">
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($item['Brand'] . ' ' . $item['Model']); ?></div>
                                    <div class="item-specs">
                                        <?php echo htmlspecialchars($item['Color']); ?> •
                                        <?php echo htmlspecialchars($item['SwitchType']); ?> •
                                        <?php echo htmlspecialchars($item['Layout']); ?>%
                                    </div>
                                    <div class="item-quantity">Qty: <?php echo $item['Quantity']; ?></div>
                                </div>
                                <div class="item-price">
                                    ₱<?php echo number_format($item['SubTotal'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total">
                        <span>Total Amount</span>
                        <span class="total-value">₱<?php echo number_format($order['TotalAmount'], 2); ?></span>
                    </div>
                </div>

                <div class="details-sidebar">
                    <div class="info-card">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i>
                            Customer Information
                        </h3>
                        <div class="info-content">
                            <p><strong><?php echo htmlspecialchars($order['FullName']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order['Email']); ?></p>
                            <p><?php echo htmlspecialchars($order['Contact']); ?></p>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Delivery Address
                        </h3>
                        <div class="info-content">
                            <p><?php echo nl2br(htmlspecialchars($order['Address'])); ?></p>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card"></i>
                            Payment Method
                        </h3>
                        <div class="info-content">
                            <p><strong>Cash on Delivery (COD)</strong></p>
                            <p class="text-muted">Pay when you receive your order</p>
                        </div>
                    </div>       
                </div>
            </div>

            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
                <!-- <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Print Receipt
                </button> -->
            </div>
        </div>
    </main>

    <script>
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

            setTimeout(() => {
                document.querySelector('.checkmark-circle').classList.add('animate');
            }, 200);
        });
    </script>
</body>

</html>