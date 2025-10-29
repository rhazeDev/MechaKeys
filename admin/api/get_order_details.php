<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$order_query = "SELECT 
                    o.OrderID,
                    o.CustomerID,
                    o.TrackingID,
                    o.PaymentID,
                    o.TotalAmount,
                    o.PlaceOrdered,
                    u.FullName as CustomerName,
                    u.Email as CustomerEmail,
                    u.Contact as CustomerContact,
                    u.Address as CustomerAddress,
                    t.DeliveryStatus,
                    t.LastUpdated,
                    t.DeliveryPersonID,
                    p.Status as PaymentStatus,
                    p.Amount as PaymentAmount,
                    delivery_person.FullName as DeliveryPersonName,
                    delivery_person.Contact as DeliveryPersonContact
                FROM orders o
                INNER JOIN users u ON o.CustomerID = u.ID
                INNER JOIN trackings t ON o.TrackingID = t.TrackingID
                INNER JOIN payments p ON o.PaymentID = p.PaymentID
                LEFT JOIN users delivery_person ON t.DeliveryPersonID = delivery_person.ID
                WHERE o.OrderID = ?";

$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

$items_query = "SELECT 
                    oi.OrderItemID,
                    oi.ProductID,
                    oi.Quantity,
                    oi.SubTotal,
                    p.Brand,
                    p.Model,
                    pv.Color,
                    pv.SwitchType,
                    pv.Layout,
                    pv.Price,
                    (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath
                FROM orderitems oi
                INNER JOIN products p ON oi.ProductID = p.ProductID
                LEFT JOIN productvariations pv ON oi.ProductID = pv.ProductID
                WHERE oi.OrderID = ?
                GROUP BY oi.OrderItemID";

$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$items_stmt->close();

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);

$conn->close();
?>