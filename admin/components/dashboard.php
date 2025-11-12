<!-- Dashboard Section -->
<section id="dashboard" class="section active">
    <!-- Sales Section -->
    <div class="dashboard-section">
        <h2 class="section-title">Sales Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Orders</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $total_orders; ?></div>
                <div class="stat-change">
                    All customer orders
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pending Orders</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $pending_orders; ?></div>
                <div class="stat-change <?php echo $pending_orders > 0 ? 'negative' : 'positive'; ?>">
                    <?php if ($pending_orders > 0): ?>
                        <i class="fas fa-exclamation-circle"></i>
                        Awaiting processing
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i>
                        All orders processed
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">In Transit</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $in_transit_orders; ?></div>
                <div class="stat-change">
                    Out for delivery
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Revenue</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                </div>
                <div class="stat-value">₱<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    From paid orders
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Delivered Orders</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $delivered_orders; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-check-circle"></i>
                    Successfully completed
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Section -->
    <div class="dashboard-section">
        <h2 class="section-title">Inventory Management</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Products</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $total_products; ?></div>
                <div class="stat-change">
                    Active products in store
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Total Stock</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $total_stock ?? 0; ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-check-circle"></i>
                    Units in inventory
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Low Stock Alert</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $low_stock_count; ?></div>
                <div class="stat-change <?php echo $low_stock_count > 0 ? 'negative' : 'positive'; ?>">
                    <?php if ($low_stock_count > 0): ?>
                        <i class="fas fa-arrow-down"></i>
                        Products need restocking
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i>
                        All stock levels good
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Section -->
    <div class="dashboard-section">
        <h2 class="section-title">Delivery Operations</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Delivery Riders</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="stat-value" id="deliveryRidersCount">0</div>
                <div class="stat-change">
                    Active delivery personnel
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Active Deliveries</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
                <div class="stat-value" id="activeDeliveriesCount">0</div>
                <div class="stat-change">
                    In progress deliveries
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Delivered Today</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="deliveredTodayCount">0</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    Today's deliveries
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-title">Pending Assignments</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-value" id="pendingAssignmentsCount">0</div>
                <div class="stat-change">
                    Awaiting rider assignment
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h3>Order Status Distribution</h3>
            <canvas id="orderStatusChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Revenue Overview</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Product Categories</h3>
            <canvas id="productCategoriesChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Stock Levels</h3>
            <canvas id="stockChart"></canvas>
        </div>
    </div>
</section>
