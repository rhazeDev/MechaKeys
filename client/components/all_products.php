<?php
require_once __DIR__ . '/../../conn.php';

$isLoggedIn = isset($_SESSION['user_id']);

$category = isset($_GET['category']) ? $_GET['category'] : '';

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
        LEFT JOIN ProductVariations pv ON p.ProductID = pv.ProductID";

if (!empty($category)) {
    $sql .= " WHERE p.Category = ?";
}

$sql .= " GROUP BY p.ProductID
          ORDER BY p.TotalSold DESC, p.ProductID DESC";

if (!empty($category)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$products = [];

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

        $products[] = [
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

$categoryTitle = 'All Products';
if (!empty($category)) {
    $categoryTitle = ucfirst($category);
    if ($category == 'keyboard') {
        $categoryTitle = 'Keyboards';
    } elseif ($category == 'switches') {
        $categoryTitle = 'Switches';
    } elseif ($category == 'keycaps') {
        $categoryTitle = 'Keycaps';
    } elseif ($category == 'accessories') {
        $categoryTitle = 'Accessories';
    }
}
?>

<section class="section">
    <h2 class="section-title">
        <?php echo htmlspecialchars($categoryTitle); ?>
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
        <?php if (empty($products)): ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-box-open"></i>
                <h3>No products found</h3>
                <p>There are currently no products in this category.</p>
                <a href="index.php" class="btn-view-all">
                    View All Products
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'"
                    style="cursor: pointer;">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="width: 100%; height: 100%; object-fit: cover;">
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
                        <?php if ($product['category'] === 'keyboard'): ?>
                            <div class="product-specs">
                                <?php echo htmlspecialchars($product['specs']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="product-footer">
                            <div class="product-price"><?php echo htmlspecialchars($product['price']); ?></div>
                            <div class="product-actions">
                                <?php if ($isLoggedIn): ?>
                                    <button class="btn-cart"
                                        onclick="event.stopPropagation(); window.location.href='product.php?id=<?php echo $product['id']; ?>'"
                                        title="Add to Cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn-cart disabled" disabled title="Login to add to cart"
                                        onclick="event.stopPropagation();">
                                        <i class="fas fa-lock"></i>
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