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
                o.PlaceOrdered,
                t.DeliveryStatus,
                t.LastUpdated,
                DATEDIFF(NOW(), DATE(t.LastUpdated)) as DaysSinceDelivery
            FROM Orders o
            INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
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

$is_delivered = $order['DeliveryStatus'] === 'Delivered';
$days_since_delivery = (int) $order['DaysSinceDelivery'];
$is_within_return_window = $days_since_delivery <= 7;
$can_return = $is_delivered && $is_within_return_window;
$days_remaining = max(0, 7 - $days_since_delivery);

$return_check_sql = "SELECT ReturnID, Status FROM returns WHERE OrderID = ? AND CustomerID = ?";
$return_check_stmt = $conn->prepare($return_check_sql);
$return_check_stmt->bind_param("ii", $order_id, $customer_id);
$return_check_stmt->execute();
$return_check_result = $return_check_stmt->get_result();
$existing_return = $return_check_result->fetch_assoc();
$return_check_stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'can_return' => $can_return,
    'is_delivered' => $is_delivered,
    'is_within_return_window' => $is_within_return_window,
    'days_since_delivery' => $days_since_delivery,
    'days_remaining' => $days_remaining,
    'has_existing_return' => $existing_return !== null,
    'return_status' => $existing_return['Status'] ?? null,
    'delivery_date' => $order['LastUpdated'],
    'message' => $can_return
        ? "You can return this order (${days_remaining} days remaining)"
        : ($is_delivered ? 'Return window has expired (7 days from delivery)' : 'Order not yet delivered')
]);
?>