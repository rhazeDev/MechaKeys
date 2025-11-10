<?php
session_start();
require_once '../../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}
?>

<div class="returns-container">
    <div class="returns-header">
        <h2><i class="fas fa-exchange-alt"></i> Returns & Refunds Management</h2>
        <div class="returns-filters">
            <button class="filter-btn active" onclick="filterReturns('all')">All</button>
            <button class="filter-btn" onclick="filterReturns('Pending')">Pending</button>
            <button class="filter-btn" onclick="filterReturns('Approved')">Approved</button>
            <button class="filter-btn" onclick="filterReturns('Rejected')">Rejected</button>
            <button class="filter-btn" onclick="filterReturns('Completed')">Completed</button>
        </div>
    </div>

    <div class="returns-content">
        <div id="returnsTable" class="returns-table">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading returns...
            </div>
        </div>
    </div>
</div>

<!-- Return Details Modal -->
<div id="returnDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-undo"></i> Return Details</h2>
            <button class="modal-close" onclick="closeReturnDetailsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="returnDetailsContent">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<!-- Assign Rider Modal for Return -->
<div id="assignReturnRiderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-shield"></i> Assign Delivery Rider for Return</h2>
            <button class="modal-close" onclick="closeAssignRiderModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="assignReturnId">
            
            <div class="form-group">
                <label for="returnRiderSelect"><i class="fas fa-motorcycle"></i> Select Rider</label>
                <select id="returnRiderSelect" class="form-control">
                    <option value="">-- Select a delivery rider --</option>
                </select>
            </div>

            <div class="form-actions">
                <button class="btn-primary" onclick="confirmAssignReturnRider()">
                    <i class="fas fa-check-circle"></i> Assign Rider
                </button>
                <button class="btn-secondary" onclick="closeAssignRiderModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Return Modal -->
<div id="rejectReturnModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-ban"></i> Reject Return Request</h2>
            <button class="modal-close" onclick="closeRejectModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="rejectReturnId">
            
            <div class="form-group">
                <label for="rejectReason"><i class="fas fa-comment"></i> Reason for Rejection</label>
                <textarea id="rejectReason" class="form-control" placeholder="Provide a detailed reason for rejecting this return..." rows="4" required></textarea>
            </div>

            <div class="form-actions">
                <button class="btn-danger" onclick="confirmRejectReturn()">
                    <i class="fas fa-check-circle"></i> Reject Return
                </button>
                <button class="btn-secondary" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>
