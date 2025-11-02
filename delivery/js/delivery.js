let currentOrderId = null;
let currentTrackingId = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDeliveries();
    loadProfileInfo();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('refreshBtn').addEventListener('click', () => {
        document.getElementById('refreshBtn').classList.add('spinning');
        loadDeliveries();
        setTimeout(() => {
            document.getElementById('refreshBtn').classList.remove('spinning');
        }, 500);
    });

    document.getElementById('profileBtn').addEventListener('click', openProfileModal);

    document.getElementById('statusFilter').addEventListener('change', () => {
        loadDeliveries();
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
}

async function loadDeliveries() {
    try {
        const statusFilter = document.getElementById('statusFilter').value;
        const url = statusFilter
            ? `api/get_deliveries.php?status=${statusFilter}`
            : 'api/get_deliveries.php';

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            document.getElementById('pendingCount').textContent = result.stats.Assigned || 0;
            document.getElementById('pickedCount').textContent = result.stats.Picked || 0;
            document.getElementById('transitCount').textContent = result.stats['In Transit'] || 0;
            document.getElementById('deliveredCount').textContent = result.stats.Delivered || 0;

            renderDeliveries(result.deliveries);
        } else {
            showError('Failed to load deliveries');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while loading deliveries');
    }
}

function renderDeliveries(deliveries) {
    const container = document.getElementById('deliveriesList');

    if (deliveries.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No deliveries found</p></div>';
        return;
    }

    container.innerHTML = deliveries.map(delivery => {
        const statusClass = getStatusClass(delivery.DeliveryStatus);
        const statusIcon = getStatusIcon(delivery.DeliveryStatus);

        return `
            <div class="delivery-card" onclick="openOrderModal(${delivery.OrderID})">
                <div class="delivery-header">
                    <div class="delivery-order-info">
                        <h3 class="order-number">#${String(delivery.OrderID).padStart(6, '0')}</h3>
                        <p class="customer-name">${delivery.CustomerName}</p>
                    </div>
                    <span class="status-badge ${statusClass}">
                        <i class="${statusIcon}"></i>
                        ${delivery.DeliveryStatus}
                    </span>
                </div>

                <div class="delivery-body">
                    <div class="info-row">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${delivery.CustomerAddress}</span>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-phone"></i>
                        <span>${delivery.CustomerPhone}</span>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-box"></i>
                        <span>${delivery.ItemCount} item(s) - ₱${parseFloat(delivery.TotalAmount).toFixed(2)}</span>
                    </div>
                </div>

                <div class="delivery-footer">
                    <button class="btn btn-primary btn-sm" onclick="openStatusModal(event, ${delivery.OrderID}, ${delivery.TrackingID}, '${delivery.DeliveryStatus}')">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function getStatusClass(status) {
    const statusMap = {
        'Assigned': 'assigned',
        'Picked': 'picked',
        'In Transit': 'in-transit',
        'Delivered': 'delivered'
    };
    return statusMap[status] || 'default';
}

function getStatusIcon(status) {
    const iconMap = {
        'Assigned': 'fas fa-clipboard-check',
        'Picked': 'fas fa-box',
        'In Transit': 'fas fa-truck',
        'Delivered': 'fas fa-check-circle'
    };
    return iconMap[status] || 'fas fa-info-circle';
}

async function openOrderModal(orderId) {
    const modal = document.getElementById('orderModal');
    const detailsDiv = document.getElementById('orderDetails');

    detailsDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.classList.add('active');

    try {
        const response = await fetch(`api/get_order_details.php?order_id=${orderId}`);
        const result = await response.json();

        if (result.success) {
            const order = result.order;
            const items = result.items;
            const tracking = result.tracking;

            const itemsHtml = items.map(item => `
                <div class="order-item">
                    <div class="item-image">
                        ${item.ImagePath ?
                    `<img src="/${item.ImagePath}" alt="${item.Brand}">` :
                    '<div class="placeholder"><i class="fas fa-keyboard"></i></div>'
                }
                    </div>
                    <div class="item-info">
                        <h4>${item.Brand} ${item.Model}</h4>
                        <p class="item-specs">${item.Layout}% • ${item.SwitchType} • ${item.Color}</p>
                        <p class="item-qty">Qty: ${item.Quantity}</p>
                    </div>
                    <div class="item-price">₱${parseFloat(item.SubTotal).toFixed(2)}</div>
                </div>
            `).join('');

            detailsDiv.innerHTML = `
                <div class="order-detail-container">
                    <div class="detail-section">
                        <h3>Customer Information</h3>
                        <div class="detail-row">
                            <span class="label">Name:</span>
                            <span class="value">${order.FullName}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Email:</span>
                            <span class="value">${order.Email}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Phone:</span>
                            <span class="value">${order.Contact}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Address:</span>
                            <span class="value">${order.Address}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Delivery Items</h3>
                        <div class="items-list">
                            ${itemsHtml}
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Order Summary</h3>
                        <div class="detail-row">
                            <span class="label">Order Date:</span>
                            <span class="value">${new Date(order.PlaceOrdered).toLocaleDateString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Total Amount:</span>
                            <span class="value total">₱${parseFloat(order.TotalAmount).toFixed(2)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Current Status:</span>
                            <span class="value"><span class="badge ${getStatusClass(tracking.DeliveryStatus)}">${tracking.DeliveryStatus === 'Ready to Deliver' ? 'Assigned' : tracking.DeliveryStatus}</span></span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            detailsDiv.innerHTML = '<div class="error-message">Failed to load order details</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        detailsDiv.innerHTML = '<div class="error-message">An error occurred</div>';
    }
}

function openStatusModal(event, orderId, trackingId, currentStatus) {
    event.stopPropagation();
    currentOrderId = orderId;
    currentTrackingId = trackingId;

    const modal = document.getElementById('statusModal');
    modal.classList.add('active');

    const options = document.querySelectorAll('.status-option');
    options.forEach(option => {
        const optionStatus = option.dataset.status;
        option.classList.remove('disabled');

        if (currentStatus === 'Delivered') {
            option.classList.add('disabled');
        } else if (currentStatus === 'In Transit' && optionStatus === 'Picked') {
            option.classList.add('disabled');
        } else if (currentStatus === 'Picked' && (optionStatus === 'Picked' || optionStatus === 'In Transit')) {
            option.classList.add('disabled');
        }
    });
}

async function updateDeliveryStatus(newStatus) {
    if (!currentOrderId || !currentTrackingId) {
        showError('Invalid order or tracking information');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('order_id', currentOrderId);
        formData.append('tracking_id', currentTrackingId);
        formData.append('status', newStatus);

        const response = await fetch('api/update_delivery_status.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeModal('statusModal');
            closeModal('orderModal');
            showSuccess('Delivery status updated successfully');
            loadDeliveries();
        } else {
            showError(result.message || 'Failed to update delivery status');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while updating the status');
    }
}

function loadProfileInfo() {
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profilePhone = document.getElementById('profilePhone');
    const profileAddress = document.getElementById('profileAddress');

    if (profileName) {
        profileName.textContent = 'Delivery Rider';
        profileEmail.textContent = 'Email: rider@mechakeys.com';
        profilePhone.textContent = 'Phone: +63 XXX XXX XXXX';
        profileAddress.textContent = 'Address: City, Country';
    }
}

function openProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.add('active');
}

function logoutDelivery() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../logout.php';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        if (modalId === 'statusModal') {
            currentOrderId = null;
            currentTrackingId = null;
        }
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .btn-icon.spinning {
        animation: spin 0.5s linear;
    }
`;
document.head.appendChild(style);
