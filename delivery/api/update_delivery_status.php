<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$tracking_id = isset($_POST['tracking_id']) ? intval($_POST['tracking_id']) : 0;
$new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($order_id <= 0 || $tracking_id <= 0 || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$status_mapping = [
    'Picked' => 'Picked',
    'In Transit' => 'In Transit',
    'Delivered' => 'Delivered'
];

if (!isset($status_mapping[$new_status])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$mapped_status = $status_mapping[$new_status];

$proof_image_path = null;
if ($new_status === 'Delivered' && isset($_FILES['proof_image'])) {
    $file = $_FILES['proof_image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
        exit;
    }

    if ($file['size'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Empty file uploaded']);
        exit;
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_type = $file['type'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file_type, $allowed_types) && !in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, GIF, WebP']);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size too large (max 5MB)']);
        exit;
    }

    $upload_dir = '../../proofofdelivery';
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit;
        }
    }

    if (!is_writable($upload_dir)) {
        echo json_encode(['success' => false, 'message' => 'Upload directory not writable']);
        exit;
    }

    $timestamp = time();
    $random_str = bin2hex(random_bytes(4));
    $filename = "proof_{$tracking_id}_{$timestamp}_{$random_str}.jpg";
    $file_path = $upload_dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $error_msg = 'Failed to save image';
        if (!is_uploaded_file($file['tmp_name'])) {
            $error_msg = 'Invalid upload - not from HTTP POST';
        }
        echo json_encode(['success' => false, 'message' => $error_msg]);
        exit;
    }

    if (!file_exists($file_path)) {
        echo json_encode(['success' => false, 'message' => 'File creation verification failed']);
        exit;
    }

    $proof_image_path = 'mechakeys/proofofdelivery/' . $filename;
}

$conn->begin_transaction();

try {
    if ($new_status === 'Picked') {
        $check_assignment = $conn->prepare("SELECT DeliveryPersonID FROM trackings WHERE TrackingID = ?");
        $check_assignment->bind_param("i", $tracking_id);
        $check_assignment->execute();
        $check_result = $check_assignment->get_result();
        $check_data = $check_result->fetch_assoc();

        if ($check_data['DeliveryPersonID'] == 0) {
            $assign_rider = $conn->prepare("UPDATE trackings SET DeliveryPersonID = ? WHERE TrackingID = ?");
            $assign_rider->bind_param("ii", $_SESSION['user_id'], $tracking_id);
            if (!$assign_rider->execute()) {
                throw new Exception('Failed to assign delivery');
            }
            $assign_rider->close();
        }
        $check_assignment->close();
    }

    if ($new_status === 'Delivered' && $proof_image_path) {
        $update_tracking = $conn->prepare("UPDATE trackings SET DeliveryStatus = ?, DeliveryProof = ?, LastUpdated = NOW() WHERE TrackingID = ?");
        $update_tracking->bind_param("ssi", $mapped_status, $proof_image_path, $tracking_id);
    } else {
        $update_tracking = $conn->prepare("UPDATE trackings SET DeliveryStatus = ?, LastUpdated = NOW() WHERE TrackingID = ?");
        $update_tracking->bind_param("si", $mapped_status, $tracking_id);
    }

    if (!$update_tracking->execute()) {
        throw new Exception('Failed to update tracking status');
    }
    $update_tracking->close();

    $get_customer = $conn->prepare("SELECT CustomerID FROM orders WHERE OrderID = ?");
    $get_customer->bind_param("i", $order_id);
    $get_customer->execute();
    $customer_result = $get_customer->get_result();
    $customer_data = $customer_result->fetch_assoc();
    $customer_id = $customer_data['CustomerID'];
    $get_customer->close();

    $notification_title = "Delivery Status Updated";
    $status_messages = [
        'Picked' => "Your order #$order_id has been picked up and is on the way.",
        'In Transit' => "Your order #$order_id is on the way to you!",
        'Delivered' => "Your order #$order_id has been delivered. Thank you!"
    ];

    $notification_message = $status_messages[$new_status];
    $notification_type = "delivery";
    $notification_status = "unread";

    $insert_notification = $conn->prepare("INSERT INTO notifications (CustomerID, Title, Message, Type, Status) VALUES (?, ?, ?, ?, ?)");
    $insert_notification->bind_param("issss", $customer_id, $notification_title, $notification_message, $notification_type, $notification_status);

    if (!$insert_notification->execute()) {
        throw new Exception('Failed to create notification');
    }
    $insert_notification->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Delivery status updated successfully',
        'status' => $new_status
    ]);

} catch (Exception $e) {
    $conn->rollback();

    if ($proof_image_path && file_exists($upload_dir . '/' . $filename)) {
        unlink($upload_dir . '/' . $filename);
    }

    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>