<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$filter_status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$allowed_statuses = ['Pending', 'Approved', 'Rejected', 'Returned', 'Completed', 'all'];

if (!in_array($filter_status, $allowed_statuses)) {
    $filter_status = 'all';
}

$where_clause = '';
if ($filter_status !== 'all') {
    if ($filter_status === 'Completed') {
        $where_clause = " AND r.Status = 'Returned'";
    } else {
        $where_clause = " AND r.Status = '$filter_status'";
    }
}

$returns_sql = "SELECT 
                r.ReturnID,
                r.OrderID,
                r.CustomerID,
                r.ReturnReason,
                r.ProofImage,
                r.Status,
                r.AdminMessage,
                r.ReturnTrackingID,
                r.CreatedAt,
                r.UpdatedAt,
                o.TotalAmount,
                o.PlaceOrdered,
                u.FullName,
                u.Email,
                u.Contact,
                u.Address,
                (SELECT SUM(RefundAmount) FROM return_items WHERE ReturnID = r.ReturnID) as TotalRefundAmount,
                rt.DeliveryStatus as ReturnDeliveryStatus,
                rt.DeliveryPersonID,
                rd.FullName as ReturnRiderName
            FROM returns r
            INNER JOIN orders o ON r.OrderID = o.OrderID
            INNER JOIN users u ON r.CustomerID = u.ID
            LEFT JOIN trackings rt ON r.ReturnTrackingID = rt.TrackingID
            LEFT JOIN users rd ON rt.DeliveryPersonID = rd.ID AND rd.Role = 'delivery'
            WHERE 1=1 $where_clause
            ORDER BY r.CreatedAt DESC";

$returns_stmt = $conn->prepare($returns_sql);
if (!$returns_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$returns_stmt->execute();
$returns_result = $returns_stmt->get_result();

$returns = [];
while ($row = $returns_result->fetch_assoc()) {
    $proof_path = $row['ProofImage'];
    if ($proof_path && strpos($proof_path, 'mechakeys/') === 0) {
        $proof_path = substr($proof_path, strlen('mechakeys/'));
    }

    $returns[] = [
        'return_id' => $row['ReturnID'],
        'order_id' => $row['OrderID'],
        'customer_id' => $row['CustomerID'],
        'customer_name' => $row['FullName'],
        'customer_email' => $row['Email'],
        'customer_contact' => $row['Contact'],
        'customer_address' => $row['Address'],
        'order_total' => $row['TotalAmount'],
        'order_placed' => $row['PlaceOrdered'],
        'return_reason' => $row['ReturnReason'],
        'proof_image' => $proof_path,
        'status' => $row['Status'],
        'admin_message' => $row['AdminMessage'],
        'total_refund_amount' => $row['TotalRefundAmount'],
        'return_tracking_id' => $row['ReturnTrackingID'],
        'return_delivery_status' => $row['ReturnDeliveryStatus'],
        'return_rider_name' => $row['ReturnRiderName'],
        'created_at' => $row['CreatedAt'],
        'updated_at' => $row['UpdatedAt']
    ];
}

$returns_stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'returns' => $returns,
    'total' => count($returns),
    'filter' => $filter_status
]);
?>