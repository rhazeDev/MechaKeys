<?php
require_once __DIR__ . '/../../conn.php';

$isLoggedIn = isset($_SESSION['user_id']);

$sql = "SELECT 
            p.ProductID,
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
            p.TotalSold,
            (SELECT Path FROM ProductImages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            (SELECT MIN(Price) FROM ProductVariations WHERE ProductID = p.ProductID) as MinPrice,
            (SELECT MAX(Price) FROM ProductVariations WHERE ProductID = p.ProductID) as MaxPrice,
            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations
        FROM Products p
        LEFT JOIN ProductVariations pv ON p.ProductID = pv.ProductID
        GROUP BY p.ProductID
        ORDER BY p.TotalSold DESC, p.ProductID DESC
        LIMIT 8";

$result = $conn->query($sql);
$featured_products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imagePath = $row['ImagePath'];
        if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
            $imagePath = substr($imagePath, strlen('mechakeys/'));
        }
        
        $minPrice = floatval($row['MinPrice'] ?? 0);
        $maxPrice = floatval($row['MaxPrice'] ?? 0);
        $priceDisplay = '';
        if ($minPrice > 0 && $maxPrice > 0) {
            if ($minPrice == $maxPrice) {
                $priceDisplay = '₱' . number_format($minPrice, 2);
            } else {
                $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
            }
        } else {
            $priceDisplay = 'Price not available';
        }
        
        $featured_products[] = [
            'id' => $row['ProductID'],
            'name' => $row['Brand'] . ' ' . $row['Model'],
            'description' => $row['Description'],
            'category' => $row['Category'],
            'price' => $priceDisplay,
            'image' => $imagePath ? '../' . $imagePath : null,
            'specs' => $row['Variations'] ? $row['Variations'] : $row['Category'],
            'total_sold' => $row['TotalSold']
        ];
    }
}
?>

<section class="section">
    <h2 class="section-title">
        <i class="fas fa-fire"></i>
        Popular Products
    </h2>

    <?php if (!$isLoggedIn): ?>
        <div class="login-prompt">
            <p><i class="fas fa-lock"></i> <strong>Sign in to add items to cart and checkout</strong></p>
            <a href="../login.php" class="btn-primary" style="display: inline-block; text-decoration: none;">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        </div>
    <?php endif; ?>

    <div class="products-grid">
        <?php if (empty($featured_products)): ?>
            <p style="text-align: center; width: 100%; padding: 2rem;">No products available at the moment.</p>
        <?php else: ?>
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'" style="cursor: pointer;">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 4rem;">⌨️</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-content">
                        <div class="product-header">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <?php if ($product['total_sold'] > 0): ?>
                                <div class="product-sold-badge">
                                    <i class="fas fa-fire"></i> <?php echo $product['total_sold']; ?> sold
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-specs">
                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($product['specs']); ?>
                        </div>
                        <div class="product-footer">
                            <div class="product-price"><?php echo htmlspecialchars($product['price']); ?></div>
                            <div class="product-actions">
                                <?php if ($isLoggedIn): ?>
                                    <button class="btn-cart" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button class="btn-cart disabled" disabled title="Login to add to cart" onclick="event.stopPropagation();">
                                        <i class="fas fa-lock"></i> Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
