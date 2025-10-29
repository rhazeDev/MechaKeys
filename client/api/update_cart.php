<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

$customer_id = $_SESSION['user_id'];

if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);

if ($cart_id <= 0 || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if ($quantity === 0) {
    $sql_delete = "DELETE FROM Carts WHERE CartID = ? AND CustomerID = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $cart_id, $customer_id);

    if ($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart', 'action' => 'removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
    }
    exit;
}

$sql = "SELECT c.VariationID, pv.StockQuantity, pv.Price
        FROM Carts c
        JOIN ProductVariations pv ON c.VariationID = pv.VariationID
        WHERE c.CartID = ? AND c.CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

$cart_item = $result->fetch_assoc();
$available_stock = intval($cart_item['StockQuantity']);
$price = floatval($cart_item['Price']);

if ($quantity > $available_stock) {
    echo json_encode([
        'success' => false,
        'message' => 'Requested quantity exceeds available stock (' . $available_stock . ')',
        'available_stock' => $available_stock
    ]);
    exit;
}

$sql_update = "UPDATE Carts SET Quantity = ?, Price = ? WHERE CartID = ? AND CustomerID = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("idii", $quantity, $price, $cart_id, $customer_id);

if ($stmt_update->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully',
        'new_quantity' => $quantity
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}

$conn->close();
?>