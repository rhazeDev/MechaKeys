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

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';
$return_id = isset($_POST['return_id']) ? intval($_POST['return_id']) : 0;
$return_status = isset($_POST['return_status']) ? trim($_POST['return_status']) : '';

if ($return_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid return ID']);
    exit;
}

$valid_statuses = ['Pending', 'Picked', 'In Transit', 'Returned'];

if (!in_array($return_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid return status']);
    exit;
}

$return_sql = "SELECT r.ReturnTrackingID, r.OrderID, r.CustomerID, r.Status, o.TotalAmount, o.Discount
               FROM returns r
               INNER JOIN orders o ON r.OrderID = o.OrderID
               WHERE r.ReturnID = ?";

$return_stmt = $conn->prepare($return_sql);
$return_stmt->bind_param("i", $return_id);
$return_stmt->execute();
$return_result = $return_stmt->get_result();

if ($return_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Return not found']);
    exit;
}

$return_data = $return_result->fetch_assoc();
$return_stmt->close();

if ($user_role === 'delivery') {
    $check_rider_sql = "SELECT DeliveryPersonID FROM trackings WHERE TrackingID = ?";
    $check_rider_stmt = $conn->prepare($check_rider_sql);
    $check_rider_stmt->bind_param("i", $return_data['ReturnTrackingID']);
    $check_rider_stmt->execute();
    $check_rider_result = $check_rider_stmt->get_result();

    if ($check_rider_result->num_rows === 0 || $check_rider_result->fetch_assoc()['DeliveryPersonID'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    $check_rider_stmt->close();
} elseif ($user_role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn->begin_transaction();

try {
    $update_tracking_sql = "UPDATE trackings SET DeliveryStatus = ?, LastUpdated = NOW() WHERE TrackingID = ?";
    $update_tracking_stmt = $conn->prepare($update_tracking_sql);
    $update_tracking_stmt->bind_param("si", $return_status, $return_data['ReturnTrackingID']);

    if (!$update_tracking_stmt->execute()) {
        throw new Exception('Failed to update return tracking status');
    }
    $update_tracking_stmt->close();

    $proof_path = null;
    if ($return_status === 'Returned' && isset($_FILES['return_proof'])) {
        if ($_FILES['return_proof']['error'] === UPLOAD_ERR_OK) {
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $_FILES['return_proof']['tmp_name']);
            finfo_close($file_info);

            if (in_array($mime_type, $allowed_mime_types)) {
                $proof_dir = '../../proofofdelivery/';
                if (!is_dir($proof_dir)) {
                    mkdir($proof_dir, 0755, true);
                }

                $file_ext = pathinfo($_FILES['return_proof']['name'], PATHINFO_EXTENSION);
                $filename = 'proof_return_' . $return_data['ReturnTrackingID'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                $file_path = $proof_dir . $filename;
                $proof_path = 'mechakeys/proofofdelivery/' . $filename;

                if (move_uploaded_file($_FILES['return_proof']['tmp_name'], $file_path)) {
                    $update_proof_sql = "UPDATE trackings SET DeliveryProof = ? WHERE TrackingID = ?";
                    $update_proof_stmt = $conn->prepare($update_proof_sql);
                    $update_proof_stmt->bind_param("si", $proof_path, $return_data['ReturnTrackingID']);
                    $update_proof_stmt->execute();
                    $update_proof_stmt->close();
                }
            }
        }
    }

    if ($return_status === 'Returned') {
        $refund_amount = $return_data['TotalAmount'];
        $customer_id = $return_data['CustomerID'];

        $update_coins_sql = "UPDATE users SET Coins = Coins + ? WHERE ID = ?";
        $update_coins_stmt = $conn->prepare($update_coins_sql);
        $update_coins_stmt->bind_param("di", $refund_amount, $customer_id);

        if (!$update_coins_stmt->execute()) {
            throw new Exception('Failed to credit coins to customer');
        }
        $update_coins_stmt->close();

        $notif_title = "Refund Processed";
        $notif_message = "Your return has been completed. ₱" . number_format($refund_amount, 2) . " has been credited to your account as coins.";
        $notif_type = "return";
        $notif_status = "unread";

        $notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
        $notif_stmt = $conn->prepare($notif_sql);
        $notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);
        $notif_stmt->execute();
        $notif_stmt->close();

        $update_return_sql = "UPDATE returns SET Status = 'Completed', UpdatedAt = NOW() WHERE ReturnID = ?";
        $update_return_stmt = $conn->prepare($update_return_sql);
        $update_return_stmt->bind_param("i", $return_id);
        $update_return_stmt->execute();
        $update_return_stmt->close();
    }

    $status_messages = [
        'Pending' => 'Your return pickup is pending. Awaiting rider confirmation.',
        'Picked' => 'Your return has been picked up and is on the way.',
        'In Transit' => 'Your return is in transit to our facility.',
        'Returned' => 'Your return has been received. Refund has been processed!'
    ];

    $notif_title = "Return Status Updated";
    $notif_message = "Your return for order #" . $return_data['OrderID'] . ": " . ($status_messages[$return_status] ?? 'Status updated');
    $notif_type = "delivery";
    $notif_status = "unread";
    $customer_id = $return_data['CustomerID'];

    $notif_sql = "INSERT INTO notifications (CustomerID, Title, Message, Type, Status, TimeCreated) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);

    if (!$notif_stmt->execute()) {
        throw new Exception('Failed to create notification');
    }
    $notif_stmt->close();

    $conn->commit();

    $message = $return_status === 'Returned'
        ? 'Return completed successfully. Coins credited to customer account.'
        : 'Return status updated successfully';

    echo json_encode([
        'success' => true,
        'message' => $message,
        'return_id' => $return_id,
        'return_status' => $return_status,
        'coins_credited' => $return_status === 'Returned' ? $return_data['TotalAmount'] : 0
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>