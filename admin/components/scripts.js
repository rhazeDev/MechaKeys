function showCustomConfirm(message, onOk) {
    const dialog = document.getElementById('customConfirmDialog');
    document.getElementById('confirmDialogMessage').innerHTML = message;
    dialog.style.display = 'flex';
    const okBtn = document.getElementById('confirmDialogOkBtn');
    function okHandler() {
        dialog.style.display = 'none';
        okBtn.removeEventListener('click', okHandler);
        onOk();
    }
    okBtn.addEventListener('click', okHandler);
}

function closeCustomConfirm() {
    document.getElementById('customConfirmDialog').style.display = 'none';
}
let variationCount = 1;

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();
        const section = this.dataset.section;
        showSection(section);

        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
    });
});

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');

    document.querySelectorAll('.nav-item').forEach(nav => {
        if (nav.dataset.section === sectionId) {
            nav.classList.add('active');
        } else {
            nav.classList.remove('active');
        }
    });

    if (sectionId === 'dashboard') {
        loadDashboard();
    } else if (sectionId === 'products') {
        loadProducts();
    } else if (sectionId === 'inventory') {
        loadInventory();
    } else if (sectionId === 'orders') {
        loadOrders();
    }
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('layout-select')) {
        const index = e.target.dataset.index;
        const customLayoutGroup = document.getElementById('customLayout' + index);
        const customLayoutInput = customLayoutGroup ? customLayoutGroup.querySelector('input') : null;

        if (e.target.value === 'custom') {
            if (customLayoutGroup) {
                customLayoutGroup.style.display = 'block';
                if (customLayoutInput) customLayoutInput.required = true;
            }
        } else {
            if (customLayoutGroup) {
                customLayoutGroup.style.display = 'none';
                if (customLayoutInput) {
                    customLayoutInput.required = false;
                    customLayoutInput.value = '';
                }
            }
        }
    }

    if (e.target.id === 'categorySelect') {
        const brandFieldGroup = document.getElementById('brandFieldGroup');
        const brandSelect = document.getElementById('brandSelect');
        const keyboardOnlyFields = document.querySelectorAll('.keyboard-only-field');

        if (e.target.value === 'keyboard') {
            brandFieldGroup.style.display = '';
            brandSelect.required = true;

            keyboardOnlyFields.forEach(field => {
                field.style.display = '';
                const inputs = field.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (!input.classList.contains('custom-layout-group')) {
                        input.required = true;
                    }
                });
            });
        } else if (e.target.value) {
            brandFieldGroup.style.display = 'none';
            brandSelect.required = false;
            brandSelect.value = '';

            keyboardOnlyFields.forEach(field => {
                field.style.display = 'none';
                const inputs = field.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.required = false;
                    if (input.tagName === 'SELECT') {
                        input.value = '';
                    } else if (input.type === 'text') {
                        input.value = '';
                    }
                });
            });
        } else {
            brandFieldGroup.style.display = '';
            brandSelect.required = true;

            keyboardOnlyFields.forEach(field => {
                field.style.display = '';
            });
        }
    }
});

