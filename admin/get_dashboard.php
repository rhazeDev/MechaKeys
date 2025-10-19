<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Fetch dashboard statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_variations = $conn->query("SELECT COUNT(*) as count FROM productvariations")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(StockQuantity) as total FROM productvariations")->fetch_assoc()['total'];
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM productvariations WHERE StockQuantity < 10")->fetch_assoc()['count'];

// Fetch recent products (limit to 10)
$products_query = "SELECT p.*, 
                   (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
                   (SELECT SUM(StockQuantity) FROM productvariations WHERE ProductID = p.ProductID) as TotalStock,
                   (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
                   (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice
                   FROM products p 
                   ORDER BY p.ProductID DESC
                   LIMIT 10";
$products_result = $conn->query($products_query);

$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total_products' => $total_products,
        'total_variations' => $total_variations,
        'total_stock' => $total_stock ?? 0,
        'low_stock_count' => $low_stock_count
    ],
    'products' => $products
]);

$conn->close();
?>
