<!-- Custom Confirm Dialog -->
<?php include __DIR__ . '/confirmDialog.html'; ?>
<!-- Orders Management Section -->
<section id="orders" class="section">
    <!-- Filter and Search Bar -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Order Management</h2>
            <div class="panel-actions">
                <select id="orderStatusFilter" class="filter-select" onchange="filterOrders()">
                    <option value="all">All Orders</option>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Assigned">Assigned</option>
                    <option value="In Transit">In Transit</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
                <button class="btn btn-secondary" onclick="refreshOrders()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Orders Statistics -->
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card-small">
                <div class="stat-icon-small primary">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="pendingOrdersCount">0</div>
                    <div class="stat-label-small">Pending Orders</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small warning">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="assignedOrdersCount">0</div>
                    <div class="stat-label-small">Assigned</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small info">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="shippedOrdersCount">0</div>
                    <div class="stat-label-small">In Transit</div>
                </div>
            </div>
            <div class="stat-card-small">
                <div class="stat-icon-small success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value-small" id="deliveredOrdersCount">0</div>
                    <div class="stat-label-small">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Return Status</th>
                        <th>Delivery Rider</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading orders...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- View Order Details Modal -->
<div id="orderDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Order Details</h3>
            <button class="modal-close" onclick="closeOrderDetailsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="orderDetailsBody" class="modal-body">
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
        <div id="orderDetailsFooter" class="modal-footer" style="display: none;">
            <!-- Approve/Cancel buttons will be inserted here -->
        </div>
    </div>
</div>

<!-- Assign Delivery Rider Modal -->
<div id="assignDeliveryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Assign Delivery Rider</h3>
            <button class="modal-close" onclick="closeAssignDeliveryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="assignDeliveryForm" onsubmit="submitAssignDelivery(event)">
                <input type="hidden" id="assignOrderId" name="order_id">
                <input type="hidden" id="assignTrackingId" name="tracking_id">
                
                <div class="form-group">
                    <label for="deliveryRider">Select Delivery Rider</label>
                    <select id="deliveryRider" name="delivery_rider_id" class="form-control" required>
                        <option value="">-- Select Rider --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="courierName">Internal Courier/Rider Name <span style="color: red;">*</span></label>
                    <input type="text" id="courierName" name="courier_name" class="form-control" placeholder="e.g., MechaKeys Local Delivery" required>
                    <small style="color: #666; font-size: 12px;">Name of your delivery service (since it's local)</small>
                </div>

                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Any special instructions..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAssignDeliveryModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Assign Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ready to Deliver Modal with Delivery Person Selection -->
<div id="readyToDeliverModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-truck"></i> Mark as Ready to Deliver</h3>
            <button class="modal-close" onclick="closeReadyToDeliverModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="readyToDeliverForm" onsubmit="submitReadyToDeliver(event)">
                <input type="hidden" id="readyOrderId" name="order_id">
                <input type="hidden" id="readyTrackingId" name="tracking_id">
                
                <div class="form-group">
                    <label class="form-label">
                        Assign to Delivery Rider 
                        <span class="required">*</span>
                    </label>
                    <select id="readyDeliveryRider" name="delivery_rider_id" class="form-select" required>
                        <option value="">-- Select Rider --</option>
                    </select>
                    <small style="color: #666; font-size: 13px; margin-top: 4px;">Choose the delivery person who will handle this order</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeReadyToDeliverModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Details Modal from Orders -->
<div id="returnDetailsModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><i class="fas fa-undo"></i> Return Request Details</h2>
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

<!-- Assign Return Rider Modal -->
<div id="assignReturnRiderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-shield"></i> Assign Delivery Rider for Return</h2>
            <button class="modal-close" onclick="closeAssignReturnRiderModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="assignReturnRiderForm" onsubmit="confirmAssignReturnRider(event)">
                <input type="hidden" id="assignReturnId">
                <input type="hidden" id="assignReturnOrderId">
                
                <div class="form-group">
                    <label class="form-label">
                        Select Delivery Rider 
                        <span class="required">*</span>
                    </label>
                    <select id="returnRiderSelect" class="form-select" required>
                        <option value="">-- Loading riders... --</option>
                    </select>
                    <small style="color: #666; font-size: 13px; margin-top: 4px;">Choose the rider to pickup the returned item</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAssignReturnRiderModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Assign Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Return Modal -->
<div id="rejectReturnModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-times-circle"></i> Reject Return Request</h2>
            <button class="modal-close" onclick="closeRejectReturnModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="rejectReturnForm" onsubmit="confirmRejectReturn(event)">
                <input type="hidden" id="rejectReturnId">
                
                <div class="form-group">
                    <label class="form-label">
                        Rejection Reason 
                        <span class="required">*</span>
                    </label>
                    <textarea id="rejectReturnMessage" class="form-control" rows="5" 
                              placeholder="Explain why this return request is being rejected..." required></textarea>
                    <small style="color: #666; font-size: 13px; margin-top: 4px;">Customer will receive this message</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectReturnModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check-circle"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

