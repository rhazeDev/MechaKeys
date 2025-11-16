<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}
?>

<section id="returns" class="section">

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
        <div class="returns-actions">
            <button class="btn btn-success btn-refresh" onclick="loadReturns(currentReturnFilter)">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
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
            <button class="modal-close btn-close" onclick="closeReturnDetailsModal()" aria-label="Close">
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

<!-- Approve Return Modal (informational) -->
<div id="assignReturnRiderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-check-circle"></i> Approve Return</h2>
            <button class="modal-close btn-close" onclick="closeAssignRiderModal()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="assignReturnId">
            
            <div class="form-group">
                <p>
                    When you approve a return, the delivery rider who originally delivered the order will be assigned
                    to pick up the return item automatically. No manual rider selection is required.
                </p>
            </div>

            <div class="form-actions">
                <button class="btn btn-success" onclick="confirmAssignReturnRider()">
                    <i class="fas fa-check-circle"></i> Approve Return
                </button>
                <button class="btn btn-secondary btn-cancel" onclick="closeAssignRiderModal()">
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
            <button class="modal-close btn-close" onclick="closeRejectModal()" aria-label="Close">
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
                <button class="btn-danger btn-reject" onclick="confirmRejectReturn()">
                    <i class="fas fa-check-circle"></i> Reject Return
                </button>
                <button class="btn-secondary btn-cancel" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

</section>
