<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$delivery_person_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    $query = "SELECT 
                o.OrderID,
                o.CustomerID,
                o.TotalAmount,
                o.PlaceOrdered,
                c.FullName as CustomerName,
                c.Email as CustomerEmail,
                c.Address as CustomerAddress,
                c.Contact as CustomerPhone,
                c.Location as CustomerLocation,
                t.TrackingID,
                t.DeliveryStatus,
                (SELECT COUNT(*) FROM orderitems WHERE OrderID = o.OrderID) as ItemCount
              FROM orders o
              JOIN users c ON o.CustomerID = c.ID
              JOIN trackings t ON o.TrackingID = t.TrackingID
              WHERE (t.DeliveryPersonID = ? OR (t.DeliveryStatus = 'Ready to Deliver' AND t.DeliveryPersonID = 0))";

    $params = [$delivery_person_id];

    if ($status_filter) {
        if ($status_filter === 'Assigned') {
            $query .= " AND t.DeliveryStatus = 'Ready to Deliver'";
        } else {
            $query .= " AND t.DeliveryStatus = ?";
            $params[] = $status_filter;
        }
    }

    $query .= " ORDER BY o.PlaceOrdered DESC";

    $stmt = $conn->prepare($query);

    if (count($params) === 1) {
        $stmt->bind_param("i", $params[0]);
    } else {
        $types = str_repeat("i", count($params) - 1) . "s";
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $deliveries = [];
    $stats = [
        'Assigned' => 0,
        'Picked' => 0,
        'In Transit' => 0,
        'Delivered' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $display_status = $row['DeliveryStatus'];
        if ($row['DeliveryStatus'] === 'Ready to Deliver') {
            $display_status = 'Assigned';
        }

        $deliveries[] = [
            'OrderID' => $row['OrderID'],
            'TrackingID' => $row['TrackingID'],
            'CustomerName' => $row['CustomerName'],
            'CustomerEmail' => $row['CustomerEmail'],
            'CustomerAddress' => $row['CustomerAddress'],
            'CustomerPhone' => $row['CustomerPhone'],
            'CustomerLocation' => $row['CustomerLocation'],
            'TotalAmount' => $row['TotalAmount'],
            'ItemCount' => $row['ItemCount'],
            'PlaceOrdered' => $row['PlaceOrdered'],
            'DeliveryStatus' => $display_status
        ];

        if ($display_status === 'Assigned' || $row['DeliveryStatus'] === 'Ready to Deliver') {
            $stats['Assigned']++;
        } elseif ($display_status === 'Picked') {
            $stats['Picked']++;
        } elseif ($display_status === 'In Transit') {
            $stats['In Transit']++;
        } elseif ($display_status === 'Delivered') {
            $stats['Delivered']++;
        }
    }

    echo json_encode([
        'success' => true,
        'deliveries' => $deliveries,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>