let currentReturnFilter = 'all';
let allDeliveryRiders = [];

document.addEventListener('DOMContentLoaded', function () {
    loadReturns();
    loadDeliveryRiders();
});

function loadDeliveryRiders() {
    fetch('api/get_delivery_riders.php', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allDeliveryRiders = data.riders;
            }
        })
        .catch(error => console.error('Error loading riders:', error));
}

function loadReturns(filter = 'all') {
    currentReturnFilter = filter;
    const url = filter === 'all'
        ? 'api/get_returns.php'
        : `api/get_returns.php?status=${filter}`;

    fetch(url, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderReturnsTable(data.returns);
            } else {
                document.getElementById('returnsTable').innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        ${data.message || 'Failed to load returns'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('returnsTable').innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    Failed to load returns. Please try again.
                </div>
            `;
        });
}

function renderReturnsTable(returns) {
    if (returns.length === 0) {
        document.getElementById('returnsTable').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No returns found</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Return ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Refund Amount</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;

    returns.forEach(ret => {
        const statusClass = `status-${ret.status.toLowerCase()}`;
        const createdDate = new Date(ret.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        html += `
            <tr>
                <td><strong>#${ret.return_id}</strong></td>
                <td><strong>#${ret.order_id}</strong></td>
                <td>
                    <div class="customer-info">
                        <div class="customer-name">${escapeHtml(ret.customer_name)}</div>
                        <small>${escapeHtml(ret.customer_email)}</small>
                    </div>
                </td>
                <td><span class="status-badge ${statusClass}">${ret.status}</span></td>
                <td>
                    <small class="text-truncate">${escapeHtml(ret.return_reason.substring(0, 30))}...</small>
                </td>
                <td><strong>₱${parseFloat(ret.total_refund_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</strong></td>
                <td>${createdDate}</td>
                <td>
                    <button class="btn-small btn-info" onclick="viewReturnDetails(${ret.return_id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                    ${ret.status === 'Pending' ? `
                        <button class="btn-small btn-success" onclick="openAssignRiderModal(${ret.return_id})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn-small btn-danger" onclick="openRejectModal(${ret.return_id})">
                            <i class="fas fa-ban"></i> Reject
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('returnsTable').innerHTML = html;
}

function filterReturns(filter) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    loadReturns(filter);
}

function viewReturnDetails(returnId) {
    fetch(`api/get_returns.php?return_id=${returnId}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.returns.length > 0) {
                const ret = data.returns[0];
                renderReturnDetailsModal(ret);
                document.getElementById('returnDetailsModal').style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load return details');
        });
}

function renderReturnDetailsModal(ret) {
    const proofImage = ret.proof_image ? `<img src="../${ret.proof_image}" alt="Proof" style="max-width: 300px; border-radius: 8px; margin-top: 1rem;">` : '';
    const riderInfo = ret.return_rider_name ? `<strong>${escapeHtml(ret.return_rider_name)}</strong>` : 'Not assigned';

    let html = `
        <div class="return-details-container">
            <div class="details-section">
                <h3><i class="fas fa-info-circle"></i> Return Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Return ID</label>
                        <value>#${ret.return_id}</value>
                    </div>
                    <div class="info-item">
                        <label>Order ID</label>
                        <value>#${ret.order_id}</value>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <value><span class="status-badge status-${ret.status.toLowerCase()}">${ret.status}</span></value>
                    </div>
                    <div class="info-item">
                        <label>Refund Amount</label>
                        <value><strong>₱${parseFloat(ret.total_refund_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</strong></value>
                    </div>
                </div>
            </div>

            <div class="details-section">
                <h3><i class="fas fa-user"></i> Customer Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Name</label>
                        <value>${escapeHtml(ret.customer_name)}</value>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <value>${escapeHtml(ret.customer_email)}</value>
                    </div>
                    <div class="info-item">
                        <label>Contact</label>
                        <value>${escapeHtml(ret.customer_contact)}</value>
                    </div>
                    <div class="info-item">
                        <label>Address</label>
                        <value>${escapeHtml(ret.customer_address)}</value>
                    </div>
                </div>
            </div>

            <div class="details-section">
                <h3><i class="fas fa-comment"></i> Return Reason</h3>
                <p class="reason-text">${escapeHtml(ret.return_reason)}</p>
            </div>

            <div class="details-section">
                <h3><i class="fas fa-image"></i> Proof Image</h3>
                ${proofImage || '<p>No image provided</p>'}
            </div>

            ${ret.status === 'Approved' ? `
            <div class="details-section">
                <h3><i class="fas fa-truck"></i> Return Pickup</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Assigned Rider</label>
                        <value>${riderInfo}</value>
                    </div>
                    <div class="info-item">
                        <label>Pickup Status</label>
                        <value><span class="status-badge">${ret.return_delivery_status || 'Pending'}</span></value>
                    </div>
                </div>
            </div>
            ` : ''}

            ${ret.admin_message ? `
            <div class="details-section">
                <h3><i class="fas fa-comment-alt"></i> Admin Message</h3>
                <p>${escapeHtml(ret.admin_message)}</p>
            </div>
            ` : ''}
        </div>
    `;

    document.getElementById('returnDetailsContent').innerHTML = html;
}

function closeReturnDetailsModal() {
    document.getElementById('returnDetailsModal').style.display = 'none';
}

function openAssignRiderModal(returnId) {
    document.getElementById('assignReturnId').value = returnId;

    const riderSelect = document.getElementById('returnRiderSelect');
    riderSelect.innerHTML = '<option value="">-- Select a delivery rider --</option>';

    allDeliveryRiders.forEach(rider => {
        const option = document.createElement('option');
        option.value = rider.id;
        option.textContent = `${rider.name} (${rider.contact})`;
        riderSelect.appendChild(option);
    });

    document.getElementById('assignReturnRiderModal').style.display = 'flex';
}

function closeAssignRiderModal() {
    document.getElementById('assignReturnRiderModal').style.display = 'none';
}

function confirmAssignReturnRider() {
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

    fetch('api/approve_return.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAssignRiderModal();
                closeReturnDetailsModal();
                loadReturns(currentReturnFilter);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process request');
        });
}

function openRejectModal(returnId) {
    document.getElementById('rejectReturnId').value = returnId;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectReturnModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectReturnModal').style.display = 'none';
}

function confirmRejectReturn() {
    const returnId = parseInt(document.getElementById('rejectReturnId').value);
    const reason = document.getElementById('rejectReason').value.trim();

    if (!reason) {
        alert('Please provide a rejection reason');
        return;
    }

    const formData = new FormData();
    formData.append('return_id', returnId);
    formData.append('action', 'reject');
    formData.append('admin_message', reason);

    fetch('api/approve_return.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeRejectModal();
                closeReturnDetailsModal();
                loadReturns(currentReturnFilter);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process request');
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

window.onclick = function (event) {
    const returnDetailsModal = document.getElementById('returnDetailsModal');
    const assignRiderModal = document.getElementById('assignReturnRiderModal');
    const rejectModal = document.getElementById('rejectReturnModal');

    if (event.target === returnDetailsModal) {
        closeReturnDetailsModal();
    }
    if (event.target === assignRiderModal) {
        closeAssignRiderModal();
    }
    if (event.target === rejectModal) {
        closeRejectModal();
    }
}
