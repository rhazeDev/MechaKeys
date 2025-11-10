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

$return_sql = "SELECT 
                r.ReturnID,
                r.OrderID,
                r.ReturnReason,
                r.ProofImage,
                r.Status,
                r.AdminMessage,
                r.ReturnTrackingID,
                r.CreatedAt,
                r.UpdatedAt,
                rt.DeliveryStatus as ReturnDeliveryStatus,
                rt.LastUpdated as ReturnLastUpdated,
                rt.DeliveryPersonID,
                u.FullName as ReturnRiderName,
                u.Contact as ReturnRiderContact,
                u.Location as ReturnRiderLocation,
                customer.Location as CustomerLocation,
                (SELECT SUM(RefundAmount) FROM return_items WHERE ReturnID = r.ReturnID) as TotalRefundAmount
            FROM returns r
            LEFT JOIN trackings rt ON r.ReturnTrackingID = rt.TrackingID
            LEFT JOIN users u ON rt.DeliveryPersonID = u.ID AND u.Role = 'delivery'
            LEFT JOIN orders o ON r.OrderID = o.OrderID
            LEFT JOIN users customer ON o.CustomerID = customer.ID
            WHERE r.OrderID = ? AND r.CustomerID = ?";

$return_stmt = $conn->prepare($return_sql);
$return_stmt->bind_param("ii", $order_id, $customer_id);
$return_stmt->execute();
$return_result = $return_stmt->get_result();

if ($return_result->num_rows === 0) {
    echo json_encode(['success' => true, 'has_return' => false]);
    exit;
}

$return_data = $return_result->fetch_assoc();
$return_stmt->close();

$proof_path = $return_data['ProofImage'];
if ($proof_path && strpos($proof_path, 'mechakeys/') === 0) {
    $proof_path = substr($proof_path, strlen('mechakeys/'));
}

$response = [
    'success' => true,
    'has_return' => true,
    'return' => [
        'return_id' => $return_data['ReturnID'],
        'order_id' => $return_data['OrderID'],
        'status' => $return_data['Status'],
        'reason' => $return_data['ReturnReason'],
        'proof_image' => $proof_path,
        'admin_message' => $return_data['AdminMessage'],
        'total_refund_amount' => $return_data['TotalRefundAmount'] ?? 0,
        'created_at' => $return_data['CreatedAt'],
        'updated_at' => $return_data['UpdatedAt'],
        'return_tracking_id' => $return_data['ReturnTrackingID'],
        'return_delivery_status' => $return_data['ReturnDeliveryStatus'],
        'return_last_updated' => $return_data['ReturnLastUpdated'],
        'return_rider_name' => $return_data['ReturnRiderName'],
        'return_rider_contact' => $return_data['ReturnRiderContact'],
        'return_rider_location' => $return_data['ReturnRiderLocation'],
        'customer_location' => $return_data['CustomerLocation'],
        'delivery_person_id' => $return_data['DeliveryPersonID']
    ]
];

$conn->close();

echo json_encode($response);
?>