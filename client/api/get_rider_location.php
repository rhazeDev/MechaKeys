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

$verify_sql = "SELECT o.OrderID 
               FROM Orders o 
               WHERE o.OrderID = ? AND o.CustomerID = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $order_id, $customer_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}
$verify_stmt->close();

$location_sql = "SELECT 
                    u.Location as RiderLocation,
                    u.FullName as RiderName,
                    t.DeliveryStatus
                FROM Orders o
                INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
                LEFT JOIN users u ON t.DeliveryPersonID = u.ID AND u.Role = 'delivery'
                WHERE o.OrderID = ?";

$location_stmt = $conn->prepare($location_sql);
$location_stmt->bind_param("i", $order_id);
$location_stmt->execute();
$location_result = $location_stmt->get_result();

if ($location_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No tracking information found']);
    exit;
}

$location_data = $location_result->fetch_assoc();
$location_stmt->close();
$conn->close();

if (empty($location_data['RiderLocation'])) {
    echo json_encode(['success' => false, 'message' => 'Rider location not available']);
    exit;
}

echo json_encode([
    'success' => true,
    'rider_location' => $location_data['RiderLocation'],
    'rider_name' => $location_data['RiderName'],
    'delivery_status' => $location_data['DeliveryStatus']
]);
?>