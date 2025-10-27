<!-- Dashboard Section -->
<section id="dashboard" class="section active">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <!-- Orders Stats -->
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

        <!-- Product Stats -->
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
</section>
