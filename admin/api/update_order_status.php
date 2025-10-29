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
$delivery_status = isset($_POST['delivery_status']) ? trim($_POST['delivery_status']) : '';
$payment_status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : '';

if ($order_id <= 0 || $tracking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order information']);
    exit;
}

$valid_delivery_statuses = ['Pending', 'Processing', 'Assigned', 'In Transit', 'Delivered', 'Cancelled'];
$valid_payment_statuses = ['Pending', 'Paid', 'Failed', 'Refunded'];

if (!in_array($delivery_status, $valid_delivery_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery status']);
    exit;
}

if (!in_array($payment_status, $valid_payment_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
    exit;
}

$conn->begin_transaction();

try {
    $update_tracking = $conn->prepare("UPDATE trackings SET DeliveryStatus = ?, LastUpdated = NOW() WHERE TrackingID = ?");
    $update_tracking->bind_param("si", $delivery_status, $tracking_id);

    if (!$update_tracking->execute()) {
        throw new Exception('Failed to update tracking status');
    }
    $update_tracking->close();

    $get_payment = $conn->prepare("SELECT PaymentID, CustomerID FROM orders WHERE OrderID = ?");
    $get_payment->bind_param("i", $order_id);
    $get_payment->execute();
    $result = $get_payment->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order not found');
    }

    $order_data = $result->fetch_assoc();
    $payment_id = $order_data['PaymentID'];
    $customer_id = $order_data['CustomerID'];
    $get_payment->close();

    $update_payment = $conn->prepare("UPDATE payments SET Status = ? WHERE PaymentID = ?");
    $update_payment->bind_param("si", $payment_status, $payment_id);

    if (!$update_payment->execute()) {
        throw new Exception('Failed to update payment status');
    }
    $update_payment->close();

    $notification_title = "Order Status Updated";
    $notification_message = "Your order #$order_id has been updated. Delivery Status: $delivery_status, Payment Status: $payment_status";
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
        'message' => 'Order status updated successfully',
        'delivery_status' => $delivery_status,
        'payment_status' => $payment_status
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>