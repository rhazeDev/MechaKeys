<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = isset($_GET['rider_id']) ? intval($_GET['rider_id']) : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if ($rider_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rider ID']);
    exit;
}

$dateFilter = '';
$params = [$rider_id];
$types = 'i';

if ($filter === 'today') {
    $dateFilter = ' AND DATE(t.LastUpdated) = CURDATE()';
} elseif ($filter === 'month') {
    $dateFilter = ' AND YEAR(t.LastUpdated) = YEAR(CURDATE()) AND MONTH(t.LastUpdated) = MONTH(CURDATE())';
}

$total_query = "SELECT COUNT(*) as total FROM trackings t
                WHERE t.DeliveryPersonID = ?" . $dateFilter;
$total_stmt = $conn->prepare($total_query);
if (!empty($params)) {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_delivered = $total_result['total'];
$total_stmt->close();

$success_query = "SELECT COUNT(*) as total FROM trackings t
                  WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered'" . $dateFilter;
$success_stmt = $conn->prepare($success_query);
if (!empty($params)) {
    $success_stmt->bind_param($types, ...$params);
}
$success_stmt->execute();
$success_result = $success_stmt->get_result()->fetch_assoc();
$successful_deliveries = $success_result['total'];
$success_stmt->close();

$failed_query = "SELECT COUNT(*) as total FROM trackings t
                 WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Cancelled'" . $dateFilter;
$failed_stmt = $conn->prepare($failed_query);
if (!empty($params)) {
    $failed_stmt->bind_param($types, ...$params);
}
$failed_stmt->execute();
$failed_result = $failed_stmt->get_result()->fetch_assoc();
$failed_deliveries = $failed_result['total'];
$failed_stmt->close();

$in_progress_query = "SELECT COUNT(*) as total FROM trackings t
                      WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus IN ('In Transit', 'Processing')" . $dateFilter;
$in_progress_stmt = $conn->prepare($in_progress_query);
if (!empty($params)) {
    $in_progress_stmt->bind_param($types, ...$params);
}
$in_progress_stmt->execute();
$in_progress_result = $in_progress_stmt->get_result()->fetch_assoc();
$in_progress_deliveries = $in_progress_result['total'];
$in_progress_stmt->close();

$deliveries_query = "SELECT 
                        o.OrderID,
                        o.TotalAmount,
                        o.Discount,
                        u.FullName as CustomerName,
                        t.DeliveryStatus,
                        t.LastUpdated,
                        t.DeliveryProof
                    FROM trackings t
                    INNER JOIN orders o ON t.TrackingID = o.TrackingID
                    INNER JOIN users u ON o.CustomerID = u.ID
                    WHERE t.DeliveryPersonID = ?" . $dateFilter . "
                    ORDER BY t.LastUpdated DESC
                    LIMIT 50";
$deliveries_stmt = $conn->prepare($deliveries_query);
if (!empty($params)) {
    $deliveries_stmt->bind_param($types, ...$params);
}
$deliveries_stmt->execute();
$deliveries_result = $deliveries_stmt->get_result();

$deliveries = [];
while ($row = $deliveries_result->fetch_assoc()) {
    if ($row['DeliveryProof'] && strpos($row['DeliveryProof'], 'mechakeys/') === 0) {
        $row['DeliveryProof'] = substr($row['DeliveryProof'], strlen('mechakeys/'));
    }
    $deliveries[] = $row;
}
$deliveries_stmt->close();

$remittances = [];
$remitted_total = 0.00;
$collected_total = 0.00;
$unremitted_total = 0.00;
$rem_query = "SELECT RemittanceID, Amount, PeriodStart, PeriodEnd, TransactionDate, PaymentMethod, Reference, Status, Notes FROM rider_remittances WHERE RiderID = ?";
if ($filter === 'today') {
    $rem_query .= ' AND DATE(TransactionDate) = CURDATE()';
} elseif ($filter === 'month') {
    $rem_query .= ' AND YEAR(TransactionDate) = YEAR(CURDATE()) AND MONTH(TransactionDate) = MONTH(CURDATE())';
}
$rem_stmt = $conn->prepare($rem_query);
if (!empty($params)) {
    $rem_stmt->bind_param($types, ...$params);
}
$rem_stmt->execute();
$rem_result = $rem_stmt->get_result();
$remittances_sanitized = [];
while ($row = $rem_result->fetch_assoc()) {
    if (empty($row['PaymentMethod'])) {
        $row['PaymentMethod'] = 'Cash';
    }
    $remittances_sanitized[] = $row;
    if (isset($row['Status']) && $row['Status'] === 'Paid') {
        $remitted_total += floatval($row['Amount']);
    }
}
$remittances = $remittances_sanitized;
$rem_stmt->close();

$collected_sql = "SELECT COALESCE(SUM(p.Amount), 0) as total FROM payments p
    INNER JOIN orders o ON p.OrderID = o.OrderID
    INNER JOIN trackings t ON o.TrackingID = t.TrackingID
    WHERE p.Status = 'Paid' AND t.DeliveryPersonID = ?" . $dateFilter;
$col_stmt = $conn->prepare($collected_sql);
$col_stmt->bind_param($types, ...$params);
$col_stmt->execute();
$col_res = $col_stmt->get_result()->fetch_assoc();
$collected_total = floatval($col_res['total']);
$col_stmt->close();
if ($collected_total <= 0) {
    $fallback_collected_sql = "SELECT COALESCE(SUM(o.TotalAmount - o.Discount), 0) as total FROM orders o
        INNER JOIN trackings t ON o.TrackingID = t.TrackingID
        WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered'" . $dateFilter;
    $fb_stmt = $conn->prepare($fallback_collected_sql);
    $fb_stmt->bind_param($types, ...$params);
    $fb_stmt->execute();
    $fb_res = $fb_stmt->get_result()->fetch_assoc();
    $collected_total = floatval($fb_res['total']);
    $fb_stmt->close();
}

$paid_remittances_sql = "SELECT COALESCE(SUM(Amount), 0) as total FROM rider_remittances WHERE RiderID = ? AND Status = 'Paid'";
if ($filter === 'today') {
    $paid_remittances_sql .= ' AND DATE(TransactionDate) = CURDATE()';
} elseif ($filter === 'month') {
    $paid_remittances_sql .= ' AND YEAR(TransactionDate) = YEAR(CURDATE()) AND MONTH(TransactionDate) = MONTH(CURDATE())';
}
$paid_stmt = $conn->prepare($paid_remittances_sql);
$paid_stmt->bind_param($types, ...$params);
$paid_stmt->execute();
$paid_res = $paid_stmt->get_result()->fetch_assoc();
$paid_remitted = floatval($paid_res['total']);
$paid_stmt->close();

$unremitted_total = $collected_total - $paid_remitted;
if ($unremitted_total < 0)
    $unremitted_total = 0.00;

echo json_encode([
    'success' => true,
    'stats' => [
        'total' => $total_delivered,
        'successful' => $successful_deliveries,
        'failed' => $failed_deliveries,
        'in_progress' => $in_progress_deliveries
        ,
        'remitted_total' => number_format($remitted_total, 2, '.', ''),
        'unremitted_total' => number_format($unremitted_total, 2, '.', '')
    ],
    'deliveries' => $deliveries
    ,
    'remittances' => $remittances
]);

$conn->close();
?>