<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$delivery_person_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    $query = "SELECT 
                o.OrderID,
                o.CustomerID,
                o.TotalAmount,
                o.Discount,
                o.PlaceOrdered,
                u.FullName as CustomerName,
                u.Email as CustomerEmail,
                u.Address as CustomerAddress,
                u.Contact as CustomerPhone,
                u.Location as CustomerLocation,
                t.TrackingID,
                t.DeliveryStatus,
                (SELECT COUNT(*) FROM orderitems WHERE OrderID = o.OrderID) as ItemCount
              FROM orders o
              JOIN users u ON o.CustomerID = u.ID
              JOIN trackings t ON o.TrackingID = t.TrackingID
              WHERE (t.DeliveryPersonID = ? OR (t.DeliveryStatus = 'Ready to Deliver' AND t.DeliveryPersonID = 0))
              AND (t.DeliveryStatus != 'Delivered' OR DATE(t.LastUpdated) = CURDATE())";

    $params = [$delivery_person_id];

    if ($status_filter) {
        if ($status_filter === 'Assigned') {
            $query .= " AND t.DeliveryStatus = 'Ready to Deliver'";
        } else {
            $query .= " AND t.DeliveryStatus = ?";
            $params[] = $status_filter;
        }
    }

    $query .= " ORDER BY 
        CASE 
            WHEN t.DeliveryStatus IN ('Ready to Deliver', 'Assigned') THEN 1
            ELSE 2
        END,
        o.PlaceOrdered ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);

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
            'Discount' => $row['Discount'],
            'FinalAmount' => $row['TotalAmount'] - $row['Discount'],
            'ItemCount' => $row['ItemCount'],
            'PlaceOrdered' => $row['PlaceOrdered'],
            'DeliveryStatus' => $display_status,
            'IsReturn' => false
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

    $returns_query = "SELECT 
                        r.ReturnID,
                        r.OrderID,
                        r.Status as ReturnStatus,
                        r.ReturnReason,
                        r.ReturnTrackingID,
                        o.TotalAmount,
                        o.Discount,
                        o.CustomerID,
                        o.PlaceOrdered,
                        u.FullName as CustomerName,
                        u.Email as CustomerEmail,
                        u.Address as CustomerAddress,
                        u.Contact as CustomerPhone,
                        u.Location as CustomerLocation,
                        t.TrackingID,
                        t.DeliveryStatus,
                        (SELECT COUNT(*) FROM return_items WHERE ReturnID = r.ReturnID) as ItemCount
                      FROM returns r
                      JOIN orders o ON r.OrderID = o.OrderID
                      JOIN users u ON o.CustomerID = u.ID
                      JOIN trackings t ON r.ReturnTrackingID = t.TrackingID
                      WHERE t.DeliveryPersonID = ? AND r.Status IN ('Approved', 'In Transit')
                      AND (t.DeliveryStatus != 'Delivered' OR DATE(t.LastUpdated) = CURDATE())";

    $params_return = [$delivery_person_id];

    if ($status_filter) {
        if ($status_filter === 'Assigned') {
            $returns_query .= " AND t.DeliveryStatus = 'Ready to Deliver'";
        } elseif ($status_filter === 'Picked') {
            $returns_query .= " AND 1=0";
        } else {
            $returns_query .= " AND t.DeliveryStatus = ?";
            $params_return[] = $status_filter;
        }
    }

    $returns_query .= " ORDER BY 
        CASE 
            WHEN t.DeliveryStatus IN ('Ready to Deliver', 'Assigned') THEN 1
            ELSE 2
        END,
        o.PlaceOrdered ASC";

    $returns_stmt = $conn->prepare($returns_query);
    $returns_stmt->bind_param(str_repeat("s", count($params_return)), ...$params_return);

    $returns_stmt->execute();
    $returns_result = $returns_stmt->get_result();

    while ($row = $returns_result->fetch_assoc()) {
        $display_status = $row['DeliveryStatus'];
        if ($row['DeliveryStatus'] === 'Ready to Deliver') {
            $display_status = 'Assigned';
        }

        $deliveries[] = [
            'ReturnID' => $row['ReturnID'],
            'OrderID' => $row['OrderID'],
            'TrackingID' => $row['TrackingID'],
            'ReturnTrackingID' => $row['ReturnTrackingID'],
            'CustomerName' => $row['CustomerName'],
            'CustomerEmail' => $row['CustomerEmail'],
            'CustomerAddress' => $row['CustomerAddress'],
            'CustomerPhone' => $row['CustomerPhone'],
            'CustomerLocation' => $row['CustomerLocation'],
            'TotalAmount' => $row['TotalAmount'],
            'Discount' => $row['Discount'],
            'FinalAmount' => $row['TotalAmount'] - $row['Discount'],
            'RefundAmount' => $row['TotalAmount'] - $row['Discount'],
            'ItemCount' => $row['ItemCount'],
            'PlaceOrdered' => $row['PlaceOrdered'],
            'DeliveryStatus' => $display_status,
            'ReturnStatus' => $row['ReturnStatus'],
            'ReturnReason' => $row['ReturnReason'],
            'IsReturn' => true
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

    $returns_stmt->close();

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