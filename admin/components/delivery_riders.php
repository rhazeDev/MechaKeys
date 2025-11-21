<!-- Delivery Riders Management Section -->
<section id="delivery-riders" class="section">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Delivery Riders Management</h2>
            <div class="panel-actions">
                <button class="btn btn-primary" onclick="openAddRiderModal()">
                    <i class="fas fa-user-plus"></i>
                    Add New Rider
                </button>
                <button class="btn btn-secondary" onclick="refreshRiders()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Riders Statistics -->
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card-small money">
                <div class="stat-icon-small success">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="totalRidersCount">0</div>
                    <div class="stat-label-small">Total Riders</div>
                </div>
            </div>
            <div class="stat-card-small money">
                <div class="stat-icon-small primary">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="activeRidersCount">0</div>
                    <div class="stat-label-small">Active Riders</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small info">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="assignedDeliveriesCount">0</div>
                    <div class="stat-label-small">Active Deliveries</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small warning">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="completedDeliveriesCount">0</div>
                    <div class="stat-label-small">Completed Today</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small success">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="totalCollectedToday">₱0.00</div>
                    <div class="stat-label-small">Collected Today</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small success">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="totalUnremittedAmount">₱0.00</div>
                    <div class="stat-label-small">Total Unremitted</div>
                </div>
            </div>
        </div>

                <!-- Proof of Delivery Modal (top-level) -->
                <div id="proofModal" class="modal">
                    <div class="modal-content" style="width: 90%; max-width: 800px;">
                        <div class="modal-header">
                            <h3><i class="fas fa-image"></i> Proof of Delivery</h3>
                            <button class="modal-close" onclick="closeProofModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body" style="text-align: center;">
                            <img id="proofImage" src="" alt="Proof of Delivery" style="max-width: 100%; max-height: 600px; border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <!-- Rider Statistics Modal (top-level) -->
                <div id="riderStatsModal" class="modal">
                    <div class="modal-content modal-large">
                        <div class="modal-header">
                            <h3><i class="fas fa-user-tie"></i> Delivery Rider Statistics</h3>
                            <button class="modal-close" onclick="closeRiderStatsModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="currentRiderId">
                            <div class="detail-box" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                                <div style="flex:1">
                                    <label>Rider</label>
                                    <div id="riderNamePlaceholder" style="color: #666;">&nbsp;</div>
                                </div>
                                <div style="width:200px;">
                                    <label>Filter</label>
                                    <select id="riderStatsFilter" class="form-select" onchange="loadRiderStats(document.getElementById('currentRiderId').value)">
                                        <option value="all">All</option>
                                        <option value="month">This Month</option>
                                        <option value="today">Today</option>
                                    </select>
                                </div>
                            </div>

                            <div class="stats-grid" style="margin-bottom: 30px;">
                                <div class="stat-card-small">
                                    <div class="stat-icon-small primary">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="stat-value-small" id="statsTotal">0</div>
                                        <div class="stat-label-small">Total Packages</div>
                                    </div>
                                </div>
                                <div class="stat-card-small">
                                    <div class="stat-icon-small success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <div class="stat-value-small" id="statsSuccessful">0</div>
                                        <div class="stat-label-small">Successful</div>
                                    </div>
                                </div>
                                <div class="stat-card-small">
                                    <div class="stat-icon-small info">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div>
                                        <div class="stat-value-small" id="statsInProgress">0</div>
                                        <div class="stat-label-small">In Progress</div>
                                    </div>
                                </div>
                                <div class="stat-card-small">
                                    <div class="stat-icon-small success">
                                        <i class="fas fa-peso-sign"></i>
                                    </div>
                                    <div>
                                        <div class="stat-value-small" id="statsUnremitted">₱0.00</div>
                                        <div class="stat-label-small">Unremitted</div>
                                    </div>
                                </div>
                            </div>

                            <h4 style="margin-bottom: 15px;"><i class="fas fa-list"></i> Recent Deliveries</h4>
                            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Proof</th>
                                        </tr>
                                    </thead>
                                    <tbody id="riderDeliveriesBody">
                                        <tr>
                                            <td colspan="6" style="text-align: center; padding: 20px;">
                                                <i class="fas fa-spinner fa-spin"></i> Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h4 style="margin-top: 25px; margin-bottom: 15px;"><i class="fas fa-money-bill-wave"></i> Remittances</h4>
                            <div class="table-container remittances-admin-section" style="max-height: 300px; overflow-y: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Remittance ID</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="riderRemittancesBody">
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 20px;">
                                                <i class="fas fa-spinner fa-spin"></i> Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeRiderStatsModal()">Close</button>
                                <button type="button" class="btn btn-primary" onclick="openCreateRemittanceModal(document.getElementById('currentRiderId').value)">
                                    <i class="fas fa-plus"></i> Create Remittance
                                </button>
                        </div>
                    </div>
                </div>

        <!-- Riders Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Total Unremitted</th>
                        <th>Status</th>
                        <th>Active Deliveries</th>
                        <th>Actions</th>
                    </tr>
                </thead>
 
                <tbody id="ridersTableBody">
                    <tr>
                        <td colspan="9" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading riders...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Add Rider Modal -->
