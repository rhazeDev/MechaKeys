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
        const response = await fetch('api/get_riders.php');
        const result = await response.json();

        if (result.success) {
            document.getElementById('totalRidersCount').textContent = result.stats.total || 0;
            document.getElementById('activeRidersCount').textContent = result.stats.active || 0;
            document.getElementById('assignedDeliveriesCount').textContent = result.stats.activeDeliveries || 0;
            document.getElementById('completedDeliveriesCount').textContent = result.stats.completedToday || 0;

            if (result.riders && result.riders.length > 0) {
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
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No delivery riders found</td></tr>';
            }
        } else {
            showError(result.message || 'Failed to load riders', 'Load Failed');
        }
    } catch (error) {
        console.error('Failed to load riders:', error);
        document.getElementById('ridersTableBody').innerHTML =
            '<tr><td colspan="8" class="text-center" style="color: red;">Failed to load delivery riders</td></tr>';
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
            body: formData
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
        const response = await fetch('api/get_riders.php');
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
            body: formData
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
                    body: formData
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
    console.log('[RiderStats] openRiderStats called for riderId=', riderId);
    try {
        if (modal) {
            modal.style.display = 'flex';
            console.log('[RiderStats] modal shown via inline style; computed display=', window.getComputedStyle(modal).display);
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
        console.debug('[RiderStats] unable to set rider name in modal', e);
    }

    loadRiderStats(riderId);
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
        console.log('[RiderStats] fetching', url);
        const response = await fetch(url);

        const raw = await response.text();
        let result;
        try {
            result = JSON.parse(raw);
        } catch (parseError) {
            console.error('Failed to parse JSON from get_rider_stats.php:', parseError);
            console.debug('Raw response:', raw);
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

        console.log('[RiderStats] API result', result);
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
                        <td>₱${parseFloat(d.TotalAmount).toFixed(2)}</td>
                        <td><span class="badge ${getStatusClass(d.DeliveryStatus)}">${d.DeliveryStatus}</span></td>
                        <td>${new Date(d.LastUpdated).toLocaleDateString()}</td>
                        <td>
                            ${d.DeliveryProof ? `<a href="../${d.DeliveryProof}" target="_blank" class="btn-icon btn-view" title="View Proof"><i class="fas fa-image"></i></a>` : 'N/A'}
                        </td>
                    </tr>
                `).join('');
        }
    } catch (error) {
        console.error('Error:', error);
        deliveriesBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #f44336;">Failed to load rider statistics</td></tr>';
    }
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
            console.log('[RiderStats] stats button clicked, riderId=', id);
            if (id) {
                openRiderStats(parseInt(id, 10));
            }
            return;
        }

        const tr = e.target.closest('tr[data-rider-id]');
        if (tr && !e.target.closest('.action-buttons')) {
            const id = tr.getAttribute('data-rider-id');
            console.log('[RiderStats] rider row clicked, riderId=', id);
            if (id) openRiderStats(parseInt(id, 10));
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachRiderStatsHandler);
} else {
    attachRiderStatsHandler();
}
