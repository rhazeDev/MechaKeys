<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$return_id = isset($_POST['return_id']) ? intval($_POST['return_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
$admin_message = isset($_POST['admin_message']) ? trim($_POST['admin_message']) : '';

if ($return_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid return ID or action']);
    exit;
}

if ($action === 'approve' && $rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Rider selection is required for approval']);
    exit;
}

if ($action === 'reject' && empty($admin_message)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

$conn->begin_transaction();

try {
    $return_sql = "SELECT r.*, o.CustomerID, o.TotalAmount FROM returns r 
                   INNER JOIN orders o ON r.OrderID = o.OrderID 
                   WHERE r.ReturnID = ?";
    $return_stmt = $conn->prepare($return_sql);
    $return_stmt->bind_param("i", $return_id);
    $return_stmt->execute();
    $return_result = $return_stmt->get_result();

    if ($return_result->num_rows === 0) {
        throw new Exception('Return not found');
    }

    $return_data = $return_result->fetch_assoc();
    $return_stmt->close();

    if ($return_data['Status'] !== 'Pending') {
        throw new Exception('Return must be in Pending status to process');
    }

    $customer_id = $return_data['CustomerID'];
    $order_id = $return_data['OrderID'];
    $refund_amount = $return_data['TotalAmount'];

    if ($action === 'approve') {
        $rider_check_sql = "SELECT ID FROM users WHERE ID = ? AND Role = 'delivery'";
        $rider_check_stmt = $conn->prepare($rider_check_sql);
        $rider_check_stmt->bind_param("i", $rider_id);
        $rider_check_stmt->execute();
        $rider_check_result = $rider_check_stmt->get_result();

        if ($rider_check_result->num_rows === 0) {
            throw new Exception('Invalid rider selected');
        }
        $rider_check_stmt->close();

        $create_tracking_sql = "INSERT INTO trackings (DeliveryPersonID, DeliveryStatus, LastUpdated) 
                               VALUES (?, 'Ready to Deliver', NOW())";
        $create_tracking_stmt = $conn->prepare($create_tracking_sql);
        $create_tracking_stmt->bind_param("i", $rider_id);

        if (!$create_tracking_stmt->execute()) {
            throw new Exception('Failed to create return tracking');
        }

        $tracking_id = $conn->insert_id;
        $create_tracking_stmt->close();

        $update_return_sql = "UPDATE returns SET Status = 'Approved', ReturnTrackingID = ?, UpdatedAt = NOW() 
                             WHERE ReturnID = ?";
        $update_return_stmt = $conn->prepare($update_return_sql);
        $update_return_stmt->bind_param("ii", $tracking_id, $return_id);

        if (!$update_return_stmt->execute()) {
            throw new Exception('Failed to update return status');
        }
        $update_return_stmt->close();

        $notif_title = "Return Approved";
        $notif_message = "Your return request for order #$order_id has been approved. A delivery rider will pick up the item from your address.";
        $notif_type = "return";
        $notif_status = "unread";

        $notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);

        if (!$notif_stmt->execute()) {
            throw new Exception('Failed to create notification');
        }
        $notif_stmt->close();

    } else if ($action === 'reject') {
        $update_return_sql = "UPDATE returns SET Status = 'Rejected', AdminMessage = ?, UpdatedAt = NOW() 
                             WHERE ReturnID = ?";
        $update_return_stmt = $conn->prepare($update_return_sql);
        $update_return_stmt->bind_param("si", $admin_message, $return_id);

        if (!$update_return_stmt->execute()) {
            throw new Exception('Failed to update return status');
        }
        $update_return_stmt->close();

        $notif_title = "Return Rejected";
        $notif_message = "Your return request for order #$order_id has been rejected. Reason: $admin_message";
        $notif_type = "return";
        $notif_status = "unread";

        $notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);

        if (!$notif_stmt->execute()) {
            throw new Exception('Failed to create notification');
        }
        $notif_stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $action === 'approve' ? 'Return approved successfully' : 'Return rejected successfully',
        'return_id' => $return_id,
        'status' => $action === 'approve' ? 'Approved' : 'Rejected'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>