<div id="addRiderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Add New Delivery Rider</h3>
            <button class="modal-close" onclick="closeAddRiderModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addRiderForm" onsubmit="submitAddRider(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Full Name
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="full_name" class="form-input" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="email" class="form-input" placeholder="rider@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Contact Number
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="contact" class="form-input" placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" maxlength="11" required>
                        <small style="color: #666; font-size: 12px;">Format: 09XXXXXXXXX (11 digits)</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Password
                            <span class="required">*</span>
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Enter password" minlength="8" maxlength="20" required>
                        <small style="color: #666; font-size: 12px;">8-20 characters, must include uppercase, lowercase, number, and special character</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            Address
                            <span class="required">*</span>
                        </label>
                        <textarea name="address" class="form-textarea" placeholder="Enter complete address" rows="3" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddRiderModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Rider Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rider Modal -->
<div id="editRiderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit Delivery Rider</h3>
            <button class="modal-close" onclick="closeEditRiderModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editRiderForm" onsubmit="submitEditRider(event)">
                <input type="hidden" id="editRiderId" name="rider_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            Full Name
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="editRiderFullName" name="full_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Email Address
                            <span class="required">*</span>
                        </label>
                        <input type="email" id="editRiderEmail" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Contact Number
                            <span class="required">*</span>
                        </label>
                        <input type="text" id="editRiderContact" name="contact" class="form-input" pattern="09[0-9]{9}" maxlength="11" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            New Password
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="Leave empty to keep current password" minlength="8" maxlength="20">
                        <small style="color: #666; font-size: 12px;">Leave empty if you don't want to change the password</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            Address
                            <span class="required">*</span>
                        </label>
                        <textarea id="editRiderAddress" name="address" class="form-textarea" rows="3" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditRiderModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Create Remittance Modal -->
        <div id="createRemittanceModal" class="modal">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h3><i class="fas fa-peso-sign"></i> Create Remittance</h3>
                    <button class="modal-close" onclick="closeCreateRemittanceModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createRemittanceForm" onsubmit="submitCreateRemittance(event)">
                        <input type="hidden" id="createRiderId" name="rider_id">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Period</label>
                                <select id="remitPeriod" name="period" class="form-select" onchange="loadRemittanceOrders()">
                                    <option value="today">Today</option>
                                    <option value="month">This Month</option>
                                    <option value="all">All</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div id="customPeriodFields" style="display: none;">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" id="remitStart" name="period_start" class="form-input" onchange="loadRemittanceOrders()">
                                </div>
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" id="remitEnd" name="period_end" class="form-input" onchange="loadRemittanceOrders()">
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label>Orders (Select which orders to include)</label>
                                <div class="table-container" style="max-height: 220px; overflow-y: auto;">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px;"></th>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="createRemittanceOrdersBody">
                                            <tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading orders...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="text" id="createRemittanceAmount" name="amount" class="form-input" readonly>
                            </div>
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" id="createRemittanceReference" name="reference" class="form-input" placeholder="(Optional) reference">
                            </div>
                            <div class="form-group full-width">
                                <label>Notes</label>
                                <textarea id="createRemittanceNotes" name="notes" class="form-textarea" rows="3" placeholder="Notes or audit message (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeCreateRemittanceModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Remittance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
