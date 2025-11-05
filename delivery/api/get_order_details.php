<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $order_query = $conn->prepare("SELECT o.*, c.FullName, c.Email, c.Address, c.Contact 
                                   FROM orders o
                                   JOIN users c ON o.CustomerID = c.ID
                                   WHERE o.OrderID = ?");
    $order_query->bind_param("i", $order_id);
    $order_query->execute();
    $order_result = $order_query->get_result();

    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $order = $order_result->fetch_assoc();
    $order_query->close();

    $items_query = $conn->prepare("
        SELECT 
            oi.OrderItemID,
            oi.Quantity,
            oi.SubTotal,
            p.Brand,
            p.Model,
            pv.Layout,
            pv.SwitchType,
            pv.Color,
            pv.Price,
            (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath
        FROM orderitems oi
        JOIN products p ON oi.ProductID = p.ProductID
        JOIN productvariations pv ON oi.VariationID = pv.VariationID
        WHERE oi.OrderID = ?
    ");
    $items_query->bind_param("i", $order_id);
    $items_query->execute();
    $items_result = $items_query->get_result();

    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    $items_query->close();

    $tracking_query = $conn->prepare("SELECT * FROM trackings WHERE TrackingID = ?");
    $tracking_query->bind_param("i", $order['TrackingID']);
    $tracking_query->execute();
    $tracking_result = $tracking_query->get_result();
    $tracking = $tracking_result->fetch_assoc();
    $tracking_query->close();

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'tracking' => $tracking
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>