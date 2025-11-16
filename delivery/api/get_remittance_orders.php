<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = intval($_SESSION['user_id']);
$remittance_id = isset($_GET['remittance_id']) ? intval($_GET['remittance_id']) : 0;

if ($remittance_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid remittance id']);
    exit;
}

try {
    $check_stmt = $conn->prepare('SELECT RiderID FROM rider_remittances WHERE RemittanceID = ? LIMIT 1');
    $check_stmt->bind_param('i', $remittance_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    if (!$check_res || $check_res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Remittance not found']);
        exit;
    }
    $row = $check_res->fetch_assoc();
    if (intval($row['RiderID']) !== $rider_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to remittance orders']);
        exit;
    }
    $check_stmt->close();

    $sql = "SELECT o.OrderID, o.TotalAmount, o.Discount, o.PlaceOrdered, u.FullName as CustomerName, t.LastUpdated
        FROM remittance_orders ro
        INNER JOIN orders o ON ro.OrderID = o.OrderID
        INNER JOIN trackings t ON o.TrackingID = t.TrackingID
        INNER JOIN users u ON o.CustomerID = u.ID
        WHERE ro.RemittanceID = ?
        ORDER BY o.PlaceOrdered DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $remittance_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $orders = [];
    while ($o = $res->fetch_assoc()) {
        $orders[] = $o;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>