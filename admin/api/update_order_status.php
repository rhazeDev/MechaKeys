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
$delivery_person_id = isset($_POST['delivery_person_id']) ? intval($_POST['delivery_person_id']) : 0;

if ($order_id <= 0 || $tracking_id <= 0 || $delivery_person_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order or delivery person information']);
    exit;
}

$valid_delivery_statuses = ['Processing', 'Assigned', 'In Transit', 'Delivered', 'Cancelled'];
$valid_payment_statuses = ['Pending', 'Paid', 'Failed', 'Refunded'];

$conn->begin_transaction();

try {
    $verify_rider = $conn->prepare("SELECT ID, FullName FROM users WHERE ID = ? AND Role = 'delivery'");
    $verify_rider->bind_param("i", $delivery_person_id);
    $verify_rider->execute();
    $rider_result = $verify_rider->get_result();

    if ($rider_result->num_rows === 0) {
        throw new Exception('Invalid delivery person');
    }

    $rider = $rider_result->fetch_assoc();
    $verify_rider->close();

    $delivery_status = 'Assigned';
    $update_tracking = $conn->prepare("UPDATE trackings SET DeliveryStatus = ?, DeliveryPersonID = ?, LastUpdated = NOW() WHERE TrackingID = ?");
    $update_tracking->bind_param("sii", $delivery_status, $delivery_person_id, $tracking_id);

    if (!$update_tracking->execute()) {
        throw new Exception('Failed to update tracking status');
    }
    $update_tracking->close();

    $customer_query = $conn->prepare("SELECT CustomerID FROM orders WHERE OrderID = ?");
    $customer_query->bind_param("i", $order_id);
    $customer_query->execute();
    $customer_result = $customer_query->get_result();
    $customer = $customer_result->fetch_assoc();
    $customer_id = $customer['CustomerID'];
    $customer_query->close();

    $notification_title = "Delivery Rider Assigned";
    $notification_message = "Your order #$order_id has been assigned to " . $rider['FullName'] . " for delivery. Status: Assigned";
    $notification_type = "order";
    $notification_status = "unread";

    $insert_notification = $conn->prepare("INSERT INTO notifications (CustomerID, Title, Message, Type, Status) VALUES (?, ?, ?, ?, ?)");
    $insert_notification->bind_param("issss", $customer_id, $notification_title, $notification_message, $notification_type, $notification_status);

    if (!$insert_notification->execute()) {
        throw new Exception('Failed to create notification');
    }
    $insert_notification->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'delivery_status' => 'Assigned',
        'rider_name' => $rider['FullName']
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>