document.getElementById('productImages').addEventListener('change', function (e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    Array.from(this.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="preview-remove" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function removeImage(index) {
    const input = document.getElementById('productImages');
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    files.splice(index, 1);
    files.forEach(file => dt.items.add(file));
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}

function addVariation() {
    const container = document.getElementById('variationsContainer');
    const categorySelect = document.getElementById('categorySelect');
    const isKeyboard = categorySelect.value === 'keyboard';

    const div = document.createElement('div');
    div.className = 'variation-item';
    div.innerHTML = `
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Layout <span class="required">*</span></label>
            <select name="variations[${variationCount}][layout]" class="form-select layout-select" data-index="${variationCount}" ${isKeyboard ? 'required' : ''}>
                <option value="">Select layout</option>
                <option value="100">100% (Full Size)</option>
                <option value="96">96%</option>
                <option value="80">80% (TKL)</option>
                <option value="75">75%</option>
                <option value="65">65%</option>
                <option value="60">60%</option>
                <option value="40">40%</option>
                <option value="custom">Custom (Type below)</option>
            </select>
        </div>
        <div class="form-group custom-layout-group keyboard-only-field" id="customLayout${variationCount}" style="display: none;">
            <label class="form-label">Custom Layout</label>
            <input type="text" name="variations[${variationCount}][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
        </div>
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Switch Type</label>
            <input type="text" name="variations[${variationCount}][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" ${isKeyboard ? 'required' : ''}>
        </div>
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Color</label>
            <input type="text" name="variations[${variationCount}][color]" class="form-input" placeholder="e.g., Blue, Black" ${isKeyboard ? 'required' : ''}>
        </div>
        <div class="form-group">
            <label class="form-label">Price (₱)</label>
            <input type="number" name="variations[${variationCount}][price]" class="form-input" placeholder="0.00" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="variations[${variationCount}][stock]" class="form-input" placeholder="0" min="0" required>
        </div>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    variationCount++;
}

document.getElementById('addProductForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    const layoutSelects = document.querySelectorAll('.layout-select');
    layoutSelects.forEach((select, index) => {
        if (select.value === 'custom') {
            const customLayoutInput = document.querySelector(`input[name="variations[${index}][custom_layout]"]`);
            if (customLayoutInput && customLayoutInput.value) {
                formData.set(`variations[${index}][layout]`, customLayoutInput.value);
            }
        }
    });

    const messageDiv = document.getElementById('add-product-message');

    try {
        const response = await fetch('api/add_product.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            messageDiv.innerHTML = `
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    ${result.message}
                </div>
            `;
            this.reset();
            document.getElementById('imagePreview').innerHTML = '';
            variationCount = 1;

            const container = document.getElementById('variationsContainer');
            container.innerHTML = `
                <div class="variation-item">
                    <div class="form-group">
                        <label class="form-label">Layout <span class="required">*</span></label>
                        <select name="variations[0][layout]" class="form-select layout-select" data-index="0" required>
                            <option value="">Select layout</option>
                            <option value="100">100% (Full Size)</option>
                            <option value="96">96%</option>
                            <option value="80">80% (TKL)</option>
                            <option value="75">75%</option>
                            <option value="65">65%</option>
                            <option value="60">60%</option>
                            <option value="40">40%</option>
                            <option value="custom">Custom (Type below)</option>
                        </select>
                    </div>
                    <div class="form-group custom-layout-group" id="customLayout0" style="display: none;">
                        <label class="form-label">Custom Layout</label>
                        <input type="text" name="variations[0][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Switch Type</label>
                        <input type="text" name="variations[0][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <input type="text" name="variations[0][color]" class="form-input" placeholder="e.g., Blue, Black" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" name="variations[0][price]" class="form-input" placeholder="0.00" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="variations[0][stock]" class="form-input" placeholder="0" min="0" required>
                    </div>
                </div>
            `;

            setTimeout(() => {
                showSection('dashboard');
                messageDiv.innerHTML = '';
            }, 2000);
        } else {
            messageDiv.innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    ${result.message}
                </div>
            `;
        }
    } catch (error) {
        messageDiv.innerHTML = `
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                An error occurred. Please try again.
            </div>
        `;
    }

    window.scrollTo(0, 0);
});

