<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/**
 * Admin endpoint to manually trigger auto-marking of old delivered orders
 */

try {
    $auto_mark_sql = "SELECT 
                        o.OrderID,
                        o.TrackingID,
                        t.DeliveryStatus,
                        t.LastUpdated,
                        DATEDIFF(NOW(), t.LastUpdated) as days_since_delivery,
                        o.CustomerID,
                        u.FullName as CustomerName
                    FROM Orders o
                    INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
                    INNER JOIN users u ON o.CustomerID = u.ID
                    WHERE t.DeliveryStatus = 'Delivered' 
                    AND DATEDIFF(NOW(), t.LastUpdated) >= 7";

    $result = $conn->query($auto_mark_sql);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
        exit;
    }

    $orders_updated = 0;
    $orders_marked = [];

    while ($order = $result->fetch_assoc()) {
        $tracking_id = $order['TrackingID'];
        $order_id = $order['OrderID'];
        $customer_id = $order['CustomerID'];
        $customer_name = $order['CustomerName'];
        $days_since = $order['days_since_delivery'];

        $update_sql = "UPDATE Trackings 
                      SET DeliveryStatus = 'Order Received', 
                          LastUpdated = NOW()
                      WHERE TrackingID = ? AND DeliveryStatus = 'Delivered'";

        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $tracking_id);

        if ($update_stmt->execute()) {
            $orders_updated++;
            $orders_marked[] = [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'days_since_delivery' => $days_since
            ];

            $notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated)
                         VALUES (?, ?, ?, ?, ?, NOW())";

            $notif_stmt = $conn->prepare($notif_sql);
            $title = "Order Auto-Confirmed as Received";
            $message = "Your order #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . " was automatically marked as received after 7 days. If you haven't received it, please contact support.";
            $type = "order";
            $status = "unread";

            $notif_stmt->bind_param("issss", $customer_id, $title, $message, $type, $status);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        $update_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => "Auto-marked $orders_updated orders as received",
        'orders_marked' => $orders_marked,
        'count' => $orders_updated
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>