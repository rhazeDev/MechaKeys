<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $riders_query = "SELECT 
                        u.ID,
                        u.FullName,
                        u.Email,
                        u.Contact,
                        u.Address,
                        u.DateCreated,
                        COALESCE((SELECT SUM(p.Amount) FROM payments p
                            INNER JOIN orders o_pay ON p.OrderID = o_pay.OrderID
                            INNER JOIN trackings t_pay ON o_pay.TrackingID = t_pay.TrackingID
                            WHERE p.Status = 'Paid' AND t_pay.DeliveryPersonID = u.ID), 0) AS CollectedByRider,
                        COALESCE((SELECT SUM(o2.TotalAmount - o2.Discount) FROM orders o2
                            INNER JOIN trackings t2 ON o2.TrackingID = t2.TrackingID
                            WHERE t2.DeliveryPersonID = u.ID AND t2.DeliveryStatus = 'Delivered'), 0) AS DeliveredOrderTotalByRider,
                        COALESCE((SELECT SUM(rr.Amount) FROM rider_remittances rr WHERE rr.RiderID = u.ID AND rr.Status = 'Paid'), 0) as TotalRemittedByRider,
                        COUNT(DISTINCT CASE WHEN t.DeliveryStatus IN ('Ready to Deliver', 'Picked', 'In Transit') THEN t.TrackingID END) as ActiveDeliveries,
                        COUNT(DISTINCT CASE WHEN t.DeliveryStatus = 'Delivered' AND DATE(t.LastUpdated) = CURDATE() THEN t.TrackingID END) as CompletedToday
                    FROM users u
                    LEFT JOIN trackings t ON u.ID = t.DeliveryPersonID
                    WHERE u.Role = 'delivery'
                    GROUP BY u.ID
                    ORDER BY u.DateCreated DESC";

    $riders_result = $conn->query($riders_query);

    if (!$riders_result) {
        throw new Exception('Failed to fetch riders: ' . $conn->error);
    }

    $riders = [];
    while ($row = $riders_result->fetch_assoc()) {
        $collected = floatval($row['CollectedByRider'] ?? 0);
        $delivered = floatval($row['DeliveredOrderTotalByRider'] ?? 0);
        $paid_remitted = floatval($row['TotalRemittedByRider'] ?? 0);
        $unremitted = max($collected, $delivered) - $paid_remitted;
        if ($unremitted < 0)
            $unremitted = 0.00;
        $row['TotalUnremitted'] = number_format($unremitted, 2, '.', '');
        $riders[] = $row;
    }

    $total_riders = count($riders);
    $active_riders = 0;
    $total_active_deliveries = 0;
    $total_completed_today = 0;
    $total_unremitted_amount = 0.00;
    $total_collected_today = 0.00;

    foreach ($riders as $rider) {
        if ($rider['ActiveDeliveries'] > 0) {
            $active_riders++;
        }
        $total_active_deliveries += $rider['ActiveDeliveries'];
        $total_completed_today += $rider['CompletedToday'];
        $total_unremitted_amount += floatval($rider['TotalUnremitted']);
    }

    $collected_query = "SELECT COALESCE(SUM(p.Amount), 0) as total FROM payments p
        INNER JOIN orders o ON p.OrderID = o.OrderID
        INNER JOIN trackings t ON o.TrackingID = t.TrackingID
        WHERE p.Status = 'Paid' AND DATE(p.TransactionDate) = CURDATE()";
    $col_res = $conn->query($collected_query)->fetch_assoc();
    $total_collected_today = floatval($col_res['total']);
    if ($total_collected_today <= 0) {
        $collected_fallback_query = "SELECT COALESCE(SUM(o.TotalAmount - o.Discount), 0) as total FROM orders o
            INNER JOIN trackings t ON o.TrackingID = t.TrackingID
            WHERE t.DeliveryStatus = 'Delivered' AND DATE(t.LastUpdated) = CURDATE()";
        $col_res2 = $conn->query($collected_fallback_query)->fetch_assoc();
        $total_collected_today = floatval($col_res2['total']);
    }

    echo json_encode([
        'success' => true,
        'riders' => $riders,
        'stats' => [
            'total' => $total_riders,
            'active' => $active_riders,
            'activeDeliveries' => $total_active_deliveries,
            'completedToday' => $total_completed_today,
            'totalUnremitted' => number_format($total_unremitted_amount, 2, '.', ''),
            'totalCollectedToday' => number_format($total_collected_today, 2, '.', ''),
            'totalRemitted' => number_format(0, 2, '.', '')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>