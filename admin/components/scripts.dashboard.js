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
