<!-- Dashboard Section -->
<section id="dashboard" class="section active">
    <!-- Statistics Cards -->
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
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                Active products in store
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-title">Total Variations</div>
                </div>
                <div class="stat-icon info">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
            <div class="stat-value"><?php echo $total_variations; ?></div>
            <div class="stat-change">
                Product variations available
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

    <!-- Recent Products -->
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Recent Products</h2>
            <div class="panel-actions">
                <button class="btn btn-primary" onclick="showSection('add-product')">
                    <i class="fas fa-plus"></i>
                    Add Product
                </button>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <?php if ($product['ImagePath']): ?>
                                            <img src="/<?php echo htmlspecialchars($product['ImagePath']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['Brand'] . ' ' . $product['Model']); ?>" 
                                                 class="product-image">
                                        <?php else: ?>
                                            <div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-keyboard" style="color: #ccc;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="product-info">
                                            <span class="product-name"><?php echo htmlspecialchars($product['Brand'] . ' ' . $product['Model']); ?></span>
                                            <span class="product-category">ID: #<?php echo $product['ProductID']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['Brand']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($product['Category'])); ?></td>
                                <td>
                                    <?php
                                    $minPrice = floatval($product['MinPrice'] ?? 0);
                                    $maxPrice = floatval($product['MaxPrice'] ?? 0);
                                    if ($minPrice == $maxPrice) {
                                        echo '₱' . number_format($minPrice, 2);
                                    } else {
                                        echo '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                                    }
                                    ?>
                                </td>
                                <td><?php echo $product['TotalStock'] ?? 0; ?> units</td>
                                <td>
                                    <?php 
                                    $stock = $product['TotalStock'] ?? 0;
                                    if ($stock == 0): ?>
                                        <span class="badge error">Out of Stock</span>
                                    <?php elseif ($stock < 10): ?>
                                        <span class="badge warning">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
