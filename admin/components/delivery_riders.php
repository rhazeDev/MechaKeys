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
            <div class="stat-card-small">
                <div class="stat-icon-small success">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="totalRidersCount">0</div>
                    <div class="stat-label-small">Total Riders</div>
                </div>
            </div>
            <div class="stat-card-small">
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
                        <th>Status</th>
                        <th>Active Deliveries</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ridersTableBody">
                    <tr>
                        <td colspan="8" class="text-center">
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
