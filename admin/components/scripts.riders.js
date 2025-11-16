async function loadDeliveryRiders(attempt = 0) {
    const tbody = document.getElementById('ridersTableBody');
    if (!tbody) {
        if (attempt < 10) {
            console.warn(`[loadDeliveryRiders] #ridersTableBody not found, retrying (${attempt + 1})`);
            setTimeout(() => loadDeliveryRiders(attempt + 1), 100);
        } else {
            console.error('[loadDeliveryRiders] #ridersTableBody not found after retries');
        }
        return;
    }

    try {
        const response = await fetch('api/get_riders.php', {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
            document.getElementById('totalRidersCount').textContent = result.stats.total || 0;
            document.getElementById('activeRidersCount').textContent = result.stats.active || 0;
            document.getElementById('assignedDeliveriesCount').textContent = result.stats.activeDeliveries || 0;
            document.getElementById('completedDeliveriesCount').textContent = result.stats.completedToday || 0;

            if (result.riders && result.riders.length > 0) {
                const formatMoney = (value) => {
                    const num = parseFloat(value) || 0;
                    return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                };

                tbody.innerHTML = result.riders.map(rider => {
                    const statusBadge = rider.ActiveDeliveries > 0
                        ? '<span class="badge success">Active</span>'
                        : '<span class="badge default">Inactive</span>';

                    return `
                <tr data-rider-id="${rider.ID}" data-rider-name="${rider.FullName}" style="cursor: pointer;" onclick="openRiderStats(${rider.ID})">
                            <td>#${String(rider.ID).padStart(4, '0')}</td>
                            <td><strong>${rider.FullName}</strong></td>
                            <td>${rider.Email}</td>
                            <td>${rider.Contact}</td>
                            <td>${rider.Address}</td>
                            <td>${formatMoney(rider.TotalUnremitted)}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <span class="badge ${rider.ActiveDeliveries > 0 ? 'warning' : 'default'}">
                                    ${rider.ActiveDeliveries} ${rider.ActiveDeliveries === 1 ? 'delivery' : 'deliveries'}
                                </span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="action-buttons">
                                    <button class="btn-icon btn-stats" onclick="openRiderStats(${rider.ID})" title="View Stats">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button class="btn-icon btn-edit" onclick="openEditRiderModal(${rider.ID})" title="Edit Rider">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <!-- <button class="btn-icon btn-delete" onclick="deleteRider(${rider.ID}, '${rider.FullName}')" title="Delete Rider">
                                        <i class="fas fa-trash"></i>
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">No delivery riders found</td></tr>';
            }
            if (typeof result.stats.totalUnremitted !== 'undefined') {
                const remEl = document.getElementById('totalUnremittedAmount');
                if (remEl) remEl.textContent = '₱' + parseFloat(result.stats.totalUnremitted || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if (typeof result.stats.totalCollectedToday !== 'undefined') {
                const colEl = document.getElementById('totalCollectedToday');
                if (colEl) colEl.textContent = '₱' + parseFloat(result.stats.totalCollectedToday || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        } else {
            showError(result.message || 'Failed to load riders', 'Load Failed');
            tbody.innerHTML = '<tr><td colspan="9" class="text-center" style="color: red;">Failed to load riders</td></tr>';
        }
    } catch (error) {
        console.error('Failed to load riders:', error);
        document.getElementById('ridersTableBody').innerHTML =
            '<tr><td colspan="9" class="text-center" style="color: red;">Failed to load delivery riders</td></tr>';
    }
}

function refreshRiders() {
    loadDeliveryRiders();
}

function openAddRiderModal() {
    const modal = document.getElementById('addRiderModal');
    modal.style.display = 'flex';
    document.getElementById('addRiderForm').reset();
}

function closeAddRiderModal() {
    const modal = document.getElementById('addRiderModal');
    modal.style.display = 'none';
    document.getElementById('addRiderForm').reset();
}

async function submitAddRider(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('api/add_rider.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            closeAddRiderModal();
            showToast(result.message, 'success', 'Rider Created');
            loadDeliveryRiders();
        } else {
            showError(result.message || 'Failed to create rider account', 'Creation Failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while creating the rider account', 'Error');
    }
}

async function openEditRiderModal(riderId) {
    const modal = document.getElementById('editRiderModal');

    try {
        const response = await fetch('api/get_riders.php', {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
            const rider = result.riders.find(r => r.ID == riderId);

            if (rider) {
                document.getElementById('editRiderId').value = rider.ID;
                document.getElementById('editRiderFullName').value = rider.FullName;
                document.getElementById('editRiderEmail').value = rider.Email;
                document.getElementById('editRiderContact').value = rider.Contact;
                document.getElementById('editRiderAddress').value = rider.Address;

                modal.style.display = 'flex';
            } else {
                showError('Rider not found', 'Error');
            }
        } else {
            showError('Failed to load riders', 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Failed to load rider data', 'Error');
    }
}

function closeEditRiderModal() {
    const modal = document.getElementById('editRiderModal');
    modal.style.display = 'none';
    document.getElementById('editRiderForm').reset();
}

async function submitEditRider(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('api/update_rider.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            closeEditRiderModal();
            showToast(result.message, 'success', 'Rider Updated');
            loadDeliveryRiders();
        } else {
            showError(result.message || 'Failed to update rider', 'Update Failed');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while updating the rider', 'Error');
    }
}

async function deleteRider(riderId, riderName) {
    showCustomConfirm(
        `Are you sure you want to delete <strong>${riderName}</strong>?<br><small style="color: #f44336;">This action cannot be undone.</small>`,
        async () => {
            try {
                const formData = new FormData();
                formData.append('rider_id', riderId);

                const response = await fetch('api/delete_rider.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success', 'Rider Deleted');
                    loadDeliveryRiders();
                } else {
                    showError(result.message || 'Failed to delete rider', 'Delete Failed');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while deleting the rider', 'Error');
            }
        }
    );
}

function openProofModal(imagePath) {
    const modal = document.getElementById('proofModal');
    const proofImage = document.getElementById('proofImage');
    proofImage.src = imagePath;
    if (modal) modal.style.display = 'flex';
}

function closeProofModal() {
    const modal = document.getElementById('proofModal');
    if (modal) modal.style.display = 'none';
}

let currentRiderId = null;

function openRiderStats(riderId) {
    currentRiderId = riderId;
    document.getElementById('currentRiderId').value = riderId;
    const modal = document.getElementById('riderStatsModal');
    try {
        if (modal) {
            modal.style.display = 'flex';
        } else {
            console.warn('[RiderStats] modal element not found');
        }
    } catch (e) {
        console.error('[RiderStats] error showing modal', e);
    }

    try {
        const tr = document.querySelector(`tr[data-rider-id="${riderId}"]`);
        const namePlaceholder = document.getElementById('riderNamePlaceholder');
        if (tr && namePlaceholder) {
            const rname = tr.getAttribute('data-rider-name') || (tr.querySelector('td:nth-child(2)') && tr.querySelector('td:nth-child(2)').innerText) || '';
            namePlaceholder.textContent = rname;
        }
    } catch (e) {
    }

    loadRiderStats(riderId);
    if (typeof changeRemittanceStatus === 'function') {
    }
}

function closeRiderStatsModal() {
    const modal = document.getElementById('riderStatsModal');
    if (modal) modal.style.display = 'none';
}

async function loadRiderStats(riderId) {
    const filter = document.getElementById('riderStatsFilter').value || 'all';
    const deliveriesBody = document.getElementById('riderDeliveriesBody');

    deliveriesBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

    try {
        const url = `api/get_rider_stats.php?rider_id=${riderId}&filter=${filter}`;
        const response = await fetch(url, {
            credentials: 'same-origin'
        });

        const raw = await response.text();
        let result;
        try {
            result = JSON.parse(raw);
        } catch (parseError) {
            console.error('Failed to parse JSON from get_rider_stats.php:', parseError);
            deliveriesBody.innerHTML = `<tr><td colspan="6" style="text-align: left; color: #f44336; white-space: pre-wrap;">Unexpected response from server:\n${escapeHtml(raw)}</td></tr>`;
            return;
        }

        if (!response.ok || !result.success) {
            const msg = result && result.message ? result.message : 'Failed to load rider stats';
            console.warn('[RiderStats] get_rider_stats returned error:', msg, 'raw:', raw);
            deliveriesBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #f44336;">' + msg + '</td></tr>';
            return;
        }

        const stats = result.stats;
        const deliveries = result.deliveries;

        document.getElementById('statsTotal').textContent = stats.total;
        document.getElementById('statsSuccessful').textContent = stats.successful;
        document.getElementById('statsInProgress').textContent = stats.in_progress;

        if (!deliveries || deliveries.length === 0) {
            deliveriesBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No deliveries found</td></tr>';
        } else {
            deliveriesBody.innerHTML = deliveries.map(d => `
                    <tr>
                        <td>#${String(d.OrderID).padStart(6, '0')}</td>
                        <td>${d.CustomerName}</td>
                    <td>₱${parseFloat(d.TotalAmount - (d.Discount || 0)).toFixed(2)}</td>
                        <td><span class="badge ${getStatusClass(d.DeliveryStatus)}">${d.DeliveryStatus}</span></td>
                        <td>${new Date(d.LastUpdated).toLocaleDateString()}</td>
                        <td>
                            ${d.DeliveryProof ? `<a href="../${d.DeliveryProof}" target="_blank" class="btn-icon btn-view" title="View Proof"><i class="fas fa-image"></i></a>` : 'N/A'}
                        </td>
                    </tr>
                `).join('');
        }
        const remTableBody = document.getElementById('riderRemittancesBody');
        if (!remTableBody) {
        } else {
            const remittances = result.remittances || [];
            if (remittances.length === 0) {
                remTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No remittances found</td></tr>';
            } else {
                remTableBody.innerHTML = remittances.map(r => `
                        <tr>
                            <td>#${r.RemittanceID}</td>
                            <td>₱${parseFloat(r.Amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                            <td>${r.PeriodStart ? r.PeriodStart + (r.PeriodEnd ? ' - ' + r.PeriodEnd : '') : '-'}</td>
                            <td>${new Date(r.TransactionDate).toLocaleDateString()}</td>
                            <td>${r.PaymentMethod || 'Cash'}</td>
                            <td>${r.Reference || '-'}</td>
                            <td>${r.Status ? `<span class="badge ${r.Status === 'Paid' ? 'success' : r.Status === 'Pending' ? 'warning' : 'error'}">${r.Status}</span>` : '-'}</td>
                            <td onclick="event.stopPropagation()">
                                ${r.Status === 'Pending' ? `
                                    <button class="btn-small btn-success" onclick="changeRemittanceStatus(${r.RemittanceID}, 'Paid')">Mark Paid</button>
                                    <button class="btn-small btn-danger" onclick="changeRemittanceStatus(${r.RemittanceID}, 'Cancelled')">Cancel</button>
                                ` : r.Status === 'Paid' ? `
                                    <button class="btn-small btn-secondary" onclick="changeRemittanceStatus(${r.RemittanceID}, 'Pending')">Reopen</button>
                                ` : `<button class="btn-small btn-secondary" onclick="changeRemittanceStatus(${r.RemittanceID}, 'Pending')">Reopen</button>`}
                            </td>
                        </tr>
                    `).join('');
            }
        }

        if (document.getElementById('statsUnremitted')) {
            const remTotal = parseFloat(result.stats.unremitted_total || 0);
            document.getElementById('statsUnremitted').textContent = '₱' + remTotal.toLocaleString('en-PH', { minimumFractionDigits: 2 });
        }
    } catch (error) {
        console.error('Error:', error);
        deliveriesBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #f44336;">Failed to load rider statistics</td></tr>';
    }
}

function changeRemittanceStatus(remittanceId, newStatus) {
    showCustomConfirm(`Are you sure you want to set the remittance #${remittanceId} to <strong>${newStatus}</strong>?`, async () => {
        try {
            const formData = new FormData();
            formData.append('remittance_id', remittanceId);
            formData.append('status', newStatus);

            const response = await fetch('api/update_remittance_status.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const result = await response.json();
            if (result.success) {
                showToast('Remittance updated', 'success', 'Updated');
                const riderId = document.getElementById('currentRiderId').value;
                if (riderId) loadRiderStats(riderId);
                loadDeliveryRiders();
            } else {
                showError(result.message || 'Failed to update remittance', 'Error');
            }
        } catch (err) {
            console.error('Error updating remittance status:', err);
            showError('An error occurred while updating remittance status', 'Error');
        }
    });
}
function attachRiderStatsHandler() {
    const tbody = document.getElementById('ridersTableBody');
    if (!tbody) {
        console.warn('[RiderStats] attachRiderStatsHandler: #ridersTableBody not found');
        return;
    }

    tbody.addEventListener('click', function (e) {
        const statsBtn = e.target.closest('.btn-stats');
        if (statsBtn) {
            e.stopPropagation();
            const tr = statsBtn.closest('tr');
            if (!tr) return;
            const id = tr.getAttribute('data-rider-id');
            if (id) {
                openRiderStats(parseInt(id, 10));
            }
            return;
        }

        const tr = e.target.closest('tr[data-rider-id]');
        if (tr && !e.target.closest('.action-buttons')) {
            const id = tr.getAttribute('data-rider-id');
            if (id) openRiderStats(parseInt(id, 10));
        }
    });
}
function openCreateRemittanceModal(riderId) {
    const modal = document.getElementById('createRemittanceModal');
    const input = document.getElementById('createRiderId');
    input.value = riderId;
    const select = document.getElementById('remitPeriod');
    if (select) select.value = 'today';
    document.getElementById('createRemittanceForm').reset();
    document.getElementById('customPeriodFields').style.display = 'none';
    if (modal) modal.style.display = 'flex';
    loadRemittanceOrders();
}

function closeCreateRemittanceModal() {
    const modal = document.getElementById('createRemittanceModal');
    const body = document.getElementById('createRemittanceOrdersBody');
    if (modal) modal.style.display = 'none';
    if (body) body.innerHTML = '<tr><td colspan="5" class="text-center">No orders loaded</td></tr>';
    document.getElementById('createRemittanceAmount').value = '';
}

async function loadRemittanceOrders() {
    const riderId = document.getElementById('createRiderId').value;
    const filter = document.getElementById('remitPeriod').value;
    const startEl = document.getElementById('remitStart');
    const endEl = document.getElementById('remitEnd');
    const body = document.getElementById('createRemittanceOrdersBody');
    const amountInput = document.getElementById('createRemittanceAmount');

    if (!riderId) return;
    body.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading orders...</td></tr>';
    const customFields = document.getElementById('customPeriodFields');
    if (filter === 'custom') {
        customFields.style.display = 'flex';
    } else {
        customFields.style.display = 'none';
    }

    let url = `api/get_rider_candidate_orders.php?rider_id=${riderId}&filter=${encodeURIComponent(filter)}`;
    if (filter === 'custom' && startEl && endEl && startEl.value && endEl.value) {
        url += `&period_start=${encodeURIComponent(startEl.value)}&period_end=${encodeURIComponent(endEl.value)}`;
    }

    try {
        const response = await fetch(url, { credentials: 'same-origin' });
        const json = await response.json();
        if (!json.success) {
            body.innerHTML = `<tr><td colspan="5" class="text-center" style="color:red;">${json.message || 'Failed to load orders'}</td></tr>`;
            amountInput.value = '';
            return;
        }

        const orders = json.orders || [];
        if (orders.length === 0) {
            body.innerHTML = '<tr><td colspan="5" style="text-align:center;">No eligible delivered orders found for this period</td></tr>';
            amountInput.value = '';
            return;
        }

        body.innerHTML = orders.map(o => `
            <tr>
                <td><input class="remitOrderCheckbox" type="checkbox" data-orderid="${o.OrderID}" data-amount="${(o.TotalAmount - (o.Discount || 0))}" checked onchange="toggleRemittanceOrder(this)"></td>
                <td>#${String(o.OrderID).padStart(6, '0')}</td>
                <td>${escapeHtml(o.CustomerName || '')}</td>
                <td>₱${parseFloat((o.TotalAmount - (o.Discount || 0))).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td>${new Date(o.LastUpdated).toLocaleDateString()}</td>
            </tr>
        `).join('');

        const total = orders.reduce((acc, cur) => acc + (parseFloat(cur.TotalAmount || 0) - (parseFloat(cur.Discount || 0) || 0)), 0);
        amountInput.value = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    } catch (err) {
        console.error('Error loading candidate orders:', err);
        body.innerHTML = '<tr><td colspan="5" style="text-align:center; color: red;">Failed to load candidate orders</td></tr>';
        amountInput.value = '';
    }
}

function toggleRemittanceOrder(checkbox) {
    const amountInput = document.getElementById('createRemittanceAmount');
    if (!amountInput) return;
    const checkboxes = document.querySelectorAll('.remitOrderCheckbox');
    let total = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            total += parseFloat(cb.getAttribute('data-amount') || 0);
        }
    });
    amountInput.value = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

async function submitCreateRemittance(event) {
    event.preventDefault();
    const riderId = document.getElementById('createRiderId').value;
    if (!riderId) return showError('Rider not selected', 'Validation');
    const amountInput = document.getElementById('createRemittanceAmount');
    const notes = document.getElementById('createRemittanceNotes').value;
    const reference = document.getElementById('createRemittanceReference').value;
    const filter = document.getElementById('remitPeriod').value;
    const start = document.getElementById('remitStart').value; const end = document.getElementById('remitEnd').value;

    const selected = Array.from(document.querySelectorAll('.remitOrderCheckbox')).filter(cb => cb.checked).map(cb => cb.getAttribute('data-orderid'));
    if (selected.length === 0) return showError('No orders selected', 'Validation');

    const amountStr = amountInput.value.replace(/[₱, ]/g, '');
    const amount = parseFloat(amountStr) || 0;
    if (amount <= 0) return showError('Invalid amount', 'Validation');

    const fd = new FormData();
    fd.append('rider_id', riderId);
    fd.append('amount', amount);
    if (filter === 'custom' && start && end) {
        fd.append('period_start', start);
        fd.append('period_end', end);
    } else if (filter === 'today') {
        fd.append('period_start', '');
        fd.append('period_end', '');
    } else if (filter === 'month') {
        fd.append('period_start', '');
        fd.append('period_end', '');
    }
    fd.append('reference', reference);
    fd.append('notes', notes);
    selected.forEach(id => fd.append('order_ids[]', id));

    try {
        const response = await fetch('api/add_remittance.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        });
        const result = await response.json();
        if (result.success) {
            showToast(result.message || 'Remittance created', 'success', 'Created');
            closeCreateRemittanceModal();
            if (document.getElementById('currentRiderId').value) loadRiderStats(document.getElementById('currentRiderId').value);
            loadDeliveryRiders();
        } else {
            showError(result.message || 'Failed to create remittance', 'Error');
        }
    } catch (err) {
        console.error('Error creating remittance:', err);
        showError('An error occurred while creating the remittance', 'Error');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachRiderStatsHandler);
} else {
    attachRiderStatsHandler();
}
