<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE t.DeliveryStatus = '" . $conn->real_escape_string($status_filter) . "'";
}

$orders_query = "SELECT 
                    o.OrderID,
                    o.CustomerID,
                    o.TrackingID,
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
                    p.PaymentID,
                    COUNT(oi.OrderItemID) as ItemCount,
                    delivery_person.FullName as DeliveryPersonName
                FROM orders o
                INNER JOIN users u ON o.CustomerID = u.ID
                INNER JOIN trackings t ON o.TrackingID = t.TrackingID
                INNER JOIN payments p ON o.PaymentID = p.PaymentID
                LEFT JOIN orderitems oi ON o.OrderID = oi.OrderID
                LEFT JOIN users delivery_person ON t.DeliveryPersonID = delivery_person.ID
                $where_clause
                GROUP BY o.OrderID
                ORDER BY o.PlaceOrdered DESC";

$orders_result = $conn->query($orders_query);

if (!$orders_result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
    exit;
}

$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

$stats_query = "SELECT 
                    SUM(CASE WHEN t.DeliveryStatus = 'Processing' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN t.DeliveryStatus = 'Assigned' THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN t.DeliveryStatus IN ('Shipped', 'In Transit') THEN 1 ELSE 0 END) as shipped,
                    SUM(CASE WHEN t.DeliveryStatus = 'Delivered' THEN 1 ELSE 0 END) as delivered
                FROM orders o
                INNER JOIN trackings t ON o.TrackingID = t.TrackingID";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$riders_query = "SELECT ID, FullName, Contact FROM users WHERE Role = 'delivery' ORDER BY FullName ASC";
$riders_result = $conn->query($riders_query);

$riders = [];
while ($row = $riders_result->fetch_assoc()) {
    $riders[] = $row;
}

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'stats' => $stats,
    'riders' => $riders
]);
?>