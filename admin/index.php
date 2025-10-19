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

$products_query = "SELECT p.*, 
                   (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
                   (SELECT SUM(StockQuantity) FROM productvariations WHERE ProductID = p.ProductID) as TotalStock,
                   (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
                   (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice
                   FROM products p 
                   ORDER BY p.ProductID DESC";
$products = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MechaKeys</title>
    <link rel="stylesheet" href="../css/admin.css">
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
                <?php include 'components/products.php'; ?>
                <?php include 'components/inventory.php'; ?>
                <?php include 'components/add_product.php'; ?>
            </div>
        </main>
    </div>

    <?php include 'components/modals.php'; ?>

    <script src="components/scripts.js"></script>
</body>

</html>