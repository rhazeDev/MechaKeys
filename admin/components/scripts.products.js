let editVariationCounter = 0;

async function loadProducts() {
    const container = document.getElementById('products-list');
    container.innerHTML = '<div class="text-center">Loading...</div>';

    try {
        const response = await fetch('api/get_products.php', {
            credentials: 'same-origin'
        });
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
                    body: JSON.stringify({ product_id: productId }),
                    credentials: 'same-origin'
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

async function editProduct(productId) {
    try {
        const response = await fetch(`api/get_product.php?id=${productId}`, {
            credentials: 'same-origin'
        });
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
            body: formData,
            credentials: 'same-origin'
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
