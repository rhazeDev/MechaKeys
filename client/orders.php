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
                t.DeliveryProof,
                t.LastUpdated,
                t.DeliveryPersonID,
                u.FullName as DeliveryPersonName,
                u.Contact as DeliveryPersonContact,
                u.Location as DeliveryPersonLocation,
                p.Status as PaymentStatus,
                COUNT(oi.OrderItemID) as ItemCount,
                customer.Location as CustomerLocation
            FROM Orders o
            INNER JOIN Trackings t ON o.TrackingID = t.TrackingID
            INNER JOIN Payments p ON o.PaymentID = p.PaymentID
            LEFT JOIN OrderItems oi ON o.OrderID = oi.OrderID
            LEFT JOIN users u ON t.DeliveryPersonID = u.ID AND u.Role = 'delivery'
            LEFT JOIN users customer ON o.CustomerID = customer.ID
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
    <link href="../css/modal-items.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
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
                        $statusIcon = 'fa-circle';
                        
                        if ($status === 'Delivered') {
                            $statusClass = 'status-delivered';
                            $statusIcon = 'fa-check-circle';
                        } elseif ($status === 'Processing' || $status === 'Ready to Deliver') {
                            $statusClass = 'status-processing';
                            $statusIcon = 'fa-cog';
                        } elseif ($status === 'Pending') {
                            $statusClass = 'status-pending';
                            $statusIcon = 'fa-clock';
                        } elseif ($status === 'In Transit' || $status === 'Picked') {
                            $statusClass = 'status-shipped';
                            $statusIcon = 'fa-shipping-fast';
                        } elseif ($status === 'Cancelled') {
                            $statusClass = 'status-cancelled';
                            $statusIcon = 'fa-times-circle';
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
                                    <i class="fas <?php echo $statusIcon; ?>"></i>
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
                                            <strong><?php echo htmlspecialchars($order['DeliveryPersonName']); ?></strong>
                                            <?php if (!empty($order['DeliveryPersonContact'])): ?>
                                                (<?php echo htmlspecialchars($order['DeliveryPersonContact']); ?>)
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($status === 'Delivered' && !empty($order['DeliveryProof'])):
                                    $proofPath = $order['DeliveryProof'];
                                    if ($proofPath && strpos($proofPath, 'mechakeys/') === 0) {
                                        $proofPath = substr($proofPath, strlen('mechakeys/'));
                                    }
                                ?>
                                <div class="delivery-proof-thumb" style="margin: 1rem 0;">
                                    <a href="../<?php echo htmlspecialchars($proofPath, ENT_QUOTES); ?>" target="_blank" rel="noopener">
                                        <img src="../<?php echo htmlspecialchars($proofPath, ENT_QUOTES); ?>" alt="Proof of Delivery" style="max-width:160px; border-radius:8px; border:1px solid #e5e7eb;">
                                    </a>
                                </div>
                                <?php endif; ?>

                                <div class="tracking-timeline">
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Processing', 'Pending', 'Ready to Deliver', 'Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
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
                                        class="timeline-line <?php echo in_array($status, ['Processing', 'Ready to Deliver', 'Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    </div>
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Processing', 'Ready to Deliver', 'Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
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
                                        class="timeline-line <?php echo in_array($status, ['Ready to Deliver', 'Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    </div>
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Ready to Deliver', 'Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Ready to Deliver</span>
                                            <span
                                                class="timeline-date"><?php echo $status === 'Ready to Deliver' ? 'Awaiting pickup' : ''; ?></span>
                                        </div>
                                    </div>
                                    <div
                                        class="timeline-line <?php echo in_array($status, ['Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                    </div>
                                    <div
                                        class="timeline-item <?php echo in_array($status, ['Picked', 'Shipped', 'In Transit', 'Delivered']) ? 'completed' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <span class="timeline-title">Picked Up</span>
                                            <span
                                                class="timeline-date"><?php echo $status === 'Picked' ? 'Picked by courier' : ''; ?></span>
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
                                            <span class="timeline-title">In Transit</span>
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

                                <?php if ($status === 'In Transit' && $order['DeliveryPersonID'] > 0): ?>
                                <div class="track-delivery-section">
                                    <button class="btn-track-delivery" 
                                            data-order-id="<?php echo $order['OrderID']; ?>"
                                            data-customer-location="<?php echo htmlspecialchars($order['CustomerLocation'] ?? '', ENT_QUOTES); ?>"
                                            data-rider-location="<?php echo htmlspecialchars($order['DeliveryPersonLocation'] ?? '', ENT_QUOTES); ?>"
                                            data-rider-name="<?php echo htmlspecialchars($order['DeliveryPersonName'] ?? '', ENT_QUOTES); ?>">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Track Live Delivery
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="order-card-footer">
                                <button class="btn-view-details" onclick="viewOrderDetails(<?php echo $order['OrderID']; ?>)">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </button>
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

    <!-- Live Tracking Modal -->
    <div id="trackingModal" class="modal">
        <div class="modal-content tracking-modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-map-marked-alt"></i> Live Delivery Tracking</h2>
                <button class="modal-close" onclick="closeTrackingModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body tracking-modal-body">
                <div class="tracking-info-bar">
                    <div class="tracking-info-item">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <small>Delivery Rider</small>
                            <strong id="trackingRiderName">Loading...</strong>
                        </div>
                    </div>
                    <div class="tracking-info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <small>ETA</small>
                            <strong id="trackingETA">Calculating...</strong>
                        </div>
                    </div>
                    <div class="tracking-info-item">
                        <i class="fas fa-route"></i>
                        <div>
                            <small>Distance</small>
                            <strong id="trackingDistance">Calculating...</strong>
                        </div>
                    </div>
                </div>
                <div id="trackingMap" class="tracking-map"></div>
                <div class="tracking-legend">
                    <div class="legend-item">
                        <div class="legend-marker customer-marker"><i class="fas fa-home"></i></div>
                        <span>Your Location</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-marker rider-marker"><i class="fas fa-motorcycle"></i></div>
                        <span>Delivery Rider</span>
                    </div>
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
            
                        document.querySelectorAll('.btn-track-delivery').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = parseInt(this.dataset.orderId);
                    const customerLocation = this.dataset.customerLocation;
                    const riderLocation = this.dataset.riderLocation;
                    const riderName = this.dataset.riderName;
                    
                    openTrackingModal(orderId, customerLocation, riderLocation, riderName);
                });
            });
        });

        function viewOrderDetails(orderId) {
            const modal = document.getElementById('orderModal');
            const modalBody = document.getElementById('modalBody');

            modal.style.display = 'flex';
            modalBody.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

            fetch(`api/get_order_details.php?order_id=${orderId}`)
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
                        <div class="modal-item-meta">
                            ${item.color || 'N/A'} • ${item.switch_type || 'N/A'} • ${item.layout || 'N/A'}%
                        </div>
                        <div class="modal-item-qty">Qty: ${item.quantity}</div>
                    </div>
                    <div class="modal-item-price">₱${parseFloat(item.subtotal).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</div>
                </div>
            `).join('');

                        const showTrackingButton = order.delivery_status === 'In Transit' && 
                                      order.delivery_person_location && 
                                      order.customer_location;

            document.getElementById('modalBody').innerHTML = `
                <div class="modal-order-info">
                    <div class="modal-info-row">
                        <span class="modal-label">Order Number:</span>
                        <span class="modal-value">#${String(order.order_id).padStart(6, '0')}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Order Date:</span>
                        <span class="modal-value">${new Date(order.placed_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Status:</span>
                        ${(() => {
                            const s = order.delivery_status || '';
                            const cls = 'status-' + s.toLowerCase().replace(/\s+/g, '-');
                            return `<span class="modal-value status-badge ${cls}">${s}</span>`;
                        })()}
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Payment:</span>
                        <span class="modal-value">${order.payment_status}</span>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-box-open"></i> Order Items</h3>
                    <div class="modal-items-list">
                        ${itemsHtml}
                    </div>
                </div>
                
                <div class="modal-total">
                    <span><i class="fas fa-receipt"></i> Total Amount</span>
                    <span class="total-value">₱${parseFloat(order.total_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                    <p class="delivery-address">${order.address}</p>
                </div>
                
                <div class="modal-section">
                    <h3><i class="fas fa-truck"></i> Delivery Information</h3>
                    <div class="modal-info-row">
                        <span class="modal-label">Order Reference:</span>
                        <span class="modal-value">${order.order_reference}</span>
                    </div>
                    <div class="modal-info-row">
                        <span class="modal-label">Delivery Person:</span>
                        <span class="modal-value">
                            ${order.delivery_person}
                            ${order.delivery_person_contact ? ` (${order.delivery_person_contact})` : ''}
                        </span>
                    </div>
                    ${showTrackingButton ? `
                    <div class="modal-tracking-action">
                        <button class="btn-view-location" onclick="viewOrderLocation(${order.order_id}, '${order.customer_location}', '${order.delivery_person_location}', '${order.delivery_person}')">
                            <i class="fas fa-map-marked-alt"></i>
                            View Order Location
                        </button>
                    </div>
                    ` : ''}
                    ${order.delivery_proof ? `
                    <div class="modal-section">
                        <h3><i class="fas fa-image"></i> Proof of Delivery</h3>
                        <div style="margin-top:0.5rem;">
                            <a href="../${order.delivery_proof}" target="_blank" rel="noopener">
                                <img src="../${order.delivery_proof}" alt="Proof of Delivery" style="max-width:240px; border-radius:8px; border:1px solid #e5e7eb;">
                            </a>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function viewOrderLocation(orderId, customerLocation, riderLocation, riderName) {
                        closeModal();
            
                        currentTrackingOrderId = orderId;
            
                        setTimeout(() => {
                openTrackingModal(orderId, customerLocation, riderLocation, riderName);
            }, 300);
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
                closeTrackingModal();
            }
        });

                let trackingMap = null;
        let trackingMarkers = {};
        let routeLayer = null;
        let currentTrackingOrderId = null;
        let locationUpdateInterval = null;

        function openTrackingModal(orderId, customerLocation, riderLocation, riderName) {
            const modal = document.getElementById('trackingModal');
            modal.style.display = 'flex';
            
            currentTrackingOrderId = orderId;
            
            document.getElementById('trackingRiderName').textContent = riderName || 'Delivery Rider';
            
                        if (!riderLocation || riderLocation.trim() === '') {
                                document.getElementById('trackingMap').innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 2rem;">
                        <i class="fas fa-map-marker-alt" style="font-size: 4rem; color: #3b82f6; margin-bottom: 1rem;"></i>
                        <h3 style="color: #1e293b; margin-bottom: 0.5rem;">Waiting for Rider Location</h3>
                        <p style="color: #64748b; margin-bottom: 1rem;">The delivery rider's location will appear here once they start their route.</p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #3b82f6;">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Checking for location updates...</span>
                        </div>
                    </div>
                `;
                document.getElementById('trackingETA').textContent = 'Waiting...';
                document.getElementById('trackingDistance').textContent = 'Waiting...';
                
                                startLiveLocationUpdates();
                return;
            }
            
                        const customerCoords = customerLocation.split(',').map(parseFloat);
            const riderCoords = riderLocation.split(',').map(parseFloat);
            
                        setTimeout(() => {
                initTrackingMap(customerCoords, riderCoords);
                                startLiveLocationUpdates();
            }, 100);
        }

        function initTrackingMap(customerCoords, riderCoords) {
            const mapContainer = document.getElementById('trackingMap');
            
            if (trackingMap) {
                trackingMap.remove();
            }

            mapboxgl.accessToken = 'pk.eyJ1IjoicmhhemUiLCJhIjoiY21memQycHB5MDFybzJrc2d2MXZiejJ6bCJ9.SO6KCjBMT50xiSTvRy0cIw';
            
            trackingMap = new mapboxgl.Map({
                container: 'trackingMap',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: riderCoords,                 zoom: 13
            });

            trackingMap.on('load', function() {
                                const customerMarker = document.createElement('div');
                customerMarker.className = 'tracking-marker customer-marker';
                customerMarker.innerHTML = '<i class="fas fa-home"></i>';
                
                new mapboxgl.Marker(customerMarker)
                    .setLngLat(customerCoords)                     .setPopup(new mapboxgl.Popup().setHTML('<strong>Delivery Destination</strong>'))
                    .addTo(trackingMap);

                                const riderMarker = document.createElement('div');
                riderMarker.className = 'tracking-marker rider-marker';
                riderMarker.innerHTML = '<i class="fas fa-motorcycle"></i>';
                
                trackingMarkers.rider = new mapboxgl.Marker(riderMarker)
                    .setLngLat(riderCoords)                     .setPopup(new mapboxgl.Popup().setHTML('<strong>Delivery Rider</strong><br>On the way'))
                    .addTo(trackingMap);

                                drawRoute(riderCoords, customerCoords);

                                const bounds = new mapboxgl.LngLatBounds();
                bounds.extend(riderCoords);
                bounds.extend(customerCoords);
                trackingMap.fitBounds(bounds, { padding: 100 });
            });
        }

        function drawRoute(start, end) {
            const url = `https://api.mapbox.com/directions/v5/mapbox/driving/${start[0]},${start[1]};${end[0]},${end[1]}?geometries=geojson&access_token=${mapboxgl.accessToken}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.routes && data.routes.length > 0) {
                        const route = data.routes[0].geometry;
                        
                        if (trackingMap.getSource('route')) {
                            trackingMap.removeLayer('route');
                            trackingMap.removeSource('route');
                        }

                        trackingMap.addSource('route', {
                            'type': 'geojson',
                            'data': {
                                'type': 'Feature',
                                'properties': {},
                                'geometry': route
                            }
                        });

                        trackingMap.addLayer({
                            'id': 'route',
                            'type': 'line',
                            'source': 'route',
                            'layout': {
                                'line-join': 'round',
                                'line-cap': 'round'
                            },
                            'paint': {
                                'line-color': '#3b82f6',
                                'line-width': 4,
                                'line-opacity': 0.8
                            }
                        });

                                                const duration = Math.round(data.routes[0].duration / 60);                         document.getElementById('trackingETA').textContent = `${duration} min`;
                        
                        const distance = (data.routes[0].distance / 1000).toFixed(1);                         document.getElementById('trackingDistance').textContent = `${distance} km`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching route:', error);
                });
        }

        function closeTrackingModal() {
            const modal = document.getElementById('trackingModal');
            modal.style.display = 'none';
            
                        stopLiveLocationUpdates();
            
            if (trackingMap) {
                trackingMap.remove();
                trackingMap = null;
            }
            
            currentTrackingOrderId = null;
        }

        function startLiveLocationUpdates() {
                        stopLiveLocationUpdates();
            
                        locationUpdateInterval = setInterval(() => {
                updateRiderLocation();
            }, 5000);
        }

        function stopLiveLocationUpdates() {
            if (locationUpdateInterval) {
                clearInterval(locationUpdateInterval);
                locationUpdateInterval = null;
            }
        }

        function updateRiderLocation() {
            if (!currentTrackingOrderId) {
                return;
            }

            fetch(`api/get_rider_location.php?order_id=${currentTrackingOrderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.rider_location) {
                                                const newCoords = data.rider_location.split(',').map(parseFloat);
                                                
                                                if (!trackingMap) {
                                                        const modal = document.getElementById('trackingModal');
                            if (modal.style.display === 'flex') {
                                                                fetch(`api/get_order_details.php?order_id=${currentTrackingOrderId}`)
                                    .then(response => response.json())
                                    .then(orderData => {
                                        if (orderData.success && orderData.order.customer_location) {
                                            const customerCoords = orderData.order.customer_location.split(',').map(parseFloat);
                                                                                        document.getElementById('trackingMap').innerHTML = '';
                                                                                        initTrackingMap(customerCoords, newCoords);
                                        }
                                    });
                            }
                            return;
                        }
                        
                                                if (trackingMarkers.rider) {
                            trackingMarkers.rider.setLngLat(newCoords);
                            
                                                        const customerMarkers = document.querySelectorAll('.customer-marker');
                            if (customerMarkers.length > 0) {
                                                                const customerCoords = customerMarkers[0].closest('.mapboxgl-marker')?._lngLat;
                                if (customerCoords) {
                                    drawRoute(newCoords, [customerCoords.lng, customerCoords.lat]);
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating rider location:', error);
                });
        }

        window.onclick = function (event) {
            const orderModal = document.getElementById('orderModal');
            const trackingModal = document.getElementById('trackingModal');
            
            if (event.target === orderModal) {
                closeModal();
            }
            if (event.target === trackingModal) {
                closeTrackingModal();
            }
        }
    </script>
</body>

</html>