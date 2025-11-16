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

$remittance_id = isset($_POST['remittance_id']) ? intval($_POST['remittance_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

$allowed = ['Pending', 'Paid', 'Cancelled', 'Failed'];
if ($remittance_id <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid remittance ID or status']);
    exit;
}

try {
    $update_sql = "UPDATE rider_remittances SET Status = ?, Notes = IFNULL(NULLIF(?, ''), Notes) WHERE RemittanceID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssi', $status, $note, $remittance_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update remittance status: ' . $stmt->error);
    }

    $stmt->close();

    $admin_id = intval($_SESSION['user_id']);
    $old = '';
    $log_stmt = $conn->prepare("INSERT INTO remittance_logs (RemittanceID, AdminID, Action, OldStatus, NewStatus, Note) VALUES (?, ?, 'StatusChanged', ?, ?, ?)");
    $log_stmt->bind_param('iisss', $remittance_id, $admin_id, $old, $status, $note);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode(['success' => true, 'message' => 'Remittance status updated', 'remittance_id' => $remittance_id, 'status' => $status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>