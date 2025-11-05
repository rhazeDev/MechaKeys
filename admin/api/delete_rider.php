<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$rider_id = intval($_POST['rider_id'] ?? 0);

if ($rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rider ID']);
    exit;
}

try {
    $check_deliveries = $conn->prepare("SELECT COUNT(*) as count FROM trackings WHERE DeliveryPersonID = ? AND DeliveryStatus NOT IN ('Delivered', 'Cancelled')");
    $check_deliveries->bind_param("i", $rider_id);
    $check_deliveries->execute();
    $result = $check_deliveries->get_result();
    $data = $result->fetch_assoc();

    if ($data['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete rider with active deliveries. Please reassign or complete deliveries first.'
        ]);
        exit;
    }
    $check_deliveries->close();

    $delete_rider = $conn->prepare("DELETE FROM users WHERE ID = ? AND Role = 'delivery'");
    $delete_rider->bind_param("i", $rider_id);

    if ($delete_rider->execute()) {
        if ($delete_rider->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Rider deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Rider not found or already deleted'
            ]);
        }
    } else {
        throw new Exception('Failed to delete rider: ' . $conn->error);
    }

    $delete_rider->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>