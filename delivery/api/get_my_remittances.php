<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = intval($_SESSION['user_id']);
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$dateFilter = '';
if ($filter === 'today') {
    $dateFilter = ' AND DATE(TransactionDate) = CURDATE()';
} elseif ($filter === 'month') {
    $dateFilter = ' AND YEAR(TransactionDate) = YEAR(CURDATE()) AND MONTH(TransactionDate) = MONTH(CURDATE())';
}

try {
    $remittances = [];
    $remitted_total = 0.00;
    $collected_today = 0.00;
    $unremitted_total = 0.00;

    $sql = "SELECT RemittanceID, Amount, PeriodStart, PeriodEnd, TransactionDate, PaymentMethod, Reference, Status, Notes FROM rider_remittances WHERE RiderID = ?" . $dateFilter . " ORDER BY TransactionDate DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $rider_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        if (empty($row['PaymentMethod']))
            $row['PaymentMethod'] = 'Cash';
        $remittances[] = $row;
        $remitted_total += floatval($row['Amount']);
    }
    $stmt->close();

    $collected_sql = "SELECT COALESCE(SUM(p.Amount), 0) as total FROM payments p
        INNER JOIN orders o ON p.OrderID = o.OrderID
        INNER JOIN trackings t ON o.TrackingID = t.TrackingID
        WHERE p.Status = 'Paid' AND t.DeliveryPersonID = ? AND DATE(p.TransactionDate) = CURDATE()";
    $col_stmt = $conn->prepare($collected_sql);
    $col_stmt->bind_param('i', $rider_id);
    $col_stmt->execute();
    $col_res = $col_stmt->get_result()->fetch_assoc();
    $collected_today = floatval($col_res['total']);
    $col_stmt->close();

    if ($collected_today <= 0) {
        $fallback_sql = "SELECT COALESCE(SUM(o.TotalAmount - o.Discount), 0) as total FROM orders o
            INNER JOIN trackings t ON o.TrackingID = t.TrackingID
            WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered' AND DATE(t.LastUpdated) = CURDATE()";
        $fb_stmt = $conn->prepare($fallback_sql);
        $fb_stmt->bind_param('i', $rider_id);
        $fb_stmt->execute();
        $fb_res = $fb_stmt->get_result()->fetch_assoc();
        $collected_today = floatval($fb_res['total']);
        $fb_stmt->close();
    }
    $unremitted_sql = "SELECT COALESCE(SUM(p.Amount), 0) as total FROM payments p
        INNER JOIN orders o ON p.OrderID = o.OrderID
        INNER JOIN trackings t ON o.TrackingID = t.TrackingID
        LEFT JOIN remittance_orders ro ON o.OrderID = ro.OrderID
        LEFT JOIN rider_remittances rr ON ro.RemittanceID = rr.RemittanceID AND rr.Status = 'Paid'
        WHERE p.Status = 'Paid' AND t.DeliveryPersonID = ? AND rr.RemittanceID IS NULL";
    $un_stmt = $conn->prepare($unremitted_sql);
    $un_stmt->bind_param('i', $rider_id);
    $un_stmt->execute();
    $un_res = $un_stmt->get_result()->fetch_assoc();
    $unremitted_total = floatval($un_res['total']);
    $un_stmt->close();

    if ($unremitted_total <= 0) {
        $fallback_un_sql = "SELECT COALESCE(SUM(o.TotalAmount - o.Discount), 0) as total FROM orders o
            INNER JOIN trackings t ON o.TrackingID = t.TrackingID
            LEFT JOIN remittance_orders ro ON o.OrderID = ro.OrderID
            LEFT JOIN rider_remittances rr ON ro.RemittanceID = rr.RemittanceID AND rr.Status = 'Paid'
            WHERE t.DeliveryPersonID = ? AND t.DeliveryStatus = 'Delivered' AND rr.RemittanceID IS NULL";
        $fub_stmt = $conn->prepare($fallback_un_sql);
        $fub_stmt->bind_param('i', $rider_id);
        $fub_stmt->execute();
        $fub_res = $fub_stmt->get_result()->fetch_assoc();
        $unremitted_total = floatval($fub_res['total']);
        $fub_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'remitted_total' => number_format($remitted_total, 2, '.', ''),
        'collected_today' => number_format($collected_today, 2, '.', ''),
        'unremitted_total' => number_format($unremitted_total, 2, '.', ''),
        'remittances' => $remittances
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>