async function loadDashboard() {
    try {
        const response = await fetch('api/get_dashboard.php');
        const result = await response.json();

        if (result.success) {
            const stats = result.stats;

            document.getElementById('deliveryRidersCount').textContent = stats.delivery_riders || 0;
            document.getElementById('activeDeliveriesCount').textContent = stats.active_deliveries || 0;
            document.getElementById('deliveredTodayCount').textContent = stats.delivered_today || 0;
            document.getElementById('pendingAssignmentsCount').textContent = stats.pending_assignments || 0;

            const tbody = document.querySelector('#dashboard .data-table tbody');
            if (result.products && result.products.length > 0) {
                tbody.innerHTML = result.products.map(product => {
                    const minPrice = parseFloat(product.MinPrice || 0);
                    const maxPrice = parseFloat(product.MaxPrice || 0);
                    const priceDisplay = minPrice === maxPrice
                        ? `₱${minPrice.toFixed(2)}`
                        : `₱${minPrice.toFixed(2)} - ₱${maxPrice.toFixed(2)}`;

                    const stock = parseInt(product.TotalStock || 0);
                    let statusBadge;
                    if (stock === 0) {
                        statusBadge = '<span class="badge error">Out of Stock</span>';
                    } else if (stock < 10) {
                        statusBadge = '<span class="badge warning">Low Stock</span>';
                    } else {
                        statusBadge = '<span class="badge success">In Stock</span>';
                    }

                    const imagePath = product.ImagePath
                        ? `<img src="/${product.ImagePath}" alt="${product.Brand} ${product.Model}" class="product-image">`
                        : `<div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-keyboard" style="color: #ccc;"></i>
                           </div>`;

                    return `
                        <tr>
                            <td>
                                <div class="product-cell">
                                    ${imagePath}
                                    <div class="product-info">
                                        <span class="product-name">${product.Brand} ${product.Model}</span>
                                        <span class="product-category">ID: #${product.ProductID}</span>
                                    </div>
                                </div>
                            </td>
                            <td>${product.Brand}</td>
                            <td>${product.Category.charAt(0).toUpperCase() + product.Category.slice(1)}</td>
                            <td>${priceDisplay}</td>
                            <td>${stock} units</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No products found</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
    }
}

async function loadProducts() {
    const container = document.getElementById('products-list');
    container.innerHTML = '<div class="text-center">Loading...</div>';

    try {
        const response = await fetch('api/get_products.php');
        const result = await response.json();

        if (result.success) {
            container.innerHTML = `
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Total Stock</th>
                                <th>Sold</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${result.products.map(product => `
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            ${product.ImagePath ?
                    `<img src="/${product.ImagePath}" alt="${product.Model}" class="product-image">` :
                    '<div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;"><i class="fas fa-keyboard" style="color: #ccc;"></i></div>'
                }
                                            <div class="product-info">
                                                <span class="product-name">${product.Brand} ${product.Model}</span>
                                                <span class="product-category">ID: #${product.ProductID}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${product.Brand}</td>
                                    <td>${product.Category}</td>
                                    <td>₱${product.Price}</td>
                                    <td>${product.TotalStock || 0} units</td>
                                    <td>${product.TotalSold || 0}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn edit" onclick="editProduct(${product.ProductID})">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="action-btn delete" onclick="deleteProduct(${product.ProductID})">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="alert error">Failed to load products</div>';
    }
}

async function loadInventory() {
    const container = document.getElementById('inventory-list');
    container.innerHTML = '<div class="text-center">Loading...</div>';

    try {
        const response = await fetch('api/get_inventory.php');
        const result = await response.json();

        if (result.success) {
            container.innerHTML = `
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Layout</th>
                                <th>Switch Type</th>
                                <th>Color</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${result.inventory.map(item => `
                                <tr>
                                    <td>${item.ProductBrand} ${item.ProductModel}</td>
                                    <td>${isNaN(item.Layout) || item.Layout >= 100 ? item.Layout : item.Layout + '%'}</td>
                                    <td>${item.SwitchType}</td>
                                    <td>${item.Color}</td>
                                    <td>₱${parseFloat(item.Price).toFixed(2)}</td>
                                    <td>${item.StockQuantity} units</td>
                                    <td>
                                        ${item.StockQuantity == 0 ?
                    '<span class="badge error">Out of Stock</span>' :
                    item.StockQuantity < 10 ?
                        '<span class="badge warning">Low Stock</span>' :
                        '<span class="badge success">In Stock</span>'
                }
                                    </td>
                                    <td>
                                        <button class="action-btn edit" onclick="openEditStock(${item.VariationID}, '${item.ProductBrand} ${item.ProductModel}', '${isNaN(item.Layout) || item.Layout >= 100 ? item.Layout : item.Layout + '%'} - ${item.SwitchType} - ${item.Color}', ${item.StockQuantity})">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="alert error">Failed to load inventory</div>';
    }
}

function openEditStock(variationId, productName, variationDetails, currentStock) {
    document.getElementById('edit_variation_id').value = variationId;
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_variation_details').value = variationDetails;
    document.getElementById('edit_new_stock').value = currentStock;
    document.getElementById('editStockModal').classList.add('active');
}

async function updateStock() {
    const variationId = document.getElementById('edit_variation_id').value;
    const newStock = document.getElementById('edit_new_stock').value;
    const messageDiv = document.getElementById('edit-stock-message');

    try {
        const response = await fetch('api/update_stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                variation_id: variationId,
                stock: newStock
            })
        });

        const result = await response.json();

        if (result.success) {
            messageDiv.innerHTML = `
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    ${result.message}
                </div>
            `;
            setTimeout(() => {
                closeModal('editStockModal');
                loadInventory();
                location.reload();
            }, 1500);
        } else {
            messageDiv.innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    ${result.message}
                </div>
            `;
        }
    } catch (error) {
        messageDiv.innerHTML = `
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                An error occurred. Please try again.
            </div>
        `;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.getElementById('edit-stock-message').innerHTML = '';
}

async function deleteProduct(productId) {
    showConfirm(
        'Are you sure you want to delete this product? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch('api/delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Product deleted successfully', 'success', 'Deleted');
                    loadDashboard();
                    loadProducts();
                    loadInventory();
                } else {
                    showError(result.message, 'Delete Failed');
                }
            } catch (error) {
                showError('An error occurred. Please try again.', 'Error');
            }
        },
        null,
        'Delete Product'
    );
}

let editVariationCounter = 0;

async function editProduct(productId) {
    try {
        const response = await fetch(`api/get_product.php?id=${productId}`);
        const result = await response.json();

        if (result.success) {
            const product = result.product;

            document.getElementById('edit_product_id').value = product.ProductID;
            document.getElementById('edit_brand').value = product.Brand;
            document.getElementById('edit_model').value = product.Model;
            document.getElementById('edit_category').value = product.Category;
            document.getElementById('edit_description').value = product.Description;

            const container = document.getElementById('editVariationsContainer');
            container.innerHTML = '';
            editVariationCounter = 0;

            product.variations.forEach(variation => {
                addEditVariation(variation);
            });

            document.getElementById('editProductModal').classList.add('active');
        } else {
            showError(result.message, 'Load Failed');
        }
    } catch (error) {
        showError('Failed to load product details', 'Error');
    }
}

function addEditVariation(variationData = null) {
    const container = document.getElementById('editVariationsContainer');
    const index = editVariationCounter++;

    const variationDiv = document.createElement('div');
    variationDiv.className = 'variation-item';
    variationDiv.id = `edit-variation-${index}`;

    const variationId = variationData ? variationData.VariationID : '';
    const layout = variationData ? variationData.Layout : '';
    const switchType = variationData ? variationData.SwitchType : '';
    const color = variationData ? variationData.Color : '';
    const price = variationData ? variationData.Price : '';
    const stock = variationData ? variationData.StockQuantity : '';

    const isCustomLayout = layout && !['100', '96', '80', '75', '65', '60', '40'].includes(layout);
    const layoutValue = isCustomLayout ? 'custom' : layout;

    variationDiv.innerHTML = `
        <input type="hidden" name="variations[${index}][variation_id]" value="${variationId}">
        <div class="form-group">
            <label class="form-label">Layout <span class="required">*</span></label>
            <select name="variations[${index}][layout]" class="form-select layout-select" data-index="${index}" required>
                <option value="">Select layout</option>
                <option value="100" ${layoutValue === '100' ? 'selected' : ''}>100% (Full Size)</option>
                <option value="96" ${layoutValue === '96' ? 'selected' : ''}>96%</option>
                <option value="80" ${layoutValue === '80' ? 'selected' : ''}>80% (TKL)</option>
                <option value="75" ${layoutValue === '75' ? 'selected' : ''}>75%</option>
                <option value="65" ${layoutValue === '65' ? 'selected' : ''}>65%</option>
                <option value="60" ${layoutValue === '60' ? 'selected' : ''}>60%</option>
                <option value="40" ${layoutValue === '40' ? 'selected' : ''}>40%</option>
                <option value="custom" ${isCustomLayout ? 'selected' : ''}>Custom (Type below)</option>
            </select>
        </div>
        <div class="form-group custom-layout-group" id="editCustomLayout${index}" style="display: ${isCustomLayout ? 'block' : 'none'};">
            <label class="form-label">Custom Layout</label>
            <input type="text" name="variations[${index}][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%" value="${isCustomLayout ? layout : ''}">
        </div>
        <div class="form-group">
            <label class="form-label">Switch Type</label>
            <input type="text" name="variations[${index}][switch]" class="form-input" placeholder="e.g., Reaper SW" value="${switchType}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Color</label>
            <input type="text" name="variations[${index}][color]" class="form-input" placeholder="e.g., Blue, Black" value="${color}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Price (₱)</label>
            <input type="number" name="variations[${index}][price]" class="form-input" placeholder="0.00" step="0.01" min="0" value="${price}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="variations[${index}][stock]" class="form-input" placeholder="0" min="0" value="${stock}" required>
        </div>
        ${container.children.length > 0 || variationData ? `<button type="button" class="btn-remove" onclick="removeEditVariation(${index})"><i class="fas fa-trash"></i></button>` : ''}
    `;

    container.appendChild(variationDiv);
}

function removeEditVariation(index) {
    const variation = document.getElementById(`edit-variation-${index}`);
    if (variation) {
        variation.remove();
    }
}

async function saveProductEdit() {
    const form = document.getElementById('editProductForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('edit-product-message');

    const layoutSelects = document.querySelectorAll('#editVariationsContainer .layout-select');
    layoutSelects.forEach((select, index) => {
        if (select.value === 'custom') {
            const customLayoutInput = document.querySelector(`input[name="variations[${select.dataset.index}][custom_layout]"]`);
            if (customLayoutInput && customLayoutInput.value) {
                formData.set(`variations[${select.dataset.index}][layout]`, customLayoutInput.value);
            }
        }
    });

    try {
        const response = await fetch('api/update_product.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            messageDiv.innerHTML = `
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    ${result.message}
                </div>
            `;

            setTimeout(() => {
                closeModal('editProductModal');
                loadDashboard();
                loadProducts();
                loadInventory();
            }, 1500);
        } else {
            messageDiv.innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    ${result.message}
                </div>
            `;
        }
    } catch (error) {
        messageDiv.innerHTML = `
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                Failed to update product. Please try again.
            </div>
        `;
    }
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}


let deliveryRiders = [];

async function loadOrders(status = 'all') {
    try {
        const response = await fetch(`api/get_orders.php?status=${status}`);
        const result = await response.json();

        if (result.success) {
            document.getElementById('pendingOrdersCount').textContent = result.stats.pending || 0;
            document.getElementById('assignedOrdersCount').textContent = result.stats.assigned || 0;
            document.getElementById('shippedOrdersCount').textContent = result.stats.shipped || 0;
            document.getElementById('deliveredOrdersCount').textContent = result.stats.delivered || 0;

            deliveryRiders = result.riders;

            const tbody = document.getElementById('ordersTableBody');
            if (result.orders && result.orders.length > 0) {
                tbody.innerHTML = result.orders.map(order => {
                    const statusClass = getStatusClass(order.DeliveryStatus);
                    const paymentClass = order.PaymentStatus === 'Paid' ? 'success' :
                        order.PaymentStatus === 'Failed' ? 'error' : 'warning';

                    return `
                        <tr>
                            <td>#${String(order.OrderID).padStart(6, '0')}</td>
                            <td>
                                <div>
                                    <strong>${order.CustomerName}</strong><br>
                                    <small style="color: #666;">${order.CustomerEmail}</small>
                                </div>
                            </td>
                            <td>${order.ItemCount} item(s)</td>
                            <td>₱${parseFloat(order.TotalAmount).toFixed(2)}</td>
                            <td><span class="badge ${paymentClass}">${order.PaymentStatus}</span></td>
                            <td><span class="badge ${statusClass}">${order.DeliveryStatus}</span></td>
                            <td>
                                ${order.DeliveryPersonName ?
                            `<strong>${order.DeliveryPersonName}</strong>` :
                            '<span style="color: #999;">Not Assigned</span>'}
                            </td>
                            <td>${new Date(order.PlaceOrdered).toLocaleDateString()}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-view" onclick="viewOrderDetails(${order.OrderID})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${order.DeliveryStatus === 'Processing' ? `
                                        <button class="btn-icon btn-ready" onclick="setOrderReadyToDeliver(${order.OrderID}, ${order.TrackingID})" title="Mark Ready to Deliver">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    ` : ''}
                                    ${!order.DeliveryPersonID && order.DeliveryStatus !== 'Pending' && order.DeliveryStatus !== 'Cancelled' ? `
                                        <button class="btn-icon btn-assign" onclick="openAssignDelivery(${order.OrderID}, ${order.TrackingID})" title="Assign Rider">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center">No orders found</td></tr>';
            }
        }
    } catch (error) {
        console.error('Failed to load orders:', error);
        document.getElementById('ordersTableBody').innerHTML =
            '<tr><td colspan="9" class="text-center" style="color: red;">Failed to load orders</td></tr>';
    }
}

function getStatusClass(status) {
    const statusMap = {
        'Pending': 'warning',
        'Processing': 'info',
        'Ready to Deliver': 'success',
        'Assigned': 'primary',
        'Shipped': 'primary',
        'In Transit': 'primary',
        'Delivered': 'success',
        'Cancelled': 'error'
    };
    return statusMap[status] || 'default';
}

function filterOrders() {
    const status = document.getElementById('orderStatusFilter').value;
    loadOrders(status);
}

function refreshOrders() {
    const status = document.getElementById('orderStatusFilter').value;
    loadOrders(status);
}

async function setOrderReadyToDeliver(orderId, trackingId) {
    showCustomConfirm(
        'Mark this order as <span style="color:#4CAF50;font-weight:600;">Ready to Deliver</span>?<br><small>The order will be ready for pickup by delivery rider.</small>',
        async () => {
            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('tracking_id', trackingId);
                formData.append('delivery_status', 'Ready to Deliver');
                formData.append('payment_status', 'Pending');
                const response = await fetch('api/update_order_status.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Order marked as Ready to Deliver', 'success', 'Status Updated');
                    refreshOrders();
                } else {
                    showError(result.message || 'Failed to update order status', 'Update Failed');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while updating the order', 'Error');
            }
        }
    );
}

async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const modalBody = document.getElementById('orderDetailsBody');
    const modalFooter = document.getElementById('orderDetailsFooter');

    modal.style.display = 'flex';
    modalBody.innerHTML = '<div class="loading text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modalFooter.style.display = 'none';

    try {
        const response = await fetch(`api/get_order_details.php?order_id=${orderId}`);
        const result = await response.json();

        if (result.success) {
            const order = result.order;
            const items = result.items;

            const itemsHtml = items.map(item => `
                <div class="order-item-row">
                    <img src="/${item.ImagePath || 'products/placeholder.png'}" 
                         alt="${item.Brand} ${item.Model}" 
                         class="order-item-image"
                         onerror="this.src='/products/placeholder.png'">
                    <div class="order-item-info">
                        <div class="order-item-name">${item.Brand} ${item.Model}</div>
                        <div class="order-item-specs">
                            ${item.Layout}% • ${item.SwitchType} • ${item.Color} • Qty: ${item.Quantity}
                        </div>
                    </div>
                    <div class="order-item-price">₱${parseFloat(item.SubTotal).toFixed(2)}</div>
                </div>
            `).join('');

            modalBody.innerHTML = `
                <div class="order-details-grid">
                    <div class="detail-box">
                        <label>Order Number</label>
                        <div class="value">#${String(order.OrderID).padStart(6, '0')}</div>
                    </div>
                    <div class="detail-box">
                        <label>Order Date</label>
                        <div class="value">${new Date(order.PlaceOrdered).toLocaleDateString()}</div>
                    </div>
                    <div class="detail-box">
                        <label>Customer</label>
                        <div class="value">${order.CustomerName}</div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Status</label>
                        <div class="value">
                            <span class="badge ${getStatusClass(order.DeliveryStatus)}">${order.DeliveryStatus}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Payment Status</label>
                        <div class="value">
                            <span class="badge ${order.PaymentStatus === 'Paid' ? 'success' : 'warning'}">${order.PaymentStatus}</span>
                        </div>
                    </div>
                    <div class="detail-box">
                        <label>Delivery Rider</label>
                        <div class="value">${order.DeliveryPersonName || 'Not Assigned'}</div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-map-marker-alt"></i> Delivery Address</h4>
                    <div class="detail-box">
                        <div class="value">${order.CustomerAddress}</div>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-box"></i> Order Items</h4>
                    <div class="order-items-list">
                        ${itemsHtml}
                    </div>
                </div>
                <div class="order-total">
                    <span>Total Amount</span>
                    <span>₱${parseFloat(order.TotalAmount).toFixed(2)}</span>
                </div>
            `;

            if (order.DeliveryStatus === 'Pending') {
                modalFooter.style.display = 'flex';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" onclick="closeOrderDetailsModal()">Close</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-danger" onclick="handleOrderAction(${order.OrderID}, ${order.TrackingID}, 'cancel')">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                        <button type="button" class="btn btn-success" onclick="handleOrderAction(${order.OrderID}, ${order.TrackingID}, 'approve')">
                            <i class="fas fa-check"></i> Approve Order
                        </button>
                    </div>
                `;
            } else {
                modalFooter.style.display = 'flex';
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" onclick="closeOrderDetailsModal()">Close</button>
                `;
            }
        } else {
            modalBody.innerHTML = `<div class="alert error">${result.message}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        modalBody.innerHTML = '<div class="alert error">Failed to load order details</div>';
    }
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
    document.getElementById('orderDetailsFooter').style.display = 'none';
}

async function handleOrderAction(orderId, trackingId, action) {
    const actionText = action === 'approve' ? 'approve' : 'cancel';
    const confirmMessage = action === 'approve'
        ? 'Are you sure you want to approve this order? The order status will be set to <span style="color:#2196F3;font-weight:600;">Processing</span>.'
        : 'Are you sure you want to cancel this order? Stock quantities will be restored.';

    showCustomConfirm(confirmMessage, async () => {
        const modalFooter = document.getElementById('orderDetailsFooter');
        const originalFooterContent = modalFooter.innerHTML;
        modalFooter.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Processing...</div>';

        try {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('tracking_id', trackingId);
            formData.append('action', action);

            const response = await fetch('api/approve_cancel_order.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success', action === 'approve' ? 'Approved' : 'Cancelled');
                closeOrderDetailsModal();
                refreshOrders();
            } else {
                showError(result.message || `Failed to ${actionText} order`, 'Action Failed');
                modalFooter.innerHTML = originalFooterContent;
            }
        } catch (error) {
            console.error('Error:', error);
            showError(`An error occurred while trying to ${actionText} the order`, 'Error');
            modalFooter.innerHTML = originalFooterContent;
        }
    });
}

function openAssignDelivery(orderId, trackingId) {
    document.getElementById('assignOrderId').value = orderId;
    document.getElementById('assignTrackingId').value = trackingId;

    const select = document.getElementById('deliveryRider');
    select.innerHTML = '<option value="">-- Select Rider --</option>' +
        deliveryRiders.map(rider =>
            `<option value="${rider.ID}">${rider.FullName} - ${rider.Contact}</option>`
        ).join('');

    document.getElementById('assignDeliveryModal').style.display = 'flex';
}

function closeAssignDeliveryModal() {
    document.getElementById('assignDeliveryModal').style.display = 'none';
    document.getElementById('assignDeliveryForm').reset();
}

async function submitAssignDelivery(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    try {
        const response = await fetch('api/assign_delivery.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeAssignDeliveryModal();
            refreshOrders();
        } else {
            console.error('Error:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

window.addEventListener('click', function (event) {
    if (event.target.id === 'orderDetailsModal') {
        closeOrderDetailsModal();
    }
    if (event.target.id === 'assignDeliveryModal') {
        closeAssignDeliveryModal();
    }
});

