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
        $riders[] = $row;
    }

    $total_riders = count($riders);
    $active_riders = 0;
    $total_active_deliveries = 0;
    $total_completed_today = 0;

    foreach ($riders as $rider) {
        if ($rider['ActiveDeliveries'] > 0) {
            $active_riders++;
        }
        $total_active_deliveries += $rider['ActiveDeliveries'];
        $total_completed_today += $rider['CompletedToday'];
    }

    echo json_encode([
        'success' => true,
        'riders' => $riders,
        'stats' => [
            'total' => $total_riders,
            'active' => $active_riders,
            'activeDeliveries' => $total_active_deliveries,
            'completedToday' => $total_completed_today
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