<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_query = $conn->query("SELECT * FROM users WHERE ID = $admin_id");
$admin = $admin_query->fetch_assoc();

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_variations = $conn->query("SELECT COUNT(*) as count FROM productvariations")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(StockQuantity) as total FROM productvariations")->fetch_assoc()['total'];
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM productvariations WHERE StockQuantity < 10")->fetch_assoc()['count'];

$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders o JOIN trackings t ON o.OrderID = t.TrackingID WHERE t.DeliveryStatus = 'Processing'")->fetch_assoc()['count'];
$in_transit_orders = $conn->query("SELECT COUNT(*) as count FROM orders o JOIN trackings t ON o.OrderID = t.TrackingID WHERE t.DeliveryStatus = 'In Transit'")->fetch_assoc()['count'];
$delivered_orders = $conn->query("SELECT COUNT(*) as count FROM orders o JOIN trackings t ON o.OrderID = t.TrackingID WHERE t.DeliveryStatus = 'Delivered'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(TotalAmount) as total FROM orders o JOIN payments p ON o.OrderID = p.OrderID WHERE p.Status = 'Paid'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MechaKeys</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-orders.css">
    <link rel="stylesheet" href="../css/alert.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <?php include 'components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include 'components/header.php'; ?>

            <!-- Content -->
            <div class="content">
                <?php include 'components/dashboard.php'; ?>
                <?php include 'components/orders.php'; ?>
                <?php include 'components/products.php'; ?>
                <?php include 'components/inventory.php'; ?>
                <?php include 'components/add_product.php'; ?>
                <?php include 'components/delivery_riders.php'; ?>
            </div>
        </main>
    </div>

    <?php include 'components/modals.php'; ?>

    <script src="../js/alert.js"></script>
    <script src="components/scripts.js"></script>
    <script src="components/scripts.dashboard.js"></script>
    <script src="components/scripts.products.js"></script>
    <script src="components/scripts.inventory.js"></script>
    <script src="components/scripts.orders.js"></script>
    <script src="components/scripts.riders.js"></script>
</body>

</html>