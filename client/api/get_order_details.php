<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$customer_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$order_sql = "SELECT 
                o.OrderID,
                o.TotalAmount,
                o.PlaceOrdered,
                o.TrackingID,
                t.DeliveryStatus,
                t.DeliveryPersonID,
                dp.FullName as DeliveryPersonName,
                p.Status as PaymentStatus,
                u.Address
            FROM Orders o
            INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
            INNER JOIN Payments p ON o.PaymentID = p.PaymentID
            INNER JOIN users u ON o.CustomerID = u.ID
            LEFT JOIN users dp ON t.DeliveryPersonID = dp.ID AND dp.Role = 'delivery'
            WHERE o.OrderID = ? AND o.CustomerID = ?";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
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
            LEFT JOIN ProductVariations pv ON oi.VariationID = pv.VariationID
            LEFT JOIN ProductImages pi ON p.ProductImageID = pi.ProductImageID
            WHERE oi.OrderID = ?";

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

    $order_items[] = [
        'product_name' => $row['Brand'] . ' ' . $row['Model'],
        'layout' => $row['Layout'],
        'switch_type' => $row['SwitchType'],
        'color' => $row['Color'],
        'quantity' => $row['Quantity'],
        'subtotal' => $row['SubTotal'],
        'image' => $imagePath
    ];
}

$items_stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'order' => [
        'order_id' => $order['OrderID'],
        'total_amount' => $order['TotalAmount'],
        'placed_date' => $order['PlaceOrdered'],
        'delivery_status' => $order['DeliveryStatus'],
        'payment_status' => $order['PaymentStatus'],
        'order_reference' => 'MK-' . str_pad($order['TrackingID'], 6, '0', STR_PAD_LEFT),
        'delivery_person' => $order['DeliveryPersonName'] ?? 'Not Assigned',
        'address' => $order['Address']
    ],
    'items' => $order_items
]);
?>