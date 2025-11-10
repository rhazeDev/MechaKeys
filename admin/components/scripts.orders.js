let deliveryRiders = [];

async function loadOrders(status = 'all') {
    try {
        const response = await fetch(`api/get_orders.php?status=${status}`, {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('pendingOrdersCount').textContent = result.stats.pending || 0;
            document.getElementById('assignedOrdersCount').textContent = result.stats.assigned || 0;
            document.getElementById('shippedOrdersCount').textContent = result.stats.shipped || 0;
            document.getElementById('deliveredOrdersCount').textContent = result.stats.delivered || 0;

            deliveryRiders = result.riders;

            const tbody = document.getElementById('ordersTableBody');
            if (result.orders && result.orders.length > 0) {
                tbody.innerHTML = result.orders.map(order => {
                    const statusClass = getStatusClass(order.DeliveryStatus);
                    order.PaymentStatus === 'Failed' ? 'error' : 'warning';

                    let returnStatusHtml = '<span style="color: #999;">—</span>';
                    if (order.ReturnID) {
                        const returnStatusClass = order.ReturnStatus === 'Pending' ? 'warning' :
                            order.ReturnStatus === 'Approved' ? 'info' :
                                order.ReturnStatus === 'Rejected' ? 'error' :
                                    order.ReturnStatus === 'Returned' ? 'info' :
                                        'success';
                        returnStatusHtml = `<span class="badge ${returnStatusClass}">${order.ReturnStatus}</span>`;
                    }

                    let returnActionHtml = '';
                    if (order.ReturnID && (order.ReturnStatus === 'Pending' || order.ReturnStatus === 'Approved' || order.ReturnStatus === 'Picked' || order.ReturnStatus === 'In Transit')) {
                        const buttonTitle = order.ReturnStatus === 'Pending' ? 'View Return Request' : 'View Return Details';
                        const buttonIcon = order.ReturnStatus === 'Pending' ? 'fas fa-undo' : 'fas fa-eye';
                        returnActionHtml = `<button class="btn-icon btn-info" onclick="viewReturnDetails(${order.OrderID})" title="${buttonTitle}">
                            <i class="${buttonIcon}"></i>
                        </button>`;
                    }

                    return `
                        <tr>
                            <td>#${String(order.OrderID).padStart(6, '0')}</td>
                            <td>
                                <div>
                                    <strong>${order.CustomerName}</strong><br>
                                    <small style="color: #666;">${order.CustomerEmail}</small>
                                </div>
                            </td>
                            <td>${order.ItemCount} item(s)</td>
                            <td>₱${parseFloat(order.TotalAmount - order.Discount).toFixed(2)}</td>
                            <td><span class="badge ${statusClass}">${order.DeliveryStatus}</span></td>
                            <td>${returnStatusHtml}</td>
                            <td>
                                ${order.DeliveryPersonName ?
                            `<strong>${order.DeliveryPersonName}</strong>` :
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
                                    ${returnActionHtml}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">No orders found</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
        document.getElementById('ordersTableBody').innerHTML =
            '<tr><td colspan="9" class="text-center" style="color: red;">Failed to load orders</td></tr>';
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
                riders.map(rider => `<option value="${rider.ID}">${rider.FullName} (${rider.Email})</option>`).join('');
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
                         alt="${item.Brand} ${item.Model}" 
                         class="order-item-image"
                         onerror="this.src='/products/placeholder.png'">
                    <div class="order-item-info">
                        <div class="order-item-name">${item.Brand} ${item.Model}</div>
                        <div class="order-item-specs">
                            ${item.Layout}% • ${item.SwitchType} • ${item.Color} • Qty: ${item.Quantity}
                        </div>
                    </div>
                    <div class="order-item-price">₱${parseFloat(item.SubTotal).toFixed(2)}</div>
                </div>
            `).join('');

            let returnSectionHtml = '';
            if (order.ReturnID) {
                try {
                    const returnResponse = await fetch(`api/get_return_by_order.php?order_id=${orderId}`, {
                        credentials: 'same-origin'
                    });
                    const returnResult = await returnResponse.json();

                    if (returnResult.success) {
                        const returnData = returnResult.return;

                        let statusBadgeClass = 'warning';
                        if (returnData.status === 'Approved') statusBadgeClass = 'info';
                        else if (returnData.status === 'Rejected') statusBadgeClass = 'error';
                        else if (returnData.status === 'Returned') statusBadgeClass = 'success';
                        else if (returnData.status === 'Picked') statusBadgeClass = 'primary';
                        else if (returnData.status === 'In Transit') statusBadgeClass = 'primary';

                        let riderInfoHtml = '';
                        if (returnData.return_rider_name) {
                            let deliveryStatusBadge = 'warning';
                            if (returnData.return_delivery_status === 'Picked') deliveryStatusBadge = 'info';
                            else if (returnData.return_delivery_status === 'In Transit') deliveryStatusBadge = 'primary';
                            else if (returnData.return_delivery_status === 'Delivered') deliveryStatusBadge = 'success';

                            riderInfoHtml = `
                                <div class="return-pickup-info">
                                    <h5><i class="fas fa-truck"></i> Return Pickup Details</h5>
                                    <div class="pickup-details-grid">
                                        <div class="pickup-detail">
                                            <label>Rider:</label>
                                            <span><strong>${escapeHtml(returnData.return_rider_name)}</strong></span>
                                        </div>
                                        <div class="pickup-detail">
                                            <label>Contact:</label>
                                            <span>${escapeHtml(returnData.return_rider_contact || '—')}</span>
                                        </div>
                                        <div class="pickup-detail">
                                            <label>Status:</label>
                                            <span class="badge ${deliveryStatusBadge}">${escapeHtml(returnData.return_delivery_status || 'Assigned')}</span>
                                        </div>
                                        <div class="pickup-detail">
                                            <label>Tracking:</label>
                                            <span>${returnData.return_tracking_id ? '#' + String(returnData.return_tracking_id).padStart(6, '0') : '—'}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else if (returnData.status === 'Approved') {
                            riderInfoHtml = `
                                <div class="return-pickup-info">
                                    <div class="status-message warning">
                                        <i class="fas fa-user-clock"></i> Waiting for rider assignment
                                    </div>
                                </div>
                            `;
                        }

                        let actionButtonsHtml = '';
                        if (returnData.status === 'Pending') {
                            actionButtonsHtml = `
                                <div class="return-actions">
                                    <button class="btn btn-sm btn-primary" onclick="openAssignReturnRiderModal(${returnData.return_id}, ${returnData.order_id})">
                                        <i class="fas fa-user-check"></i> Assign Rider
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="openRejectReturnModal(${returnData.return_id})">
                                        <i class="fas fa-times-circle"></i> Reject
                                    </button>
                                </div>
                            `;
                        } else if (returnData.status === 'Approved' && !returnData.return_rider_name) {
                            actionButtonsHtml = `
                                <div class="return-actions">
                                    <button class="btn btn-sm btn-primary" onclick="openAssignReturnRiderModal(${returnData.return_id}, ${returnData.order_id})">
                                        <i class="fas fa-user-plus"></i> Assign Pickup Rider
                                    </button>
                                </div>
                            `;
                        }

                        returnSectionHtml = `
                            <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
                                <h4 style="margin-bottom: 15px; color: #333;"><i class="fas fa-undo"></i> Return Request</h4>
                                
                                <div class="return-header-info" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <div>
                                        <span class="badge ${statusBadgeClass}">${returnData.status}</span>
                                        <small style="color: #666; margin-left: 10px;">Submitted: ${new Date(returnData.created_at).toLocaleDateString()}</small>
                                    </div>
                                    <button class="btn btn-sm btn-info" onclick="viewReturnDetails(${orderId})">
                                        <i class="fas fa-external-link-alt"></i> View Full Details
                                    </button>
                                </div>

                                <div style="margin-bottom: 15px;">
                                    <strong>Return Reason:</strong>
                                    <div style="margin-top: 5px; padding: 10px; background: white; border-radius: 6px; border: 1px solid #e9ecef;">
                                        ${escapeHtml(returnData.return_reason)}
                                    </div>
                                </div>

                                ${returnData.proof_image ? `
                                <div style="margin-bottom: 15px;">
                                    <strong>Proof of Item Condition:</strong>
                                    <div style="margin-top: 5px;">
                                        <img src="../${returnData.proof_image}" alt="Proof Image" style="max-width: 200px; border-radius: 6px; border: 1px solid #ddd;">
                                    </div>
                                </div>
                                ` : ''}

                                ${riderInfoHtml}

                                ${returnData.admin_message ? `
                                <div style="margin-bottom: 15px;">
                                    <strong>Admin Message:</strong>
                                    <div style="margin-top: 5px; padding: 10px; background: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                                        ${escapeHtml(returnData.admin_message)}
                                    </div>
                                </div>
                                ` : ''}

                                ${actionButtonsHtml}
                            </div>
                        `;
                    }
                } catch (returnError) {
                    console.error('Error loading return details:', returnError);
                    returnSectionHtml = `
                        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <i class="fas fa-exclamation-triangle"></i> Unable to load return details
                        </div>
                    `;
                }
            }

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
                        <div class="value">${order.CustomerName}</div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Status</label>
                        <div class="value">
                            <span class="badge ${getStatusClass(order.DeliveryStatus)}">${order.DeliveryStatus}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Payment Status</label>
                        <div class="value">
                            <span class="badge ${order.PaymentStatus === 'Paid' ? 'success' : 'warning'}">${order.PaymentStatus}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Rider</label>
                        <div class="value">${order.DeliveryPersonName || 'Not Assigned'}</div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                    <div class="detail-box">
                        <div class="value">${order.CustomerAddress}</div>
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
                        <img src="/${order.DeliveryProof}" alt="Proof of Delivery" style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;" onclick="openProofModal('/${order.DeliveryProof}')">
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                            <a href="/${order.DeliveryProof}" target="_blank" style="color: #2196F3; text-decoration: none;">
                                <i class="fas fa-external-link-alt"></i> Open Full Size
                            </a>
                        </p>
                    </div>
                </div>
                ` : ''}
                ${returnSectionHtml}
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
                            <span><strong>₱${parseFloat(order.TotalAmount - order.Discount).toFixed(2)}</strong></span>
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
            modalBody.innerHTML = `<div class="alert error">${result.message}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert error">Failed to load order details</div>';
    }
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
    document.getElementById('orderDetailsFooter').style.display = 'none';
}

async function handleOrderAction(orderId, trackingId, action) {
    const actionText = action === 'approve' ? 'approve' : 'cancel';
    const confirmMessage = action === 'approve'
        ? 'Are you sure you want to approve this order? The order status will be set to <span style="color:#2196F3;font-weight:600;">Processing</span>.'
        : 'Are you sure you want to cancel this order? Stock quantities will be restored.';

    showCustomConfirm(confirmMessage, async () => {
        const modalFooter = document.getElementById('orderDetailsFooter');
        const originalFooterContent = modalFooter.innerHTML;
        modalFooter.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Processing...</div>';

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
                modalFooter.innerHTML = originalFooterContent;
            }
        } catch (error) {
            console.error('Error:', error);
            showError(`An error occurred while trying to ${actionText} the order`, 'Error');
            modalFooter.innerHTML = originalFooterContent;
        }
    });
}

function openAssignDelivery(orderId, trackingId) {
    document.getElementById('assignOrderId').value = orderId;
    document.getElementById('assignTrackingId').value = trackingId;

    const select = document.getElementById('deliveryRider');
    select.innerHTML = '<option value="">-- Select Rider --</option>' +
        deliveryRiders.map(rider =>
            `<option value="${rider.ID}">${rider.FullName} - ${rider.Contact}</option>`
        ).join('');

    document.getElementById('assignDeliveryModal').style.display = 'flex';
}

function closeAssignDeliveryModal() {
    document.getElementById('assignDeliveryModal').style.display = 'none';
    document.getElementById('assignDeliveryForm').reset();
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
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function viewReturnDetails(orderId) {
    try {
        const response = await fetch(`api/get_return_by_order.php?order_id=${orderId}`, {
            credentials: 'same-origin'
        });

        if (!response.ok) {
            alert('Error loading return details: ' + response.status + ' ' + response.statusText);
            return;
        }

        const result = await response.json();

        if (!result.success) {
            alert('Error: ' + (result.message || 'Failed to load return details'));
            return;
        }

        const returnData = result.return;

        let statusBadgeClass = 'warning';
        if (returnData.status === 'Approved') statusBadgeClass = 'info';
        else if (returnData.status === 'Rejected') statusBadgeClass = 'error';
        else if (returnData.status === 'Returned') statusBadgeClass = 'success';
        else if (returnData.status === 'Picked') statusBadgeClass = 'primary';
        else if (returnData.status === 'In Transit') statusBadgeClass = 'primary';

        let modalTitle = 'Return Request Details';
        if (returnData.status === 'Approved') modalTitle = 'Approved Return Details';
        else if (returnData.status === 'Picked' || returnData.status === 'In Transit') modalTitle = 'Return Pickup in Progress';
        else if (returnData.status === 'Returned') modalTitle = 'Completed Return Details';

        let actionButtonsHtml = '';
        if (returnData.status === 'Pending') {
            actionButtonsHtml = `
                <button class="btn btn-primary" onclick="openAssignReturnRiderModal(${returnData.return_id}, ${returnData.order_id})">
                    <i class="fas fa-user-check"></i> Assign Rider
                </button>
                <button class="btn btn-danger" onclick="openRejectReturnModal(${returnData.return_id})">
                    <i class="fas fa-times-circle"></i> Reject Return
                </button>
            `;
        } else if (returnData.status === 'Approved' && !returnData.return_rider_name) {
            actionButtonsHtml = `
                <button class="btn btn-primary" onclick="openAssignReturnRiderModal(${returnData.return_id}, ${returnData.order_id})">
                    <i class="fas fa-user-plus"></i> Assign Pickup Rider
                </button>
            `;
        }

        let riderInfoHtml = '';
        if (returnData.return_rider_name) {
            let deliveryStatusBadge = 'warning';
            if (returnData.return_delivery_status === 'Picked') deliveryStatusBadge = 'info';
            else if (returnData.return_delivery_status === 'In Transit') deliveryStatusBadge = 'primary';
            else if (returnData.return_delivery_status === 'Delivered') deliveryStatusBadge = 'success';

            riderInfoHtml = `
                <div class="info-section">
                    <h4><i class="fas fa-truck"></i> Return Pickup Details</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Assigned Rider:</label>
                            <span><strong>${escapeHtml(returnData.return_rider_name)}</strong></span>
                        </div>
                        <div class="info-item">
                            <label>Rider Contact:</label>
                            <span>${escapeHtml(returnData.return_rider_contact || '—')}</span>
                        </div>
                        <div class="info-item">
                            <label>Pickup Status:</label>
                            <span class="badge ${deliveryStatusBadge}">${escapeHtml(returnData.return_delivery_status || 'Assigned')}</span>
                        </div>
                        <div class="info-item">
                            <label>Tracking ID:</label>
                            <span>${returnData.return_tracking_id ? '#' + String(returnData.return_tracking_id).padStart(6, '0') : '—'}</span>
                        </div>
                    </div>
                    ${returnData.return_delivery_status === 'In Transit' ? `
                    <div style="margin-top: 15px; padding: 12px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                        <p style="margin: 0; color: #1565c0; font-weight: 500;">
                            <i class="fas fa-info-circle"></i> Rider is currently en route for pickup
                        </p>
                    </div>
                    ` : ''}
                    ${returnData.return_delivery_status === 'Picked' ? `
                    <div style="margin-top: 15px; padding: 12px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4caf50;">
                        <p style="margin: 0; color: #2e7d32; font-weight: 500;">
                            <i class="fas fa-check-circle"></i> Item has been picked up by the rider
                        </p>
                    </div>
                    ` : ''}
                </div>
            `;
        } else if (returnData.status === 'Approved') {
            riderInfoHtml = `
                <div class="info-section">
                    <h4><i class="fas fa-clock"></i> Return Pickup Status</h4>
                    <div style="padding: 12px; background: #fff3e0; border-radius: 8px; border-left: 4px solid #ff9800;">
                        <p style="margin: 0; color: #e65100; font-weight: 500;">
                            <i class="fas fa-user-clock"></i> Waiting for rider assignment
                        </p>
                        <small style="color: #bf360c;">A delivery rider needs to be assigned to pick up the return item.</small>
                    </div>
                </div>
            `;
        }

        let proofImageHtml = '';
        if (returnData.proof_image) {
            proofImageHtml = `
                <div class="info-section">
                    <h4><i class="fas fa-image"></i> Proof of Item Condition</h4>
                    <img src="../${returnData.proof_image}" alt="Proof Image" style="max-width: 300px; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px;">
                </div>
            `;
        }

        let orderItemsHtml = '';
        if (returnData.order_items && returnData.order_items.length > 0) {
            const itemsRows = returnData.order_items.map(item => `
                <tr>
                    <td>${escapeHtml(item.product_name)}</td>
                    <td style="text-align: center;">${item.quantity}</td>
                    <td style="text-align: right;">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td style="text-align: right;">₱${parseFloat(item.total).toFixed(2)}</td>
                </tr>
            `).join('');

            orderItemsHtml = `
                <div class="info-section">
                    <h4><i class="fas fa-shopping-bag"></i> Order Items (Summary)</h4>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ddd;">
                                <th style="text-align: left; padding: 8px;">Product</th>
                                <th style="text-align: center; padding: 8px;">Qty</th>
                                <th style="text-align: right; padding: 8px;">Price</th>
                                <th style="text-align: right; padding: 8px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid #ddd; font-weight: 600;">
                                <td colspan="3" style="text-align: right; padding: 8px;">Order Subtotal:</td>
                                <td style="text-align: right; padding: 8px;">₱${parseFloat(returnData.order_subtotal || returnData.order_total).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
        }

        const modalContent = `
            <div class="return-modal-content">
                <div class="return-header-section">
                    <div class="return-title">
                        <h3>${modalTitle} - Order #${String(returnData.order_id).padStart(6, '0')}</h3>
                        <span class="badge ${statusBadgeClass}">${returnData.status}</span>
                    </div>
                    <p style="color: #666; margin-top: 8px;">Submitted: ${new Date(returnData.created_at).toLocaleString()}</p>
                    ${returnData.status === 'Approved' ? `<p style="color: #2196f3; margin-top: 4px; font-weight: 500;"><i class="fas fa-check-circle"></i> Return approved and ready for pickup</p>` : ''}
                </div>

                <div class="info-section">
                    <h4><i class="fas fa-user"></i> Customer Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name:</label>
                            <span>${escapeHtml(returnData.customer_name)}</span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span>${escapeHtml(returnData.customer_email)}</span>
                        </div>
                        <div class="info-item">
                            <label>Contact:</label>
                            <span>${escapeHtml(returnData.customer_contact)}</span>
                        </div>
                        <div class="info-item">
                            <label>Address:</label>
                            <span>${escapeHtml(returnData.customer_address)}</span>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h4><i class="fas fa-box"></i> Order Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Order Date:</label>
                            <span>${new Date(returnData.order_placed).toLocaleDateString()}</span>
                        </div>
                        <div class="info-item">
                            <label>Order Subtotal:</label>
                            <span>₱${parseFloat(returnData.order_subtotal || returnData.order_total).toFixed(2)}</span>
                        </div>
                        ${returnData.order_discount > 0 ? `
                        <div class="info-item">
                            <label>Coin Discount:</label>
                            <span>-₱${parseFloat(returnData.order_discount).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <label>Final Total:</label>
                            <span><strong>₱${parseFloat(returnData.order_total).toFixed(2)}</strong></span>
                        </div>
                        ` : `
                        <div class="info-item">
                            <label>Order Total:</label>
                            <span><strong>₱${parseFloat(returnData.order_total).toFixed(2)}</strong></span>
                        </div>
                        `}
                        <div class="info-item">
                            <label>Refund Amount:</label>
                            <span>₱${parseFloat(returnData.total_refund_amount || returnData.order_total).toFixed(2)}</span>
                        </div>
                    </div>
                </div>

                ${orderItemsHtml}

                <div class="info-section">
                    <h4><i class="fas fa-comment"></i> Return Reason</h4>
                    <div class="reason-box">
                        ${escapeHtml(returnData.return_reason)}
                    </div>
                </div>

                ${proofImageHtml}

                ${riderInfoHtml}

                ${returnData.status === 'Approved' || returnData.status === 'Picked' || returnData.status === 'In Transit' || returnData.status === 'Returned' ? `
                <div class="info-section">
                    <h4><i class="fas fa-history"></i> Return Timeline</h4>
                    <div class="return-timeline">
                        <div class="timeline-step ${returnData.status !== 'Pending' ? 'completed' : ''}">
                            <div class="timeline-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-title">Return Requested</span>
                                <span class="timeline-date">${new Date(returnData.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                        <div class="timeline-step ${returnData.status === 'Approved' || returnData.status === 'Picked' || returnData.status === 'In Transit' || returnData.status === 'Returned' ? 'completed' : ''}">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-title">Return Approved</span>
                                <span class="timeline-date">${returnData.status === 'Approved' || returnData.status === 'Picked' || returnData.status === 'In Transit' || returnData.status === 'Returned' ? 'Approved' : 'Pending'}</span>
                            </div>
                        </div>
                        <div class="timeline-step ${returnData.return_rider_name ? 'completed' : ''}">
                            <div class="timeline-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-title">Rider Assigned</span>
                                <span class="timeline-date">${returnData.return_rider_name ? returnData.return_rider_name : 'Not assigned'}</span>
                            </div>
                        </div>
                        <div class="timeline-step ${(returnData.return_delivery_status === 'Picked' || returnData.return_delivery_status === 'In Transit' || returnData.return_delivery_status === 'Delivered') ? 'completed' : ''}">
                            <div class="timeline-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-title">Item Picked Up</span>
                                <span class="timeline-date">${returnData.return_delivery_status === 'Picked' || returnData.return_delivery_status === 'In Transit' || returnData.return_delivery_status === 'Delivered' ? 'Picked up' : 'Pending'}</span>
                            </div>
                        </div>
                        <div class="timeline-step ${returnData.status === 'Returned' ? 'completed' : ''}">
                            <div class="timeline-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="timeline-title">Return Completed</span>
                                <span class="timeline-date">${returnData.status === 'Returned' ? 'Completed' : 'Pending'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}

                ${returnData.admin_message ? `
                    <div class="info-section">
                        <h4><i class="fas fa-note-sticky"></i> Admin Message</h4>
                        <div class="message-box" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 4px;">
                            ${escapeHtml(returnData.admin_message)}
                        </div>
                    </div>
                ` : ''}

                <div class="action-buttons" style="display: flex; gap: 10px; margin-top: 20px;">
                    ${actionButtonsHtml}
                </div>
            </div>
        `;

        document.getElementById('returnDetailsContent').innerHTML = modalContent;

        const modalHeader = document.querySelector('#returnDetailsModal .modal-header h2');
        if (modalHeader) {
            modalHeader.innerHTML = `<i class="fas fa-undo"></i> ${modalTitle}`;
        }

        document.getElementById('returnDetailsModal').classList.add('active');

    } catch (error) {
        console.error('Error loading return details:', error);
        alert('Failed to load return details: ' + error.message);
    }
}

function closeReturnDetailsModal() {
    const modal = document.getElementById('returnDetailsModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function openAssignReturnRiderModal(returnId, orderId) {
    document.getElementById('assignReturnId').value = returnId;
    document.getElementById('assignReturnOrderId').value = orderId;

    const riderSelect = document.getElementById('returnRiderSelect');
    riderSelect.innerHTML = '<option value="">-- Loading riders... --</option>';

    try {
        const response = await fetch('api/get_delivery_riders.php', {
            credentials: 'same-origin'
        });
        const riders = await response.json();

        if (riders && riders.length > 0) {
            riderSelect.innerHTML = '<option value="">-- Select a delivery rider --</option>';
            riders.forEach(rider => {
                const option = document.createElement('option');
                option.value = rider.ID;
                option.textContent = `${rider.FullName} (${rider.Contact})`;
                riderSelect.appendChild(option);
            });
        } else {
            riderSelect.innerHTML = '<option value="">-- No riders available --</option>';
        }
    } catch (error) {
        console.error('Error loading riders:', error);
        riderSelect.innerHTML = '<option value="">-- Error loading riders --</option>';
    }

    document.getElementById('assignReturnRiderModal').style.display = 'flex';
}

function closeAssignReturnRiderModal() {
    document.getElementById('assignReturnRiderModal').style.display = 'none';
    document.getElementById('assignReturnRiderForm').reset();
}

async function confirmAssignReturnRider(event) {
    event.preventDefault();

    const returnId = parseInt(document.getElementById('assignReturnId').value);
    const riderId = parseInt(document.getElementById('returnRiderSelect').value);

    if (riderId <= 0) {
        alert('Please select a delivery rider');
        return;
    }

    const formData = new FormData();
    formData.append('return_id', returnId);
    formData.append('action', 'approve');
    formData.append('rider_id', riderId);

    try {
        const response = await fetch('api/approve_return.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            closeAssignReturnRiderModal();
            closeReturnDetailsModal();
            refreshOrders();
        } else {
            alert('Error: ' + (result.message || 'Failed to approve return'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to process request');
    }
}

function openRejectReturnModal(returnId) {
    document.getElementById('rejectReturnId').value = returnId;
    document.getElementById('rejectReturnMessage').value = '';
    document.getElementById('rejectReturnModal').style.display = 'flex';
}

function closeRejectReturnModal() {
    document.getElementById('rejectReturnModal').style.display = 'none';
    document.getElementById('rejectReturnForm').reset();
}

async function confirmRejectReturn(event) {
    event.preventDefault();

    const returnId = parseInt(document.getElementById('rejectReturnId').value);
    const message = document.getElementById('rejectReturnMessage').value.trim();

    if (!message) {
        alert('Please provide a rejection reason');
        return;
    }

    const formData = new FormData();
    formData.append('return_id', returnId);
    formData.append('action', 'reject');
    formData.append('admin_message', message);

    try {
        const response = await fetch('api/approve_return.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            alert('Return rejected successfully!');
            closeRejectReturnModal();
            closeReturnDetailsModal();
            refreshOrders();
        } else {
            alert('Error: ' + (result.message || 'Failed to reject return'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to process request');
    }
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
    if (event.target.id === 'returnDetailsModal') {
        closeReturnDetailsModal();
    }
    if (event.target.id === 'assignReturnRiderModal') {
        closeAssignReturnRiderModal();
    }
    if (event.target.id === 'rejectReturnModal') {
        closeRejectReturnModal();
    }
}

window.addEventListener('click', function (event) {
    if (event.target.id === 'orderDetailsModal') {
        closeOrderDetailsModal();
    }
    if (event.target.id === 'assignDeliveryModal') {
        closeAssignDeliveryModal();
    }
    if (event.target.id === 'returnDetailsModal') {
        closeReturnDetailsModal();
    }
    if (event.target.id === 'assignReturnRiderModal') {
        closeAssignReturnRiderModal();
    }
    if (event.target.id === 'rejectReturnModal') {
        closeRejectReturnModal();
    }
});
