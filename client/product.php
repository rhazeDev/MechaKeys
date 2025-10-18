<?php
session_start();
require_once '../conn.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

$sql = "SELECT 
            p.ProductID,
            p.Name,
            p.Description,
            p.Category,
            p.Price,
            p.TotalSold,
            p.ProductImageID
        FROM Products p
        WHERE p.ProductID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();

$sql_images = "SELECT Path FROM ProductImages WHERE ProductImageID = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $product['ProductImageID']);
$stmt_images->execute();
$images_result = $stmt_images->get_result();
$images = [];
while ($img = $images_result->fetch_assoc()) {
    $imagePath = $img['Path'];
    if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
        $imagePath = substr($imagePath, strlen('mechakeys/'));
    }
    $images[] = $imagePath;
}

$sql_variations = "SELECT 
            VariationID,
            Layout,
            SwitchType,
            Color,
            StockQuantity
        FROM ProductVariations 
        WHERE ProductID = ?";
$stmt_var = $conn->prepare($sql_variations);
$stmt_var->bind_param("i", $product_id);
$stmt_var->execute();
$variations_result = $stmt_var->get_result();
$variations = [];
while ($var = $variations_result->fetch_assoc()) {
    $variations[] = $var;
}

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['Name']); ?> | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/product.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="main-content">
        <nav class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <span class="separator">/</span>
            <a href="index.php#products">Products</a>
            <span class="separator">/</span>
            <span class="current"><?php echo htmlspecialchars($product['Name']); ?></span>
        </nav>

        <div class="product-detail-container">
            <div class="product-gallery">
                <div class="main-image" onclick="maximizeImage()">
                    <?php if (!empty($images)): ?>
                        <img id="mainImage" src="../<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['Name']); ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-keyboard"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="thumbnail-gallery">
                        <?php foreach ($images as $index => $image): ?>
                            <img src="../<?php echo htmlspecialchars($image); ?>" 
                                 alt="Thumbnail <?php echo $index + 1; ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                 onclick="changeImage(this, '../<?php echo htmlspecialchars($image); ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="product-badge-group">
                    <span class="category-badge">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['Category']); ?>
                    </span>
                    <?php if ($product['TotalSold'] > 0): ?>
                        <span class="sold-badge">
                            <i class="fas fa-fire"></i> <?php echo $product['TotalSold']; ?> Sold
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="product-title"><?php echo htmlspecialchars($product['Name']); ?></h1>

                <div class="product-price-section">
                    <div class="price">₱<?php echo number_format($product['Price'], 2); ?></div>
                    <div class="price-label">Price</div>
                </div>

                <?php if (!empty($variations)): ?>
                    <div class="product-variations">
                        <h3><i class="fas fa-sliders-h"></i> Available Variations</h3>
                        
                        <?php
                        $layouts = array_unique(array_column($variations, 'Layout'));
                        $switches = array_unique(array_column($variations, 'SwitchType'));
                        $colors = array_unique(array_column($variations, 'Color'));
                        ?>

                        <?php if (count($layouts) > 0): ?>
                            <div class="variation-group">
                                <label>Layout:</label>
                                <div class="variation-options">
                                    <?php foreach ($layouts as $layout): ?>
                                        <button class="variation-btn" data-type="layout" data-value="<?php echo htmlspecialchars($layout); ?>">
                                            <?php echo htmlspecialchars($layout); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($switches) > 0): ?>
                            <div class="variation-group">
                                <label>Switch Type:</label>
                                <div class="variation-options">
                                    <?php foreach ($switches as $switch): ?>
                                        <button class="variation-btn" data-type="switch" data-value="<?php echo htmlspecialchars($switch); ?>">
                                            <?php echo htmlspecialchars($switch); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($colors) > 0): ?>
                            <div class="variation-group">
                                <label>Color:</label>
                                <div class="variation-options">
                                    <?php foreach ($colors as $color): ?>
                                        <button class="variation-btn color-option" data-type="color" data-value="<?php echo htmlspecialchars($color); ?>">
                                            <?php echo htmlspecialchars($color); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="quantity-section">
                    <label>Quantity:</label>
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="decreaseQty()">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" value="1" min="1" max="99" readonly>
                        <button class="qty-btn" onclick="increaseQty()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="action-buttons">
                    <?php if ($isLoggedIn): ?>
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $product['ProductID']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn-buy-now" onclick="buyNow(<?php echo $product['ProductID']; ?>)">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                    <?php else: ?>
                        <a href="../login.php" class="btn-add-cart" style="text-decoration: none; text-align: center;">
                            <i class="fas fa-lock"></i> Login to Purchase
                        </a>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['Description'])); ?></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function maximizeImage() {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const mainImg = document.getElementById('mainImage');
            
            if (mainImg) {
                modal.style.display = 'flex';
                modalImg.src = mainImg.src;
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        function changeImage(thumbnail, imagePath) {
            document.getElementById('mainImage').src = imagePath;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        function increaseQty() {
            const qtyInput = document.getElementById('quantity');
            const currentVal = parseInt(qtyInput.value);
            if (currentVal < 99) {
                qtyInput.value = currentVal + 1;
            }
        }

        function decreaseQty() {
            const qtyInput = document.getElementById('quantity');
            const currentVal = parseInt(qtyInput.value);
            if (currentVal > 1) {
                qtyInput.value = currentVal - 1;
            }
        }

        document.querySelectorAll('.variation-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                this.parentElement.querySelectorAll('.variation-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        function addToCart(productId) {
            alert('awan pay kas');
        }

        function buyNow(productId) {
            alert('awan pay kas');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>

</html>
