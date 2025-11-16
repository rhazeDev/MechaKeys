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

$rider_id = isset($_POST['rider_id']) ? intval($_POST['rider_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;
$period_start = isset($_POST['period_start']) ? $_POST['period_start'] : null;
$period_end = isset($_POST['period_end']) ? $_POST['period_end'] : null;
$reference = isset($_POST['reference']) ? trim($_POST['reference']) : null;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
$order_ids = isset($_POST['order_ids']) ? $_POST['order_ids'] : [];

if ($rider_id <= 0 || $amount <= 0.0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rider or amount']);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("INSERT INTO rider_remittances (RiderID, Amount, PeriodStart, PeriodEnd, PaymentMethod, Reference, Notes, Status, CreatedAt, UpdatedAt, UpdatedBy) VALUES (?, ?, ?, ?, 'Cash', ?, ?, 'Pending', NOW(), NULL, NULL)");
    $stmt->bind_param('idssss', $rider_id, $amount, $period_start, $period_end, $reference, $notes);
    $stmt->execute();
    $remittance_id = $conn->insert_id;
    $stmt->close();

    if (!empty($order_ids) && is_array($order_ids)) {
        $map_stmt = $conn->prepare("INSERT IGNORE INTO remittance_orders (RemittanceID, OrderID) VALUES (?, ?)");
        foreach ($order_ids as $oid) {
            $oid = intval($oid);
            if ($oid > 0) {
                $map_stmt->bind_param('ii', $remittance_id, $oid);
                $map_stmt->execute();
            }
        }
        $map_stmt->close();
    }

    $admin_id = intval($_SESSION['user_id']);
    $log_stmt = $conn->prepare("INSERT INTO remittance_logs (RemittanceID, AdminID, Action, OldStatus, NewStatus, Note) VALUES (?, ?, 'Created', NULL, 'Pending', ?)");
    $log_stmt->bind_param('iis', $remittance_id, $admin_id, $notes);
    $log_stmt->execute();
    $log_stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Remittance created', 'remittance_id' => $remittance_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>