<?php
session_start();
require_once dirname(__DIR__, 2) . '/conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$order_id = (int) $_GET['order_id'];

$return_sql = "SELECT 
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
                o.Discount,
                o.PlaceOrdered,
                u.FullName,
                u.Email,
                u.Contact,
                u.Address,
                (SELECT SUM(RefundAmount) FROM return_items WHERE ReturnID = r.ReturnID) as TotalRefundAmount,
                rt.DeliveryStatus as ReturnDeliveryStatus,
                rt.DeliveryPersonID,
                rd.FullName as ReturnRiderName,
                rd.Contact as ReturnRiderContact
            FROM returns r
            INNER JOIN orders o ON r.OrderID = o.OrderID
            INNER JOIN users u ON r.CustomerID = u.ID
            LEFT JOIN trackings rt ON r.ReturnTrackingID = rt.TrackingID
            LEFT JOIN users rd ON rt.DeliveryPersonID = rd.ID AND rd.Role = 'delivery'
            WHERE r.OrderID = ?
            LIMIT 1";

$return_stmt = $conn->prepare($return_sql);
if (!$return_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$return_stmt->bind_param("i", $order_id);
$return_stmt->execute();
$return_result = $return_stmt->get_result();

if ($return_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Return not found']);
    exit;
}

$row = $return_result->fetch_assoc();

$proof_path = $row['ProofImage'];
if ($proof_path && strpos($proof_path, 'mechakeys/') === 0) {
    $proof_path = substr($proof_path, strlen('mechakeys/'));
}

$items_sql = "SELECT 
                    oi.OrderItemID,
                    oi.ProductID,
                    oi.Quantity,
                    oi.SubTotal as Price,
                    CONCAT(p.Brand, ' ', p.Model) as ProductName,
                    p.ProductImageID
                FROM orderitems oi
                INNER JOIN products p ON oi.ProductID = p.ProductID
                WHERE oi.OrderID = ?
                ORDER BY oi.OrderItemID ASC";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = [
        'order_item_id' => $item['OrderItemID'],
        'product_id' => $item['ProductID'],
        'product_name' => $item['ProductName'],
        'quantity' => $item['Quantity'],
        'price' => $item['Price'],
        'total' => $item['Quantity'] * $item['Price'],
        'image' => $item['ProductImageID']
    ];
}
$return_data = [
    'return_id' => $row['ReturnID'],
    'order_id' => $row['OrderID'],
    'customer_id' => $row['CustomerID'],
    'customer_name' => $row['FullName'],
    'customer_email' => $row['Email'],
    'customer_contact' => $row['Contact'],
    'customer_address' => $row['Address'],
    'order_total' => $row['TotalAmount'] - $row['Discount'],
    'order_subtotal' => $row['TotalAmount'],
    'order_discount' => $row['Discount'],
    'order_placed' => $row['PlaceOrdered'],
    'order_items' => $order_items,
    'return_reason' => $row['ReturnReason'],
    'proof_image' => $proof_path,
    'status' => $row['Status'],
    'admin_message' => $row['AdminMessage'],
    'total_refund_amount' => $row['TotalRefundAmount'] ?: ($row['TotalAmount'] - $row['Discount']),
    'return_tracking_id' => $row['ReturnTrackingID'],
    'return_delivery_status' => $row['ReturnDeliveryStatus'],
    'return_rider_name' => $row['ReturnRiderName'],
    'return_rider_contact' => $row['ReturnRiderContact'],
    'return_delivery_person_id' => $row['DeliveryPersonID'],
    'created_at' => $row['CreatedAt'],
    'updated_at' => $row['UpdatedAt']
];

$conn->close();

echo json_encode([
    'success' => true,
    'return' => $return_data
]);
?>