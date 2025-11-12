<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_variations = $conn->query("SELECT COUNT(*) as count FROM productvariations")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(StockQuantity) as total FROM productvariations")->fetch_assoc()['total'];
$low_stock_count = $conn->query("SELECT COUNT(*) as count FROM productvariations WHERE StockQuantity < 10")->fetch_assoc()['count'];

$delivery_riders = $conn->query("SELECT COUNT(*) as count FROM users WHERE Role = 'delivery'")->fetch_assoc()['count'];
$active_deliveries = $conn->query("SELECT COUNT(*) as count FROM trackings WHERE DeliveryStatus NOT IN ('Delivered', 'Cancelled')")->fetch_assoc()['count'];
$delivered_today = $conn->query("SELECT COUNT(*) as count FROM trackings WHERE DeliveryStatus = 'Delivered' AND DATE(LastUpdated) = CURDATE()")->fetch_assoc()['count'];
$pending_assignments = $conn->query("SELECT COUNT(*) as count FROM trackings WHERE DeliveryPersonID = 0 OR DeliveryPersonID IS NULL")->fetch_assoc()['count'];

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

$order_status_query = "SELECT t.DeliveryStatus, COUNT(*) as count 
                      FROM trackings t 
                      GROUP BY t.DeliveryStatus";
$order_status_result = $conn->query($order_status_query);
$order_status_data = [];
while ($row = $order_status_result->fetch_assoc()) {
    $order_status_data[] = $row;
}

$revenue_query = "SELECT DATE(o.PlaceOrdered) as date, SUM(o.TotalAmount) as revenue
                 FROM orders o 
                 JOIN trackings t ON o.TrackingID = t.TrackingID
                 WHERE t.DeliveryStatus = 'Delivered' AND o.PlaceOrdered >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(o.PlaceOrdered)
                 ORDER BY DATE(o.PlaceOrdered)";
$revenue_result = $conn->query($revenue_query);
$revenue_data = [];
while ($row = $revenue_result->fetch_assoc()) {
    $revenue_data[] = $row;
}

$categories_query = "SELECT p.Category, COUNT(*) as count 
                    FROM products p 
                    GROUP BY p.Category";
$categories_result = $conn->query($categories_query);
$categories_data = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories_data[] = $row;
}

$stock_levels_query = "SELECT 
    SUM(CASE WHEN StockQuantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN StockQuantity > 0 AND StockQuantity < 10 THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN StockQuantity >= 10 THEN 1 ELSE 0 END) as good_stock
    FROM productvariations";
$stock_levels = $conn->query($stock_levels_query)->fetch_assoc();

echo json_encode([
    'success' => true,
    'stats' => [
        'total_products' => $total_products,
        'total_variations' => $total_variations,
        'total_stock' => $total_stock ?? 0,
        'low_stock_count' => $low_stock_count,
        'delivery_riders' => $delivery_riders,
        'active_deliveries' => $active_deliveries,
        'delivered_today' => $delivered_today,
        'pending_assignments' => $pending_assignments
    ],
    'products' => $products,
    'charts' => [
        'order_status' => $order_status_data,
        'revenue' => $revenue_data,
        'categories' => $categories_data,
        'stock_levels' => $stock_levels
    ]
]);

$conn->close();
?>