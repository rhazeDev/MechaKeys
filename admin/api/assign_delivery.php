<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$tracking_id = isset($_POST['tracking_id']) ? intval($_POST['tracking_id']) : 0;
$delivery_rider_id = isset($_POST['delivery_rider_id']) ? intval($_POST['delivery_rider_id']) : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($order_id <= 0 || $tracking_id <= 0 || $delivery_rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order or rider information']);
    exit;
}

$rider_check = $conn->prepare("SELECT ID, FullName FROM users WHERE ID = ? AND Role = 'delivery'");
$rider_check->bind_param("i", $delivery_rider_id);
$rider_check->execute();
$rider_result = $rider_check->get_result();

if ($rider_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery rider']);
    exit;
}

$rider = $rider_result->fetch_assoc();
$rider_check->close();

$update_tracking = $conn->prepare("UPDATE trackings SET 
                                    DeliveryPersonID = ?, 
                                    DeliveryStatus = 'Assigned',
                                    LastUpdated = NOW() 
                                    WHERE TrackingID = ?");
$update_tracking->bind_param("ii", $delivery_rider_id, $tracking_id);

if ($update_tracking->execute()) {
    $customer_query = $conn->prepare("SELECT CustomerID FROM orders WHERE OrderID = ?");
    $customer_query->bind_param("i", $order_id);
    $customer_query->execute();
    $customer_result = $customer_query->get_result();
    $customer = $customer_result->fetch_assoc();
    $customer_id = $customer['CustomerID'];
    $customer_query->close();

    $notification_title = "Order Assigned to Delivery Rider";
    $notification_message = "Your order #$order_id has been assigned to " . $rider['FullName'] . " for delivery.";
    $notification_type = "order";
    $notification_status = "unread";

    $insert_notification = $conn->prepare("INSERT INTO notifications (CustomerID, Title, Message, Type, Status) VALUES (?, ?, ?, ?, ?)");
    $insert_notification->bind_param("issss", $customer_id, $notification_title, $notification_message, $notification_type, $notification_status);
    $insert_notification->execute();
    $insert_notification->close();

    $update_tracking->close();

    echo json_encode([
        'success' => true,
        'message' => 'Order assigned to ' . $rider['FullName'] . ' successfully',
        'rider_name' => $rider['FullName']
    ]);
} else {
    $update_tracking->close();
    echo json_encode(['success' => false, 'message' => 'Failed to assign delivery rider: ' . $conn->error]);
}

$conn->close();
?>