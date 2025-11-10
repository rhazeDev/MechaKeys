<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$customer_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$return_reason = isset($_POST['return_reason']) ? trim($_POST['return_reason']) : '';

if ($order_id <= 0 || empty($return_reason) || strlen($return_reason) < 10) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or return reason must be at least 10 characters']);
    exit;
}

if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Image upload failed']);
    exit;
}

$allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
$file_info = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($file_info, $_FILES['proof_image']['tmp_name']);
finfo_close($file_info);

if (!in_array($mime_type, $allowed_mime_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid image format. Only JPEG, PNG, and WebP are allowed']);
    exit;
}

$max_file_size = 5 * 1024 * 1024;
if ($_FILES['proof_image']['size'] > $max_file_size) {
    echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
    exit;
}

$order_check_sql = "SELECT 
                    o.OrderID,
                    o.TotalAmount,
                    t.DeliveryStatus,
                    t.LastUpdated,
                    DATEDIFF(NOW(), DATE(t.LastUpdated)) as DaysSinceDelivery
                FROM Orders o
                INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
                WHERE o.OrderID = ? AND o.CustomerID = ?";

$order_check_stmt = $conn->prepare($order_check_sql);
$order_check_stmt->bind_param("ii", $order_id, $customer_id);
$order_check_stmt->execute();
$order_check_result = $order_check_stmt->get_result();

if ($order_check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $order_check_result->fetch_assoc();
$order_check_stmt->close();

if ($order['DeliveryStatus'] !== 'Delivered') {
    echo json_encode(['success' => false, 'message' => 'Order must be delivered to request a return']);
    exit;
}

$days_since_delivery = (int) $order['DaysSinceDelivery'];
if ($days_since_delivery > 7) {
    echo json_encode(['success' => false, 'message' => 'Return window has expired. Returns must be requested within 7 days of delivery']);
    exit;
}

$return_check_sql = "SELECT ReturnID, Status FROM returns WHERE OrderID = ? AND CustomerID = ?";
$return_check_stmt = $conn->prepare($return_check_sql);
$return_check_stmt->bind_param("ii", $order_id, $customer_id);
$return_check_stmt->execute();
$return_check_result = $return_check_stmt->get_result();

if ($return_check_result->num_rows > 0) {
    $existing_return = $return_check_result->fetch_assoc();
    echo json_encode(['success' => false, 'message' => 'A return request already exists for this order with status: ' . $existing_return['Status']]);
    exit;
}
$return_check_stmt->close();

$proof_dir = '../../proofofdelivery/returns/';
if (!is_dir($proof_dir)) {
    mkdir($proof_dir, 0755, true);
}

$file_ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
$filename = 'proof_return_' . $order_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
$file_path = $proof_dir . $filename;
$db_path = 'mechakeys/proofofdelivery/returns/' . $filename;

if (!move_uploaded_file($_FILES['proof_image']['tmp_name'], $file_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save proof image']);
    exit;
}

$conn->begin_transaction();

try {
    $insert_return_sql = "INSERT INTO returns (OrderID, CustomerID, ReturnReason, ProofImage, Status, CreatedAt, UpdatedAt) 
                          VALUES (?, ?, ?, ?, 'Pending', NOW(), NOW())";

    $status = 'Pending';
    $insert_return_stmt = $conn->prepare($insert_return_sql);
    $insert_return_stmt->bind_param("iiss", $order_id, $customer_id, $return_reason, $db_path);

    if (!$insert_return_stmt->execute()) {
        throw new Exception('Failed to create return request');
    }

    $return_id = $conn->insert_id;
    $insert_return_stmt->close();

    $order_items_sql = "SELECT OrderItemID, SubTotal FROM OrderItems WHERE OrderID = ?";
    $order_items_stmt = $conn->prepare($order_items_sql);
    $order_items_stmt->bind_param("i", $order_id);
    $order_items_stmt->execute();
    $order_items_result = $order_items_stmt->get_result();

    $insert_return_item_sql = "INSERT INTO return_items (ReturnID, OrderItemID, RefundAmount) VALUES (?, ?, ?)";
    $insert_return_item_stmt = $conn->prepare($insert_return_item_sql);

    while ($item = $order_items_result->fetch_assoc()) {
        $insert_return_item_stmt->bind_param("iid", $return_id, $item['OrderItemID'], $item['SubTotal']);
        if (!$insert_return_item_stmt->execute()) {
            throw new Exception('Failed to create return item record');
        }
    }

    $insert_return_item_stmt->close();
    $order_items_stmt->close();

    $notif_title = "Return Request Submitted";
    $notif_message = "Your return request for order #$order_id has been submitted. Awaiting admin approval.";
    $notif_type = "return";
    $notif_status = "unread";

    $insert_notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
    $insert_notif_stmt = $conn->prepare($insert_notif_sql);
    $insert_notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);

    if (!$insert_notif_stmt->execute()) {
        throw new Exception('Failed to create notification');
    }
    $insert_notif_stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Return request submitted successfully',
        'return_id' => $return_id,
        'refund_amount' => $order['TotalAmount']
    ]);

} catch (Exception $e) {
    $conn->rollback();

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>