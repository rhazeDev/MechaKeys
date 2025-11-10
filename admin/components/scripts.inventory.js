async function loadInventory() {
    const container = document.getElementById('inventory-list');
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
            }),
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
