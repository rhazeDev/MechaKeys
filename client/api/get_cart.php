<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to view cart']);
    exit;
}

$customer_id = $_SESSION['user_id'];

$sql = "SELECT 
            c.CartID,
            c.ProductID,
            c.VariationID,
            c.Quantity as CartQuantity,
            c.Price as CartPrice,
            c.DateCreated,
            p.Brand,
            p.Model,
            pv.Layout,
            pv.SwitchType,
            pv.Color,
            pv.Price as CurrentPrice,
            pv.StockQuantity,
            (SELECT Path FROM ProductImages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            CASE 
                WHEN pv.StockQuantity = 0 THEN 'out_of_stock'
                WHEN c.Quantity > pv.StockQuantity THEN 'exceeds_stock'
                ELSE 'available'
            END as StockStatus
        FROM Carts c
        JOIN Products p ON c.ProductID = p.ProductID
        JOIN ProductVariations pv ON c.VariationID = pv.VariationID
        WHERE c.CustomerID = ?
        ORDER BY c.DateCreated DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_amount = 0;
$has_issues = false;

while ($row = $result->fetch_assoc()) {
    $imagePath = $row['ImagePath'];
    if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
        $imagePath = substr($imagePath, strlen('mechakeys/'));
    }

    $stock_status = $row['StockStatus'];
    $is_available = $stock_status === 'available';

    if ($stock_status !== 'available') {
        $has_issues = true;
    }

    $subtotal = $is_available ? ($row['CartQuantity'] * $row['CurrentPrice']) : 0;
    if ($is_available) {
        $total_amount += $subtotal;
    }

    $cart_items[] = [
        'cart_id' => $row['CartID'],
        'product_id' => $row['ProductID'],
        'variation_id' => $row['VariationID'],
        'product_name' => $row['Brand'] . ' ' . $row['Model'],
        'layout' => $row['Layout'] . '%',
        'switch_type' => $row['SwitchType'],
        'color' => $row['Color'],
        'quantity' => $row['CartQuantity'],
        'price' => floatval($row['CurrentPrice']),
        'subtotal' => $subtotal,
        'stock_quantity' => intval($row['StockQuantity']),
        'stock_status' => $stock_status,
        'image' => $imagePath,
        'date_added' => $row['DateCreated'],
        'is_disabled' => !$is_available
    ];
}

echo json_encode([
    'success' => true,
    'cart_items' => $cart_items,
    'total_items' => count($cart_items),
    'total_amount' => $total_amount,
    'has_issues' => $has_issues
]);

$conn->close();
?>