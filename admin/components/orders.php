<!-- Orders Management Section -->
<section id="orders" class="section">
    <!-- Filter and Search Bar -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Order Management</h2>
            <div class="panel-actions">
                <select id="orderStatusFilter" class="filter-select" onchange="filterOrders()">
                    <option value="all">All Orders</option>
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
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Delivery Rider</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <tr>
                        <td colspan="9" class="text-center">
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

<!-- Update Status Modal -->
<div id="updateStatusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Update Order Status</h3>
            <button class="modal-close" onclick="closeUpdateStatusModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="updateStatusForm" onsubmit="submitUpdateStatus(event)">
                <input type="hidden" id="updateOrderId" name="order_id">
                <input type="hidden" id="updateTrackingId" name="tracking_id">
                
                <div class="form-group">
                    <label for="deliveryStatus">Delivery Status</label>
                    <select id="deliveryStatus" name="delivery_status" class="form-control" required>
                        <option value="Processing">Processing</option>
                        <option value="Assigned">Assigned</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="paymentStatus">Payment Status</label>
                    <select id="paymentStatus" name="payment_status" class="form-control" required>
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Failed">Failed</option>
                        <option value="Refunded">Refunded</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUpdateStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
