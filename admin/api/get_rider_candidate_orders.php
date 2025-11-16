<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = isset($_GET['rider_id']) ? intval($_GET['rider_id']) : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$period_start = isset($_GET['period_start']) ? $_GET['period_start'] : null;
$period_end = isset($_GET['period_end']) ? $_GET['period_end'] : null;

if ($rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rider ID']);
    exit;
}

$dateFilter = '';
if ($filter === 'today') {
    $dateFilter = " AND DATE(t.LastUpdated) = CURDATE()";
} elseif ($filter === 'month') {
    $dateFilter = " AND YEAR(t.LastUpdated) = YEAR(CURDATE()) AND MONTH(t.LastUpdated) = MONTH(CURDATE())";
} elseif ($filter === 'custom' && $period_start && $period_end) {
    $dateFilter = " AND DATE(t.LastUpdated) BETWEEN ? AND ?";
}

try {
    if ($filter === 'custom' && $period_start && $period_end) {
    $stmt = $conn->prepare("SELECT o.OrderID, o.TotalAmount, o.Discount, o.PlaceOrdered, u.FullName as CustomerName, t.LastUpdated
            FROM trackings t
            INNER JOIN orders o ON t.TrackingID = o.TrackingID
            INNER JOIN users u ON o.CustomerID = u.ID
            WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered' AND DATE(t.LastUpdated) BETWEEN ? AND ?
            AND o.OrderID NOT IN (SELECT OrderID FROM remittance_orders)
            ORDER BY t.LastUpdated DESC");
        $stmt->bind_param('iss', $rider_id, $period_start, $period_end);
    } else {
    $stmt = $conn->prepare("SELECT o.OrderID, o.TotalAmount, o.Discount, o.PlaceOrdered, u.FullName as CustomerName, t.LastUpdated
            FROM trackings t
            INNER JOIN orders o ON t.TrackingID = o.TrackingID
            INNER JOIN users u ON o.CustomerID = u.ID
            WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered' " . $dateFilter . "
            AND o.OrderID NOT IN (SELECT OrderID FROM remittance_orders)
            ORDER BY t.LastUpdated DESC");
        $stmt->bind_param('i', $rider_id);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $orders = [];
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }

    $stmt->close();

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

?>
