async function loadInventory() {
    const container = document.getElementById('inventory-list');
    if (!container) {
        console.error('loadInventory: #inventory-list container not found');
        return;
    }
    container.innerHTML = '<div class="text-center">Loading...</div>';

    try {
        const response = await fetch('api/get_inventory.php', {
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
                                    <td>${escapeHtml(item.ProductBrand)} ${escapeHtml(item.ProductModel)}</td>
                                    <td>${escapeHtml(formatLayout(item))}</td>
                                    <td>${escapeHtml(formatField(item, 'SwitchType'))}</td>
                                    <td>${escapeHtml(formatField(item, 'Color'))}</td>
                                    <td>₱${parseFloat(item.Price).toFixed(2)}</td>
                                        <td>${escapeHtml(item.StockQuantity)} units</td>
                                    <td>
                                        ${item.StockQuantity == 0 ?
                    '<span class="badge error">Out of Stock</span>' :
                    item.StockQuantity < 10 ?
                        '<span class="badge warning">Low Stock</span>' :
                        '<span class="badge success">In Stock</span>'
                }
                                    </td>
                                    <td>
                                        <button class="action-btn edit" onclick="openEditStock(${item.VariationID}, '${escapeHtml(item.ProductBrand + ' ' + item.ProductModel)}', '${escapeHtml(buildVariationDetails(item))}', ${item.StockQuantity})">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            container.innerHTML = '<div class="alert error">Failed to load inventory</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="alert error">Failed to load inventory</div>';
    }
}

function formatLayout(item) {
    const layout = item.Layout;
    const category = (item.Category || '').toString().toLowerCase();
    const blankCategories = ['switches', 'keycaps', 'accessories'];

    if (layout === null || layout === undefined || String(layout).trim() === '') return '';

    if (String(layout).toUpperCase() === 'N/A' && blankCategories.includes(category)) return '';

    const numeric = parseFloat(layout);
    if (!isNaN(numeric)) {
        if (numeric >= 100) return String(layout);
        return `${numeric}%`;
    }
    return String(layout);
}

function formatField(item, field) {
    const val = item[field];
    const category = (item.Category || '').toString().toLowerCase();
    const blankCategories = ['switches', 'keycaps', 'accessories'];

    if (val === null || val === undefined || String(val).trim() === '') return '';
    if (String(val).toUpperCase() === 'N/A' && blankCategories.includes(category)) return '';
    return String(val);
}

function buildVariationDetails(item) {
    const parts = [];
    const layout = formatLayout(item);
    const sw = formatField(item, 'SwitchType');
    const color = formatField(item, 'Color');
    if (layout) parts.push(layout);
    if (sw) parts.push(sw);
    if (color) parts.push(color);
    return parts.join(' - ');
}

function openEditStock(variationId, productName, variationDetails, currentStock) {
    document.getElementById('edit_variation_id').value = variationId;
    document.getElementById('edit_product_name').value = productName;
    document.getElementById('edit_variation_details').value = variationDetails;
    document.getElementById('edit_new_stock').value = currentStock;
    const stockModal = document.getElementById('editStockModal');
    if (stockModal) stockModal.style.removeProperty('display');
    stockModal.classList.add('active');
}

async function updateStock() {
    const variationId = document.getElementById('edit_variation_id').value;
    const newStock = document.getElementById('edit_new_stock').value;
    const messageDiv = document.getElementById('edit-stock-message');

    const updateBtn = document.getElementById('updateStockBtn');
    if (updateBtn) {
        updateBtn.classList.add('loading');
        updateBtn.disabled = true;
    }

    try {
        const response = await fetch('api/update_stock.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                variation_id: variationId,
                stock: newStock
            }),
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            showSuccess(result.message, 'Stock Updated', 2000);
            setTimeout(() => {
                closeModal('editStockModal');
                loadInventory();
            }, 800);
        } else {
            showError(result.message || 'An error occurred while updating stock', 'Update Failed', 3000);
            const sectionEl = document.getElementById('inventory');
        }
    } catch (error) {
        showError('An error occurred. Please try again.', 'Error', 3000);
    }
    finally {
        if (updateBtn) {
            updateBtn.classList.remove('loading');
            updateBtn.disabled = false;
        }
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function filterInventory() {
    const searchInput = document.getElementById('inventorySearch');
    if (!searchInput) return;

    const query = searchInput.value.toLowerCase().trim();
    const table = document.querySelector('#inventory-list .data-table');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const product = row.cells[0]?.textContent?.toLowerCase() || '';
        const layout = row.cells[1]?.textContent?.toLowerCase() || '';
        const switchType = row.cells[2]?.textContent?.toLowerCase() || '';
        const color = row.cells[3]?.textContent?.toLowerCase() || '';
        const price = row.cells[4]?.textContent?.toLowerCase() || '';
        const stock = row.cells[5]?.textContent?.toLowerCase() || '';
        const status = row.cells[6]?.textContent?.toLowerCase() || '';
        const matches = !query ||
            product.includes(query) ||
            layout.includes(query) ||
            switchType.includes(query) ||
            color.includes(query) ||
            price.includes(query) ||
            stock.includes(query) ||
            status.includes(query);

        row.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
    });

    const container = document.getElementById('inventory-list');
    const noResultsMsg = container?.querySelector('.no-results-message');
    if (visibleCount === 0 && query) {
        if (!noResultsMsg) {
            const msg = document.createElement('div');
            msg.className = 'no-results-message';
            msg.style.cssText = 'padding: 2rem; text-align: center; color: #999;';
            msg.innerHTML = '<i class="fas fa-search"></i> No inventory items match your search.';
            container?.appendChild(msg);
        }
    } else if (noResultsMsg) {
        noResultsMsg.remove();
    }
}

