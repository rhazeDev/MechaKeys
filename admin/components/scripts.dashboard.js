async function loadDashboard() {
    try {
        const response = await fetch('api/get_dashboard.php', {
            credentials: 'same-origin'
        });
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

function renderOrderStatusChart(data) {
    const canvas = document.getElementById('orderStatusChart');
    if (!canvas) {
        console.error('Canvas element orderStatusChart not found');
        return;
    }

    canvas.width = 400;
    canvas.height = 300;

    const ctx = canvas.getContext('2d');

    if (window.orderStatusChart instanceof Chart) {
        window.orderStatusChart.destroy();
    }

    if (!data || data.length === 0) {
        window.orderStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
        return;
    }

    const labels = data.map(item => item.DeliveryStatus);
    const values = data.map(item => parseInt(item.count));

    window.orderStatusChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    if (window.revenueChart instanceof Chart) {
        window.revenueChart.destroy();
    }

    if (!data || data.length === 0) {
        window.revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'Revenue',
                    data: [0],
                    borderColor: '#f0f0f0',
                    backgroundColor: 'rgba(240, 240, 240, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        return;
    }

    const labels = data.map(item => new Date(item.date).toLocaleDateString());
    const values = data.map(item => parseFloat(item.revenue));

    window.revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: values,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4CAF50',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function renderProductCategoriesChart(data) {
    const ctx = document.getElementById('productCategoriesChart').getContext('2d');

    if (window.productCategoriesChart instanceof Chart) {
        window.productCategoriesChart.destroy();
    }

    if (!data || data.length === 0) {
        window.productCategoriesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data'],
                datasets: [{
                    label: 'Products',
                    data: [0],
                    backgroundColor: '#f0f0f0',
                    borderColor: '#e0e0e0',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        return;
    }

    const labels = data.map(item => item.Category.charAt(0).toUpperCase() + item.Category.slice(1));
    const values = data.map(item => parseInt(item.count));

    window.productCategoriesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Products',
                data: values,
                backgroundColor: '#2196F3',
                borderColor: '#1976D2',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function renderStockChart(data) {
    const ctx = document.getElementById('stockChart').getContext('2d');

    if (window.stockChart instanceof Chart) {
        window.stockChart.destroy();
    }

    if (!data || (!data.good_stock && !data.low_stock && !data.out_of_stock)) {
        window.stockChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['No Data'],
                datasets: [{
                    data: [1],
                    backgroundColor: ['#f0f0f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
        return;
    }

    const labels = ['Good Stock', 'Low Stock', 'Out of Stock'];
    const values = [
        parseInt(data.good_stock || 0),
        parseInt(data.low_stock || 0),
        parseInt(data.out_of_stock || 0)
    ];

    window.stockChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#4CAF50',
                    '#FF9800',
                    '#F44336'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

async function loadDashboardCharts() {
    try {
        const response = await fetch('api/get_dashboard.php', {
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success && result.charts) {
            renderOrderStatusChart(result.charts.order_status || []);
            renderRevenueChart(result.charts.revenue || []);
            renderProductCategoriesChart(result.charts.categories || []);
            renderStockChart(result.charts.stock_levels || {});
        } else {
            renderOrderStatusChart([
                { DeliveryStatus: 'Processing', count: 5 },
                { DeliveryStatus: 'In Transit', count: 3 },
                { DeliveryStatus: 'Delivered', count: 12 }
            ]);
            renderRevenueChart([
                { date: '2025-11-10', revenue: 1500.00 },
                { date: '2025-11-11', revenue: 2200.50 },
                { date: '2025-11-12', revenue: 1800.75 }
            ]);
            renderProductCategoriesChart([
                { Category: 'keyboard', count: 8 },
                { Category: 'keycap', count: 5 },
                { Category: 'switch', count: 3 }
            ]);
            renderStockChart({
                good_stock: 25,
                low_stock: 3,
                out_of_stock: 1
            });
        }
    } catch (error) {
        renderOrderStatusChart([
            { DeliveryStatus: 'Processing', count: 5 },
            { DeliveryStatus: 'In Transit', count: 3 },
            { DeliveryStatus: 'Delivered', count: 12 }
        ]);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    loadDashboard();
    loadDashboardCharts();
});
