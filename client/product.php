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
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
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
            Price,
            StockQuantity
        FROM ProductVariations 
        WHERE ProductID = ?";
$stmt_var = $conn->prepare($sql_variations);
$stmt_var->bind_param("i", $product_id);
$stmt_var->execute();
$variations_result = $stmt_var->get_result();
$variations = [];
$minPrice = null;
$maxPrice = null;
while ($var = $variations_result->fetch_assoc()) {
    $variations[] = $var;
    $price = floatval($var['Price']);
    if ($minPrice === null || $price < $minPrice) {
        $minPrice = $price;
    }
    if ($maxPrice === null || $price > $maxPrice) {
        $maxPrice = $price;
    }
}

$productName = $product['Brand'] . ' ' . $product['Model'];

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productName); ?> | MechaKeys</title>
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
            <span class="current"><?php echo htmlspecialchars($productName); ?></span>
        </nav>

        <div class="product-detail-container">
            <div class="product-gallery">
                <div class="main-image" onclick="maximizeImage()">
                    <?php if (!empty($images)): ?>
                        <img id="mainImage" src="../<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($productName); ?>">
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

                <h1 class="product-title"><?php echo htmlspecialchars($productName); ?></h1>

                <?php if (!empty($variations)): ?>
                    <!-- Stock and Price display at top -->
                    <div id="topPriceStock" class="top-price-stock" style="display: none; flex-direction: column;">
                        <div class="stock-display">
                            Stock: <span id="topStock">0</span>
                        </div>
                        <div class="price-display" id="topPrice">
                            0.00
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($variations)): ?>
                    <div class="product-price-section">
                        <div class="price">
                            <?php 
                            if ($minPrice !== null && $maxPrice !== null) {
                                if ($minPrice == $maxPrice) {
                                    echo '₱' . number_format($minPrice, 2);
                                } else {
                                    echo '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                                }
                            } else {
                                echo 'Price not available';
                            }
                            ?>
                        </div>
                        <div class="price-label">Price</div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($variations)): ?>
                    <input type="hidden" id="variationsData" value='<?php echo json_encode($variations); ?>'>
                    
                    <div class="product-variations">
                        <h3><i class="fas fa-sliders-h"></i> Available Variations</h3>
                        
                        <?php
                                                $layouts = array_unique(array_column($variations, 'Layout'));
                        $switches = array_unique(array_column($variations, 'SwitchType'));
                        $colors = array_unique(array_column($variations, 'Color'));
                        sort($layouts);
                        sort($switches);
                        sort($colors);
                        ?>

                        <?php if (count($layouts) > 1): ?>
                            <div class="variation-selector">
                                <label class="variation-label">LAYOUT:</label>
                                <div class="variation-options" id="layoutOptions">
                                    <?php foreach ($layouts as $layout): ?>
                                        <button class="variation-option-btn" 
                                                data-type="layout" 
                                                data-value="<?php echo htmlspecialchars($layout); ?>"
                                                onclick="selectVariation('layout', '<?php echo htmlspecialchars($layout); ?>')">
                                            <?php echo htmlspecialchars($layout); ?>%
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($switches) > 1): ?>
                            <div class="variation-selector">
                                <label class="variation-label">SWITCH:</label>
                                <div class="variation-options" id="switchOptions">
                                    <?php foreach ($switches as $switch): ?>
                                        <button class="variation-option-btn" 
                                                data-type="switch" 
                                                data-value="<?php echo htmlspecialchars($switch); ?>"
                                                onclick="selectVariation('switch', '<?php echo htmlspecialchars($switch); ?>')">
                                            <?php echo htmlspecialchars($switch); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($colors) > 1): ?>
                            <div class="variation-selector">
                                <label class="variation-label">COLOR:</label>
                                <div class="variation-options" id="colorOptions">
                                    <?php foreach ($colors as $color): ?>
                                        <button class="variation-option-btn" 
                                                data-type="color" 
                                                data-value="<?php echo htmlspecialchars($color); ?>"
                                                onclick="selectVariation('color', '<?php echo htmlspecialchars($color); ?>')">
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
                        <button class="btn-add-cart disabled" onclick="addToCart(<?php echo $product['ProductID']; ?>)" disabled>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn-buy-now disabled" onclick="buyNow(<?php echo $product['ProductID']; ?>)" disabled>
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

    <?php // include 'components/footer.php'; ?>

    <div id="imageModal" class="image-modal" onclick="closeModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        let allVariations = [];
        let selectedVariation = {
            layout: null,
            switch: null,
            color: null
        };
        let currentVariationData = null;

                document.addEventListener('DOMContentLoaded', function() {
            const variationsDataEl = document.getElementById('variationsData');
            if (variationsDataEl) {
                allVariations = JSON.parse(variationsDataEl.value);
                
                                const uniqueLayouts = [...new Set(allVariations.map(v => v.Layout))];
                const uniqueSwitches = [...new Set(allVariations.map(v => v.SwitchType))];
                const uniqueColors = [...new Set(allVariations.map(v => v.Color))];
                
                if (uniqueLayouts.length === 1) {
                    selectedVariation.layout = uniqueLayouts[0].toString();
                }
                if (uniqueSwitches.length === 1) {
                    selectedVariation.switch = uniqueSwitches[0];
                }
                if (uniqueColors.length === 1) {
                    selectedVariation.color = uniqueColors[0];
                }
                
                updateAvailableOptions();
                checkVariationComplete();
            }

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

        function selectVariation(type, value) {
                        if (selectedVariation[type] === value) {
                selectedVariation[type] = null;
            } else {
                selectedVariation[type] = value;
            }

                        updateVariationUI(type);
            updateAvailableOptions();
            checkVariationComplete();
        }

        function updateVariationUI(type) {
                        const buttons = document.querySelectorAll(`[data-type="${type}"]`);
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.value === selectedVariation[type]) {
                    btn.classList.add('active');
                }
            });
        }

        function updateAvailableOptions() {
                        let filteredVariations = allVariations;

            if (selectedVariation.layout) {
                filteredVariations = filteredVariations.filter(v => v.Layout == selectedVariation.layout);
            }
            if (selectedVariation.switch) {
                filteredVariations = filteredVariations.filter(v => v.SwitchType === selectedVariation.switch);
            }
            if (selectedVariation.color) {
                filteredVariations = filteredVariations.filter(v => v.Color === selectedVariation.color);
            }

                        const availableLayouts = [...new Set(filteredVariations.map(v => v.Layout))];
            const availableSwitches = [...new Set(filteredVariations.map(v => v.SwitchType))];
            const availableColors = [...new Set(filteredVariations.map(v => v.Color))];

                        updateOptionButtons('layout', availableLayouts);
            updateOptionButtons('switch', availableSwitches);
            updateOptionButtons('color', availableColors);
        }

        function updateOptionButtons(type, availableValues) {
            const buttons = document.querySelectorAll(`[data-type="${type}"]`);
            buttons.forEach(btn => {
                const value = type === 'layout' ? parseInt(btn.dataset.value) : btn.dataset.value;
                const isAvailable = availableValues.some(v => 
                    type === 'layout' ? v == value : v === value
                );
                
                if (isAvailable) {
                    btn.classList.remove('disabled');
                    btn.disabled = false;
                } else {
                    btn.classList.add('disabled');
                    btn.disabled = true;
                                        if (selectedVariation[type] === btn.dataset.value) {
                        selectedVariation[type] = null;
                        btn.classList.remove('active');
                    }
                }
            });
        }

        function checkVariationComplete() {
                        const hasLayout = selectedVariation.layout !== null;
            const hasSwitch = selectedVariation.switch !== null;
            const hasColor = selectedVariation.color !== null;

                        if (hasLayout && hasSwitch && hasColor) {
                currentVariationData = allVariations.find(v => 
                    v.Layout == selectedVariation.layout &&
                    v.SwitchType === selectedVariation.switch &&
                    v.Color === selectedVariation.color
                );

                if (currentVariationData) {
                    const stock = parseInt(currentVariationData.StockQuantity);
                    
                    const topPriceStock = document.getElementById('topPriceStock');
                    if (topPriceStock) {
                        topPriceStock.style.display = 'flex';
                        document.getElementById('topStock').textContent = stock;
                        document.getElementById('topPrice').textContent = '₱' + parseFloat(currentVariationData.Price).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }

                    const qtyInput = document.getElementById('quantity');
                    if (qtyInput) {
                        qtyInput.max = stock;
                        if (parseInt(qtyInput.value) > stock) {
                            qtyInput.value = Math.max(1, stock);
                        }
                    }

                                        updateActionButtons(stock > 0);
                } else {
                    resetVariationInfo();
                }
            } else {
                resetVariationInfo();
            }
        }

        function resetVariationInfo() {
            const topPriceStock = document.getElementById('topPriceStock');
            if (topPriceStock) {
                topPriceStock.style.display = 'none';
            }
            currentVariationData = null;
            updateActionButtons(false);
        }

        function updateActionButtons(enabled) {
            const addToCartBtn = document.querySelector('.btn-add-cart');
            const buyNowBtn = document.querySelector('.btn-buy-now');
            
            if (addToCartBtn && addToCartBtn.tagName === 'BUTTON') {
                addToCartBtn.disabled = !enabled;
                if (enabled) {
                    addToCartBtn.classList.remove('disabled');
                } else {
                    addToCartBtn.classList.add('disabled');
                }
            }
            
            if (buyNowBtn && buyNowBtn.tagName === 'BUTTON') {
                buyNowBtn.disabled = !enabled;
                if (enabled) {
                    buyNowBtn.classList.remove('disabled');
                } else {
                    buyNowBtn.classList.add('disabled');
                }
            }
        }

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
            const maxVal = parseInt(qtyInput.max);
            if (currentVal < maxVal) {
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

        function addToCart(productId) {
            if (!currentVariationData) {
                alert('Please select all variation options (Layout, Switch, Color)');
                return;
            }
            
            if (currentVariationData.StockQuantity <= 0) {
                alert('This variation is out of stock');
                return;
            }
            
            const quantity = document.getElementById('quantity').value;
            alert(`Adding to cart:\nLayout: ${selectedVariation.layout}%\nSwitch: ${selectedVariation.switch}\nColor: ${selectedVariation.color}\nQuantity: ${quantity}\nPrice: ₱${parseFloat(currentVariationData.Price).toFixed(2)}`);
                    }

        function buyNow(productId) {
            if (!currentVariationData) {
                alert('Please select all variation options (Layout, Switch, Color)');
                return;
            }
            
            if (currentVariationData.StockQuantity <= 0) {
                alert('This variation is out of stock');
                return;
            }
            
            const quantity = document.getElementById('quantity').value;
            alert(`Buy now:\nLayout: ${selectedVariation.layout}%\nSwitch: ${selectedVariation.switch}\nColor: ${selectedVariation.color}\nQuantity: ${quantity}\nPrice: ₱${parseFloat(currentVariationData.Price).toFixed(2)}`);
                    }
    </script>
</body>

</html>
