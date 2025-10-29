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
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($order_id <= 0 || $tracking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order information']);
    exit;
}

if (!in_array($action, ['approve', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$conn->begin_transaction();

try {
    $get_order = $conn->prepare("SELECT CustomerID FROM orders WHERE OrderID = ?");
    $get_order->bind_param("i", $order_id);
    $get_order->execute();
    $result = $get_order->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order not found');
    }

    $order_data = $result->fetch_assoc();
    $customer_id = $order_data['CustomerID'];
    $get_order->close();

    if ($action === 'approve') {
        $new_status = 'Processing';
        $notification_message = "Your order #$order_id has been approved and is now being processed.";
    } else {
        $new_status = 'Cancelled';
        $notification_message = "Your order #$order_id has been cancelled.";

        $get_items = $conn->prepare("SELECT ProductID, VariationID, Quantity FROM orderitems WHERE OrderID = ?");
        $get_items->bind_param("i", $order_id);
        $get_items->execute();
        $items_result = $get_items->get_result();

        $restore_stock = $conn->prepare("UPDATE productvariations SET StockQuantity = StockQuantity + ? WHERE VariationID = ?");
        $restore_sold = $conn->prepare("UPDATE products SET TotalSold = TotalSold - ? WHERE ProductID = ?");

        while ($item = $items_result->fetch_assoc()) {
            $restore_stock->bind_param("ii", $item['Quantity'], $item['VariationID']);
            $restore_stock->execute();

            $restore_sold->bind_param("ii", $item['Quantity'], $item['ProductID']);
            $restore_sold->execute();
        }

        $restore_stock->close();
        $restore_sold->close();
        $get_items->close();
    }

    $update_tracking = $conn->prepare("UPDATE trackings SET DeliveryStatus = ?, LastUpdated = NOW() WHERE TrackingID = ?");
    $update_tracking->bind_param("si", $new_status, $tracking_id);

    if (!$update_tracking->execute()) {
        throw new Exception('Failed to update tracking status');
    }
    $update_tracking->close();

    $notification_title = $action === 'approve' ? "Order Approved" : "Order Cancelled";
    $notification_type = "order";
    $notification_status = "unread";

    $insert_notification = $conn->prepare("INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) VALUES (?, ?, ?, ?, ?, NOW())");
    $insert_notification->bind_param("issss", $customer_id, $notification_title, $notification_message, $notification_type, $notification_status);

    if (!$insert_notification->execute()) {
        throw new Exception('Failed to create notification');
    }
    $insert_notification->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $action === 'approve' ? 'Order approved successfully' : 'Order cancelled successfully',
        'new_status' => $new_status
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>