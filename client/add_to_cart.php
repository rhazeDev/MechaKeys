<?php
session_start();
require_once '../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

$customer_id = $_SESSION['user_id'];

if (!isset($_POST['product_id']) || !isset($_POST['variation_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = intval($_POST['product_id']);
$variation_id = intval($_POST['variation_id']);
$quantity = intval($_POST['quantity']);

if ($product_id <= 0 || $variation_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$sql = "SELECT pv.StockQuantity, pv.Price, p.Brand, p.Model, pv.Layout, pv.SwitchType, pv.Color
        FROM ProductVariations pv
        JOIN Products p ON pv.ProductID = p.ProductID
        WHERE pv.VariationID = ? AND pv.ProductID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $variation_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product variation not found']);
    exit;
}

$variation = $result->fetch_assoc();
$available_stock = intval($variation['StockQuantity']);
$price = floatval($variation['Price']);

$sql_check = "SELECT CartID, Quantity FROM Carts WHERE CustomerID = ? AND ProductID = ? AND VariationID = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $customer_id, $product_id, $variation_id);
$stmt_check->execute();
$cart_result = $stmt_check->get_result();

if ($cart_result->num_rows > 0) {
    $cart_item = $cart_result->fetch_assoc();
    $new_quantity = $cart_item['Quantity'] + $quantity;

    if ($new_quantity > $available_stock) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot add to cart. Total quantity would exceed available stock (' . $available_stock . ')',
            'available_stock' => $available_stock,
            'current_in_cart' => $cart_item['Quantity']
        ]);
        exit;
    }

    $sql_update = "UPDATE Carts SET Quantity = ?, Price = ? WHERE CartID = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("idi", $new_quantity, $price, $cart_item['CartID']);

    if ($stmt_update->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully',
            'action' => 'updated',
            'new_quantity' => $new_quantity
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    if ($quantity > $available_stock) {
        echo json_encode([
            'success' => false,
            'message' => 'Requested quantity exceeds available stock (' . $available_stock . ')',
            'available_stock' => $available_stock
        ]);
        exit;
    }

    $sql_insert = "INSERT INTO Carts (CustomerID, ProductID, VariationID, Quantity, Price) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiiid", $customer_id, $product_id, $variation_id, $quantity, $price);

    if ($stmt_insert->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'action' => 'added'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
}

$conn->close();
?>