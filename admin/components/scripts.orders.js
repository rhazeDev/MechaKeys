let deliveryRiders = [];
let autoMarked = false;

async function loadOrders(status = 'all') {
    try {
        const response = await fetch(`api/get_orders.php?status=${status}`, {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
            const stats = result.stats || {};
            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            };
            setText('pendingOrdersCount', stats.pending || 0);
            setText('assignedOrdersCount', stats.assigned || 0);
            setText('shippedOrdersCount', stats.shipped || 0);
            setText('deliveredOrdersCount', stats.delivered || 0);
            setText('receivedOrdersCount', stats.received || 0);

            if (!autoMarked) {
                autoMarked = true;
                triggerAutoMarkOrders(true);
            }

            deliveryRiders = result.riders || [];

            const tbody = document.getElementById('ordersTableBody');
            if (!tbody) return;

            if (result.orders && result.orders.length > 0) {
                tbody.innerHTML = result.orders.map(order => {
                    const statusClass = getStatusClass(order.DeliveryStatus);

                    return `
                        <tr>
                            <td>#${String(order.OrderID).padStart(6, '0')}</td>
                            <td>
                                <div>
                                    <strong>${escapeHtml(order.CustomerName)}</strong><br>
                                    <small style="color: #666;">${escapeHtml(order.CustomerEmail)}</small>
                                </div>
                            </td>
                            <td>${order.ItemCount} item(s)</td>
                            <td>₱${parseFloat(order.TotalAmount - (order.Discount || 0)).toFixed(2)}</td>
                            <td><span class="badge ${statusClass}">${escapeHtml(order.DeliveryStatus)}</span></td>
                            <td>
                                ${order.DeliveryPersonName ?
                            `<strong>${escapeHtml(order.DeliveryPersonName)}</strong>` :
                            '<span style="color: #999;">Not Assigned</span>'}
                            </td>
                            <td>${new Date(order.PlaceOrdered).toLocaleDateString()}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-view" onclick="viewOrderDetails(${order.OrderID})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${order.DeliveryStatus === 'Processing' ? `
                                        <button class="btn-icon btn-ready" onclick="setOrderReadyToDeliver(${order.OrderID}, ${order.TrackingID})" title="Mark Ready to Deliver">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    ` : ''}
                                    ${!order.DeliveryPersonID && order.DeliveryStatus !== 'Pending' && order.DeliveryStatus !== 'Cancelled' ? `
                                        <button class="btn-icon btn-assign" onclick="openAssignDelivery(${order.OrderID}, ${order.TrackingID})" title="Assign Rider">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No orders found</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
        const tbody = document.getElementById('ordersTableBody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="color: red;">Failed to load orders</td></tr>';
    }
}

function getStatusClass(status) {
    const statusMap = {
        'Pending': 'warning',
        'Processing': 'info',
        'Ready to Deliver': 'success',
        'Assigned': 'primary',
        'Shipped': 'primary',
        'In Transit': 'primary',
        'Delivered': 'success',
        'Order Received': 'warning',
        'Cancelled': 'error'
    };
    return statusMap[status] || 'default';
}

function filterOrders() {
    const status = document.getElementById('orderStatusFilter').value;
    loadOrders(status);
}

function refreshOrders() {
    const status = document.getElementById('orderStatusFilter').value;
    loadOrders(status);
}

function triggerAutoMarkOrders(silent = false) {
    if (!silent && !confirm('This will auto-mark all delivered orders older than 7 days as received. Continue?')) {
        return;
    }

    const btn = event && event.target;
    const originalText = btn ? btn.innerHTML : null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    fetch('api/auto_mark_order_received.php', {
        method: 'POST',
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }

            if (data.success) {
                const count = data.count || 0;
                if (count > 0) {
                    if (!silent) showSuccess(`Successfully auto-marked ${count} order(s) as received!`, 'Auto-Mark Complete');
                    refreshOrders();
                } else {
                    if (!silent) showInfo('No orders needed auto-marking.', 'No Changes');
                }
            } else {
                if (!silent) showError(data.message || 'Failed to auto-mark orders', 'Error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
            if (!silent) showError('Failed to auto-mark orders', 'Error');
        });
}

async function setOrderReadyToDeliver(orderId, trackingId) {
    const modal = document.getElementById('readyToDeliverModal');
    document.getElementById('readyOrderId').value = orderId;
    document.getElementById('readyTrackingId').value = trackingId;

    const select = document.getElementById('readyDeliveryRider');
    select.innerHTML = '<option value="">-- Loading riders... --</option>';

    try {
        const response = await fetch('api/get_delivery_riders.php', {
            credentials: 'same-origin'
        });
        const riders = await response.json();

        if (riders && riders.length > 0) {
            select.innerHTML = '<option value="">-- Select Rider --</option>' +
                riders.map(rider => `<option value="${rider.ID}">${escapeHtml(rider.FullName)} (${escapeHtml(rider.Email)})</option>`).join('');
        } else {
            select.innerHTML = '<option value="">-- No riders available --</option>';
            showError('No delivery riders found. Please add delivery users first.', 'No Riders');
        }
    } catch (error) {
        console.error('Error loading riders:', error);
        select.innerHTML = '<option value="">-- Error loading riders --</option>';
    }

    modal.style.display = 'flex';
}

function closeReadyToDeliverModal() {
    const modal = document.getElementById('readyToDeliverModal');
    modal.style.display = 'none';
    document.getElementById('readyToDeliverForm').reset();
}

async function submitReadyToDeliver(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('delivery_status', 'Ready to Deliver');
    formData.append('payment_status', 'Pending');

    try {
        const response = await fetch('api/update_order_status.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeReadyToDeliverModal();
            showToast('Order marked as Ready to Deliver and assigned to rider', 'success', 'Status Updated');
            refreshOrders();
        } else {
            showError(result.message || 'Failed to update order status', 'Update Failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while updating the order', 'Error');
    }
}

async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const modalBody = document.getElementById('orderDetailsBody');
    const modalFooter = document.getElementById('orderDetailsFooter');

    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modalFooter.style.display = 'none';

    try {
        const response = await fetch(`api/get_order_details.php?order_id=${orderId}`, {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
            const order = result.order;
            const items = result.items;

            const itemsHtml = items.map(item => `
                <div class="order-item-row">
                    <img src="/${item.ImagePath || 'products/placeholder.png'}" 
                         alt="${escapeHtml(item.Brand)} ${escapeHtml(item.Model)}" 
                         class="order-item-image"
                         onerror="this.src='/products/placeholder.png'">
                    <div class="order-item-info">
                        <div class="order-item-name">${escapeHtml(item.Brand)} ${escapeHtml(item.Model)}</div>
                        <div class="order-item-specs">
                            ${escapeHtml(item.Layout)}% • ${escapeHtml(item.SwitchType)} • ${escapeHtml(item.Color)} • Qty: ${item.Quantity}
                        </div>
                    </div>
                    <div class="order-item-price">₱${parseFloat(item.SubTotal).toFixed(2)}</div>
                </div>
            `).join('');

            modalBody.innerHTML = `
                <div class="order-details-grid">
                    <div class="detail-box">
                        <label>Order Number</label>
                        <div class="value">#${String(order.OrderID).padStart(6, '0')}</div>
                    </div>
                    <div class="detail-box">
                        <label>Order Date</label>
                        <div class="value">${new Date(order.PlaceOrdered).toLocaleDateString()}</div>
                    </div>
                    <div class="detail-box">
                        <label>Customer</label>
                        <div class="value">${escapeHtml(order.CustomerName)}</div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Status</label>
                        <div class="value">
                            <span class="badge ${getStatusClass(order.DeliveryStatus)}">${escapeHtml(order.DeliveryStatus)}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Payment Status</label>
                        <div class="value">
                            <span class="badge ${order.PaymentStatus === 'Paid' ? 'success' : 'warning'}">${escapeHtml(order.PaymentStatus)}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Rider</label>
                        <div class="value">${escapeHtml(order.DeliveryPersonName || 'Not Assigned')}</div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                    <div class="detail-box">
                        <div class="value">${escapeHtml(order.CustomerAddress)}</div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-box"></i> Order Items</h4>
                    <div class="order-items-list">
                        ${itemsHtml}
                    </div>
                </div>
                ${order.DeliveryProof ? `
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-image"></i> Proof of Delivery</h4>
                    <div class="detail-box" style="text-align: center;">
                        <img src="/${escapeHtml(order.DeliveryProof)}" alt="Proof of Delivery" style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;" onclick="openProofModal('/${escapeHtml(order.DeliveryProof)}')">
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                            <a href="/${escapeHtml(order.DeliveryProof)}" target="_blank" style="color: #2196F3; text-decoration: none;">
                                <i class="fas fa-external-link-alt"></i> Open Full Size
                            </a>
                        </p>
                    </div>
                </div>
                ` : ''}
                <div class="order-total">
                    <div class="total-breakdown">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>₱${parseFloat(order.TotalAmount).toFixed(2)}</span>
                        </div>
                        ${order.Discount > 0 ? `
                        <div class="total-row discount-row">
                            <span>Coin Discount:</span>
                            <span>-₱${parseFloat(order.Discount).toFixed(2)}</span>
                        </div>
                        ` : ''}
                        <div class="total-row final-row">
                            <span><strong>Total Amount:</strong></span>
                            <span><strong>₱${parseFloat(order.TotalAmount - (order.Discount || 0)).toFixed(2)}</strong></span>
                        </div>
                    </div>
                </div>
            `;

            if (order.DeliveryStatus === 'Pending') {
                modalFooter.style.display = 'flex';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" onclick="closeOrderDetailsModal()">Close</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-danger" onclick="handleOrderAction(${order.OrderID}, ${order.TrackingID}, 'cancel')">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                        <button type="button" class="btn btn-success" onclick="handleOrderAction(${order.OrderID}, ${order.TrackingID}, 'approve')">
                            <i class="fas fa-check"></i> Approve Order
                        </button>
                    </div>
                `;
            } else {
                modalFooter.style.display = 'flex';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" onclick="closeOrderDetailsModal()">Close</button>
                `;
            }
        } else {
            modalBody.innerHTML = `<div class="alert error">${escapeHtml(result.message)}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert error">Failed to load order details</div>';
    }
}

function closeOrderDetailsModal() {
    const el = document.getElementById('orderDetailsModal');
    if (el) el.style.display = 'none';
    const footer = document.getElementById('orderDetailsFooter');
    if (footer) footer.style.display = 'none';
}

async function handleOrderAction(orderId, trackingId, action) {
    const actionText = action === 'approve' ? 'approve' : 'cancel';
    const confirmMessage = action === 'approve'
        ? 'Are you sure you want to approve this order? The order status will be set to <span style="color:#2196F3;font-weight:600;">Processing</span>.'
        : 'Are you sure you want to cancel this order? Stock quantities will be restored.';

    showCustomConfirm(confirmMessage, async () => {
        const modalFooter = document.getElementById('orderDetailsFooter');
        const originalFooterContent = modalFooter ? modalFooter.innerHTML : '';
        if (modalFooter) modalFooter.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Processing...</div>';

        try {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('tracking_id', trackingId);
            formData.append('action', action);

            const response = await fetch('api/approve_cancel_order.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success', action === 'approve' ? 'Approved' : 'Cancelled');
                closeOrderDetailsModal();
                refreshOrders();
            } else {
                showError(result.message || `Failed to ${actionText} order`, 'Action Failed');
                if (modalFooter) modalFooter.innerHTML = originalFooterContent;
            }
        } catch (error) {
            console.error('Error:', error);
            showError(`An error occurred while trying to ${actionText} the order`, 'Error');
            if (modalFooter) modalFooter.innerHTML = originalFooterContent;
        }
    });
}

function openAssignDelivery(orderId, trackingId) {
    const orderIdField = document.getElementById('assignOrderId');
    const trackingIdField = document.getElementById('assignTrackingId');
    if (orderIdField) orderIdField.value = orderId;
    if (trackingIdField) trackingIdField.value = trackingId;

    const select = document.getElementById('deliveryRider');
    if (select) {
        select.innerHTML = '<option value="">-- Select Rider --</option>' +
            deliveryRiders.map(rider =>
                `<option value="${rider.ID}">${escapeHtml(rider.FullName)} - ${escapeHtml(rider.Contact)}</option>`
            ).join('');
    }

    const modal = document.getElementById('assignDeliveryModal');
    if (modal) modal.style.display = 'flex';
}

function closeAssignDeliveryModal() {
    const modal = document.getElementById('assignDeliveryModal');
    if (modal) modal.style.display = 'none';
    const form = document.getElementById('assignDeliveryForm');
    if (form) form.reset();
}

async function submitAssignDelivery(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('api/assign_delivery.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            closeAssignDeliveryModal();
            refreshOrders();
        } else {
            console.error('Error:', result.message);
            showError(result.message || 'Failed to assign delivery', 'Assign Failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Failed to assign delivery', 'Error');
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

window.onclick = function (event) {
    if (event.target.classList && (event.target.classList.contains('modal') || event.target.classList.contains('modal-alt'))) {
        event.target.classList.remove('active');
    }
    if (event.target.id === 'readyToDeliverModal') {
        closeReadyToDeliverModal();
    }
    if (event.target.id === 'assignDeliveryModal') {
        closeAssignDeliveryModal();
    }
    if (event.target.id === 'orderDetailsModal') {
        closeOrderDetailsModal();
    }
}

window.addEventListener('click', function (event) {
    if (event.target.id === 'orderDetailsModal') {
        closeOrderDetailsModal();
    }
    if (event.target.id === 'assignDeliveryModal') {
        closeAssignDeliveryModal();
    }
});
