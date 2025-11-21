<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$customer_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$verify_sql = "SELECT o.OrderID, o.TrackingID, t.DeliveryStatus 
              FROM Orders o
              INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
              WHERE o.OrderID = ? AND o.CustomerID = ?";

$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $order_id, $customer_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found or does not belong to customer']);
    $verify_stmt->close();
    exit;
}

$order_data = $verify_result->fetch_assoc();
$tracking_id = $order_data['TrackingID'];
$verify_stmt->close();

$update_sql = "UPDATE Trackings 
              SET DeliveryStatus = 'Order Received', 
                  LastUpdated = NOW()
              WHERE TrackingID = ? AND DeliveryStatus = 'Delivered'";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $tracking_id);

if ($update_stmt->execute()) {
    $notification_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated)
                        VALUES (?, ?, ?, ?, ?, NOW())";

    $notif_stmt = $conn->prepare($notification_sql);
    $title = "Order Received Confirmation";
    $message = "Customer confirmed receipt for order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . ".";
    $type = "order";
    $status = "unread";

    $notif_stmt->bind_param("issss", $customer_id, $title, $message, $type, $status);
    $notif_stmt->execute();
    $notif_stmt->close();

    $update_stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Order marked as received']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
    $update_stmt->close();
    exit;
}
?>