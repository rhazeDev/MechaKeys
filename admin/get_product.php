<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$product_id = intval($_GET['id']);

// Get product details
$product_query = "SELECT ProductID, Brand, Model, Description, Category FROM products WHERE ProductID = $product_id";
$product_result = $conn->query($product_query);

if ($product_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$product = $product_result->fetch_assoc();

// Get variations
$variations_query = "SELECT VariationID, Layout, SwitchType, Color, Price, StockQuantity 
                     FROM productvariations 
                     WHERE ProductID = $product_id
                     ORDER BY VariationID";
$variations_result = $conn->query($variations_query);

$variations = [];
while ($row = $variations_result->fetch_assoc()) {
    $variations[] = $row;
}

$product['variations'] = $variations;

echo json_encode([
    'success' => true,
    'product' => $product
]);

$conn->close();
?>
