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
    const div = document.createElement('div');
    div.className = 'variation-item';
    div.innerHTML = `
        <div class="form-group">
            <label class="form-label">Layout <span class="required">*</span></label>
            <select name="variations[${variationCount}][layout]" class="form-select layout-select" data-index="${variationCount}" required>
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
        <div class="form-group custom-layout-group" id="customLayout${variationCount}" style="display: none;">
            <label class="form-label">Custom Layout</label>
            <input type="text" name="variations[${variationCount}][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
        </div>
        <div class="form-group">
            <label class="form-label">Switch Type</label>
            <input type="text" name="variations[${variationCount}][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" required>
        </div>
        <div class="form-group">
            <label class="form-label">Color</label>
            <input type="text" name="variations[${variationCount}][color]" class="form-input" placeholder="e.g., Blue, Black" required>
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
        const response = await fetch('add_product.php', {
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
        const response = await fetch('get_dashboard.php');
        const result = await response.json();

        if (result.success) {
            const stats = result.stats;
            document.querySelector('.stat-card:nth-child(1) .stat-value').textContent = stats.total_products;
            document.querySelector('.stat-card:nth-child(2) .stat-value').textContent = stats.total_variations;
            document.querySelector('.stat-card:nth-child(3) .stat-value').textContent = stats.total_stock;
            document.querySelector('.stat-card:nth-child(4) .stat-value').textContent = stats.low_stock_count;

            const lowStockCard = document.querySelector('.stat-card:nth-child(4)');
            const lowStockChange = lowStockCard.querySelector('.stat-change');
            if (stats.low_stock_count > 0) {
                lowStockChange.className = 'stat-change negative';
                lowStockChange.innerHTML = `
                    <i class="fas fa-arrow-down"></i>
                    Products need restocking
                `;
            } else {
                lowStockChange.className = 'stat-change positive';
                lowStockChange.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    All stock levels good
                `;
            }

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
        const response = await fetch('get_products.php');
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
        const response = await fetch('get_inventory.php');
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
        const response = await fetch('update_stock.php', {
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
    if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            loadDashboard();
            loadProducts();
            loadInventory();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

let editVariationCounter = 0;

async function editProduct(productId) {
    try {
        const response = await fetch(`get_product.php?id=${productId}`);
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
            alert(result.message);
        }
    } catch (error) {
        alert('Failed to load product details');
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
        const response = await fetch('update_product.php', {
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
