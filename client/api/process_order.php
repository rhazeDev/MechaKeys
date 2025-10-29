<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$customer_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT Contact, Address FROM users WHERE ID = ?");
$user_stmt->bind_param("i", $customer_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (empty($user['Contact']) || empty($user['Address'])) {
    echo json_encode(['success' => false, 'message' => 'Please complete your profile information']);
    exit;
}

$order_notes = isset($_POST['order_notes']) ? trim($_POST['order_notes']) : '';
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';

$conn->begin_transaction();

try {
    $cart_sql = "SELECT 
                    c.CartID,
                    c.ProductID,
                    c.VariationID,
                    c.Quantity,
                    c.Price,
                    pv.StockQuantity
                FROM Carts c
                INNER JOIN ProductVariations pv ON c.VariationID = pv.VariationID
                WHERE c.CustomerID = ?
                FOR UPDATE";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $customer_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    $cart_items = [];
    $total_amount = 0;
    $has_stock_issues = false;

    while ($row = $cart_result->fetch_assoc()) {
        if ($row['StockQuantity'] < $row['Quantity']) {
            $has_stock_issues = true;
            break;
        }
        $cart_items[] = $row;
        $total_amount += ($row['Price'] * $row['Quantity']);
    }

    $cart_stmt->close();

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    if ($has_stock_issues) {
        throw new Exception('Some items are out of stock');
    }

    $payment_stmt = $conn->prepare("INSERT INTO Payments (OrderID, Amount, Status, TransactionDate) VALUES (0, ?, 'Pending', NOW())");
    $payment_stmt->bind_param("d", $total_amount);
    $payment_stmt->execute();
    $payment_id = $conn->insert_id;
    $payment_stmt->close();

    $tracking_stmt = $conn->prepare("INSERT INTO Trackings (DeliveryPersonID, DeliveryStatus, LastUpdated) VALUES (0, 'Pending', NOW())");
    $tracking_stmt->execute();
    $tracking_id = $conn->insert_id;
    $tracking_stmt->close();

    $order_stmt = $conn->prepare("INSERT INTO Orders (CustomerID, TrackingID, PaymentID, TotalAmount, PlaceOrdered) VALUES (?, ?, ?, ?, NOW())");
    $order_stmt->bind_param("iiid", $customer_id, $tracking_id, $payment_id, $total_amount);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    $update_payment_stmt = $conn->prepare("UPDATE Payments SET OrderID = ? WHERE PaymentID = ?");
    $update_payment_stmt->bind_param("ii", $order_id, $payment_id);
    $update_payment_stmt->execute();
    $update_payment_stmt->close();

    $order_item_stmt = $conn->prepare("INSERT INTO OrderItems (OrderID, ProductID, VariationID, Quantity, SubTotal) VALUES (?, ?, ?, ?, ?)");
    $update_stock_stmt = $conn->prepare("UPDATE ProductVariations SET StockQuantity = StockQuantity - ? WHERE VariationID = ?");
    $update_sold_stmt = $conn->prepare("UPDATE Products SET TotalSold = TotalSold + ? WHERE ProductID = ?");

    foreach ($cart_items as $item) {
        $subtotal = $item['Price'] * $item['Quantity'];

        $order_item_stmt->bind_param("iiiid", $order_id, $item['ProductID'], $item['VariationID'], $item['Quantity'], $subtotal);
        $order_item_stmt->execute();

        $update_stock_stmt->bind_param("ii", $item['Quantity'], $item['VariationID']);
        $update_stock_stmt->execute();

        $update_sold_stmt->bind_param("ii", $item['Quantity'], $item['ProductID']);
        $update_sold_stmt->execute();
    }

    $order_item_stmt->close();
    $update_stock_stmt->close();
    $update_sold_stmt->close();

    $clear_cart_stmt = $conn->prepare("DELETE FROM Carts WHERE CustomerID = ?");
    $clear_cart_stmt->bind_param("i", $customer_id);
    $clear_cart_stmt->execute();
    $clear_cart_stmt->close();

    $notif_title = "Order Placed Successfully";
    $notif_message = "Your order #" . $order_id . " has been placed successfully. Total: ₱" . number_format($total_amount, 2);
    $notif_type = "order";
    $notif_status = "unread";

    $notif_stmt = $conn->prepare("INSERT INTO Notifications (CustomerID, Title, Message, Type, Status, TimeCreated) VALUES (?, ?, ?, ?, ?, NOW())");
    $notif_stmt->bind_param("issss", $customer_id, $notif_title, $notif_message, $notif_type, $notif_status);
    $notif_stmt->execute();
    $notif_stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>