<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['user_id'];

$orders_sql = "SELECT 
                o.OrderID,
                o.TotalAmount,
                o.PlaceOrdered,
                o.TrackingID,
                t.DeliveryStatus,
                t.LastUpdated,
                t.DeliveryPersonID,
                u.FullName as DeliveryPersonName,
                p.Status as PaymentStatus,
                COUNT(oi.OrderItemID) as ItemCount
            FROM Orders o
            INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
            INNER JOIN Payments p ON o.PaymentID = p.PaymentID
            LEFT JOIN OrderItems oi ON o.OrderID = oi.OrderID
            LEFT JOIN users u ON t.DeliveryPersonID = u.ID AND u.Role = 'delivery'
            WHERE o.CustomerID = ?
            GROUP BY o.OrderID
            ORDER BY o.PlaceOrdered DESC";

$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();

$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

$orders_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/orders.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="main-content">
        <div class="orders-container">
            <div class="orders-header">
                <h1><i class="fas fa-box"></i> My Orders</h1>
                <p class="subtitle">Track and manage your orders</p>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="fas fa-inbox"></i>
                    <h2>No Orders Yet</h2>
                    <p>You haven't placed any orders yet. Start shopping and your orders will appear here.</p>
                    <a href="index.php" class="btn-shop-now">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $status = $order['DeliveryStatus'];
                        $statusClass = 'status-default';
                        if ($status === 'Delivered') {
                            $statusClass = 'status-delivered';
                        } elseif ($status === 'Processing' || $status === 'Pending') {
                            $statusClass = 'status-processing';
                        } elseif ($status === 'Shipped' || $status === 'In Transit') {
                            $statusClass = 'status-shipped';
                        } elseif ($status === 'Cancelled') {
                            $statusClass = 'status-cancelled';
                        }
                        ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div class="order-info-group">
                                    <div class="order-number">
                                        <span class="label">Order #</span>
                                        <span
                                            class="value"><?php echo str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                    <div class="order-date">
                                        <span class="label">Placed on</span>
                                        <span
                                            class="value"><?php echo date('M d, Y', strtotime($order['PlaceOrdered'])); ?></span>
                                    </div>
                                </div>
                                <div class="order-status-badge <?php echo $statusClass; ?>">
                                    <i class="fas fa-circle"></i>
                                    <?php echo htmlspecialchars($status); ?>
                                </div>
                            </div>

                            <div class="order-card-body">
                                <div class="order-details-grid">
                                    <div class="detail-item">
                                        <i class="fas fa-boxes"></i>
                                        <div>
                                            <span class="detail-label">Items</span>
                                            <span class="detail-value"><?php echo $order['ItemCount']; ?> item(s)</span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>
                                            <span class="detail-label">Total Amount</span>
                                            <span
                                                class="detail-value">₱<?php echo number_format($order['TotalAmount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-credit-card"></i>
                                        <div>
                                            <span class="detail-label">Payment</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($order['PaymentStatus']); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-hashtag"></i>
                                        <div>
                                            <span class="detail-label">Order Ref</span>
                                            <span
                                                class="detail-value">MK-<?php echo str_pad($order['TrackingID'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($order['DeliveryPersonID'] > 0 && !empty($order['DeliveryPersonName'])): ?>
                                    <div class="courier-info">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Delivery Person:
                                            <strong><?php echo htmlspecialchars($order['DeliveryPersonName']); ?></strong></span>
                                    </div>
                                <?php endif; ?>

                                <div class="tracking-timeline">
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Processing', 'Pending', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Order Placed</span>
                                            <span
                                                class="timeline-date"><?php echo date('M d, h:i A', strtotime($order['PlaceOrdered'])); ?></span>
                                        </div>
                                    </div>
                                    <div
                                        class="timeline-line <?php echo in_array($status, ['Processing', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    </div>
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Processing', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Processing</span>
                                            <span
                                                class="timeline-date"><?php echo $status === 'Processing' ? 'In progress' : ''; ?></span>
                                        </div>
                                    </div>
                                    <div
                                        class="timeline-line <?php echo in_array($status, ['Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    </div>
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Shipped</span>
                                            <span
                                                class="timeline-date"><?php echo in_array($status, ['Shipped', 'In Transit']) ? 'Out for delivery' : ''; ?></span>
                                        </div>
                                    </div>
                                    <div class="timeline-line <?php echo $status === 'Delivered' ? 'completed' : ''; ?>"></div>
                                    <div class="timeline-item <?php echo $status === 'Delivered' ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Delivered</span>
                                            <span
                                                class="timeline-date"><?php echo $status === 'Delivered' ? date('M d, h:i A', strtotime($order['LastUpdated'])) : ''; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="order-card-footer">
                                <button class="btn-view-details" onclick="viewOrderDetails(<?php echo $order['OrderID']; ?>)">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </button>
                                <?php if ($status === 'Delivered'): ?>
                                    <button class="btn-secondary" onclick="alert('Review feature coming soon!')">
                                        <i class="fas fa-star"></i>
                                        Leave Review
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-receipt"></i> Order Details</h2>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalBody" class="modal-body">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (profileDropdown && !profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        });

        function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderModal');
            const modalBody = document.getElementById('modalBody');

            modal.style.display = 'flex';
            modalBody.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderOrderDetails(data);
                    } else {
                        modalBody.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-circle"></i> Failed to load order details</div>';
                });
        }

        function renderOrderDetails(data) {
            const order = data.order;
            const items = data.items;

            let itemsHtml = items.map(item => `
                <div class="modal-order-item">
                    <img src="../${item.image}" alt="${item.product_name}" onerror="this.src='../products/placeholder.png'">
                    <div class="modal-item-info">
                        <div class="modal-item-name">${item.product_name}</div>
                        <div class="modal-item-specs">
                            ${item.color} • ${item.switch_type} • ${item.layout}%
                        </div>
                        <div class="modal-item-qty">Qty: ${item.quantity}</div>
                    </div>
                    <div class="modal-item-price">₱${parseFloat(item.subtotal).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</div>
                </div>
            `).join('');

            document.getElementById('modalBody').innerHTML = `
                <div class="modal-order-info">
                    <div class="modal-info-row">
                        <span class="modal-label">Order Number:</span>
                        <span class="modal-value">#${String(order.order_id).padStart(6, '0')}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Order Date:</span>
                        <span class="modal-value">${new Date(order.placed_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Status:</span>
                        <span class="modal-value status-badge">${order.delivery_status}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Payment:</span>
                        <span class="modal-value">${order.payment_status}</span>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-box"></i> Order Items</h3>
                    <div class="modal-items-list">
                        ${itemsHtml}
                    </div>
                </div>
                
                <div class="modal-total">
                    <span>Total Amount</span>
                    <span class="total-value">₱${parseFloat(order.total_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                    <p>${order.address}</p>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-truck"></i> Delivery Information</h3>
                    <div class="modal-info-row">
                        <span class="modal-label">Order Reference:</span>
                        <span class="modal-value">${order.order_reference}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Delivery Person:</span>
                        <span class="modal-value">${order.delivery_person}</span>
                    </div>
                </div>
            `;
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>

</html>