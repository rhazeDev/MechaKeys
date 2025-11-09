let currentOrderId = null;
let currentTrackingId = null;
let locationWatchId = null;
let lastLocationUpdate = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDeliveries();
    loadProfileInfo();
    setupEventListeners();

    tryAutomaticLocationTracking();
});

function setupEventListeners() {
    document.getElementById('setLocationBtn').addEventListener('click', openSetLocationModal);

    document.getElementById('refreshBtn').addEventListener('click', () => {
        document.getElementById('refreshBtn').classList.add('spinning');
        loadDeliveries();
        setTimeout(() => {
            document.getElementById('refreshBtn').classList.remove('spinning');
        }, 500);
    });

    document.getElementById('profileBtn').addEventListener('click', openProfileModal);

    document.getElementById('statusFilter').addEventListener('change', () => {
        loadDeliveries();
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    const searchLocationBtn = document.getElementById('searchLocationBtn');
    const setManualLocationBtn = document.getElementById('setManualLocationBtn');

    if (searchLocationBtn) {
        searchLocationBtn.addEventListener('click', searchLocation);
    }

    if (setManualLocationBtn) {
        setManualLocationBtn.addEventListener('click', setManualLocation);
    }

    const locationSearchInput = document.getElementById('locationSearchInput');
    if (locationSearchInput) {
        locationSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchLocation();
            }
        });
    }
}

async function loadDeliveries() {
    try {
        const statusFilter = document.getElementById('statusFilter').value;
        const url = statusFilter
            ? `api/get_deliveries.php?status=${statusFilter}`
            : 'api/get_deliveries.php';

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            document.getElementById('pendingCount').textContent = result.stats.Assigned || 0;
            document.getElementById('pickedCount').textContent = result.stats.Picked || 0;
            document.getElementById('transitCount').textContent = result.stats['In Transit'] || 0;
            document.getElementById('deliveredCount').textContent = result.stats.Delivered || 0;

            renderDeliveries(result.deliveries);
        } else {
            showError('Failed to load deliveries');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while loading deliveries');
    }
}

function renderDeliveries(deliveries) {
    const container = document.getElementById('deliveriesList');

    if (deliveries.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No deliveries found</p></div>';
        return;
    }

    container.innerHTML = deliveries.map(delivery => {
        const statusClass = getStatusClass(delivery.DeliveryStatus);
        const statusIcon = getStatusIcon(delivery.DeliveryStatus);
        const hasLocation = delivery.CustomerLocation && delivery.CustomerLocation.trim() !== '';
        const isDelivered = delivery.DeliveryStatus === 'Delivered';
        const isInTransit = delivery.DeliveryStatus === 'In Transit';

        const showButtons = !isDelivered;
        const showLocationBtn = hasLocation && isInTransit && !isDelivered;

        return `
            <div class="delivery-card" onclick="openOrderModal(${delivery.OrderID})">
                <div class="delivery-header">
                    <div class="delivery-order-info">
                        <h3 class="order-number">#${String(delivery.OrderID).padStart(6, '0')}</h3>
                        <p class="customer-name">${delivery.CustomerName}</p>
                    </div>
                    <span class="status-badge ${statusClass}">
                        <i class="${statusIcon}"></i>
                        ${delivery.DeliveryStatus}
                    </span>
                </div>

                <div class="delivery-body">
                    <div class="info-row">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${delivery.CustomerAddress}</span>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-phone"></i>
                        <span>${delivery.CustomerPhone}</span>
                    </div>
                    <div class="info-row">
                        <i class="fas fa-box"></i>
                        <span>${delivery.ItemCount} item(s) - ₱${parseFloat(delivery.TotalAmount).toFixed(2)}</span>
                    </div>
                </div>

                ${showButtons ? `
                    <div class="delivery-footer">
                        ${showLocationBtn ? `
                            <button class="btn btn-success btn-sm" onclick="viewLocationMap(event, '${delivery.CustomerLocation}', '${delivery.CustomerName}', '${delivery.CustomerAddress}')">
                                <i class="fas fa-map-marked-alt"></i> View Location
                            </button>
                        ` : ''}
                        <button class="btn btn-primary btn-sm" onclick="openStatusModal(event, ${delivery.OrderID}, ${delivery.TrackingID}, '${delivery.DeliveryStatus}')">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

function getStatusClass(status) {
    const statusMap = {
        'Assigned': 'assigned',
        'Picked': 'picked',
        'In Transit': 'in-transit',
        'Delivered': 'delivered'
    };
    return statusMap[status] || 'default';
}

function getStatusIcon(status) {
    const iconMap = {
        'Assigned': 'fas fa-clipboard-check',
        'Picked': 'fas fa-box',
        'In Transit': 'fas fa-truck',
        'Delivered': 'fas fa-check-circle'
    };
    return iconMap[status] || 'fas fa-info-circle';
}

async function openOrderModal(orderId) {
    const modal = document.getElementById('orderModal');
    const detailsDiv = document.getElementById('orderDetails');

    detailsDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.classList.add('active');

    try {
        const response = await fetch(`api/get_order_details.php?order_id=${orderId}`);
        const result = await response.json();

        if (result.success) {
            const order = result.order;
            const items = result.items;
            const tracking = result.tracking;

            const itemsHtml = items.map(item => `
                <div class="order-item">
                    <div class="item-image">
                        ${item.ImagePath ?
                    `<img src="/${item.ImagePath}" alt="${item.Brand}">` :
                    '<div class="placeholder"><i class="fas fa-keyboard"></i></div>'
                }
                    </div>
                    <div class="item-info">
                        <h4>${item.Brand} ${item.Model}</h4>
                        <p class="item-specs">${item.Layout}% • ${item.SwitchType} • ${item.Color}</p>
                        <p class="item-qty">Qty: ${item.Quantity}</p>
                    </div>
                    <div class="item-price">₱${parseFloat(item.SubTotal).toFixed(2)}</div>
                </div>
            `).join('');

            detailsDiv.innerHTML = `
                <div class="order-detail-container">
                    <div class="detail-section">
                        <h3>Customer Information</h3>
                        <div class="detail-row">
                            <span class="label">Name:</span>
                            <span class="value">${order.FullName}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Email:</span>
                            <span class="value">${order.Email}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Phone:</span>
                            <span class="value">${order.Contact}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Address:</span>
                            <span class="value">${order.Address}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Delivery Items</h3>
                        <div class="items-list">
                            ${itemsHtml}
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Order Summary</h3>
                        <div class="detail-row">
                            <span class="label">Order Date:</span>
                            <span class="value">${new Date(order.PlaceOrdered).toLocaleDateString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Total Amount:</span>
                            <span class="value total">₱${parseFloat(order.TotalAmount).toFixed(2)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Current Status:</span>
                            <span class="value"><span class="badge ${getStatusClass(tracking.DeliveryStatus)}">${tracking.DeliveryStatus === 'Ready to Deliver' ? 'Assigned' : tracking.DeliveryStatus}</span></span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            detailsDiv.innerHTML = '<div class="error-message">Failed to load order details</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        detailsDiv.innerHTML = '<div class="error-message">An error occurred</div>';
    }
}

function openStatusModal(event, orderId, trackingId, currentStatus) {
    event.stopPropagation();
    currentOrderId = orderId;
    currentTrackingId = trackingId;

    const modal = document.getElementById('statusModal');
    modal.classList.add('active');

    const options = document.querySelectorAll('.status-option');
    options.forEach(option => {
        const optionStatus = option.dataset.status;
        option.classList.remove('disabled');
        option.style.opacity = '1';
        option.style.pointerEvents = 'auto';
        option.style.cursor = 'pointer';

        if (currentStatus === 'In Transit' && optionStatus === 'Picked') {
            option.style.opacity = '0.5';
            option.style.pointerEvents = 'none';
            option.style.cursor = 'not-allowed';
        }

        if (currentStatus === 'Delivered') {
            option.style.opacity = '0.5';
            option.style.pointerEvents = 'none';
            option.style.cursor = 'not-allowed';
        }
    });
}

async function updateDeliveryStatus(newStatus) {
    if (!currentOrderId || !currentTrackingId) {
        showError('Invalid order or tracking information');
        return;
    }

    if (newStatus === 'Delivered') {
        closeModal('statusModal');
        openProofOfDeliveryModal();
        return;
    }

    try {
        const formData = new FormData();
        formData.append('order_id', currentOrderId);
        formData.append('tracking_id', currentTrackingId);
        formData.append('status', newStatus);

        const response = await fetch('api/update_delivery_status.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeModal('statusModal');
            closeModal('orderModal');
            showSuccess('Delivery status updated successfully');
            loadDeliveries();
        } else {
            showError(result.message || 'Failed to update delivery status');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('An error occurred while updating the status');
    }
}

function loadProfileInfo() {
    fetch('api/get_profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const profile = data.profile;

                const profileName = document.getElementById('profileName');
                const profileEmail = document.getElementById('profileEmail');
                const profilePhone = document.getElementById('profilePhone');
                const profileAddress = document.getElementById('profileAddress');

                if (profileName) {
                    profileName.textContent = profile.full_name;
                }
                if (profileEmail) {
                    profileEmail.textContent = profile.email;
                }
                if (profilePhone) {
                    profilePhone.textContent = profile.contact;
                }
                if (profileAddress) {
                    profileAddress.textContent = profile.address;
                }
            } else {
                console.error('Failed to load profile:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading profile:', error);
        });
}

function openProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.classList.add('active');
}

function logoutDelivery() {
    window.location.href = '../logout.php';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        if (modalId === 'proofOfDeliveryModal') {
            currentOrderId = null;
            currentTrackingId = null;
        }
        if (modalId === 'locationModal') {
            if (window.locationWatchId) {
                navigator.geolocation.clearWatch(window.locationWatchId);
                window.locationWatchId = null;
            }
            if (window.deliveryMap) {
                window.deliveryMap.remove();
                window.deliveryMap = null;
            }
        }
    }
}

function viewLocationMap(event, customerLocation, customerName, customerAddress) {
    event.stopPropagation();

    if (!customerLocation || customerLocation.trim() === '') {
        showError('Customer location not available');
        return;
    }

    const coords = customerLocation.split(',');
    if (coords.length !== 2) {
        showError('Invalid customer location format');
        return;
    }

    const customerLng = parseFloat(coords[0]);
    const customerLat = parseFloat(coords[1]);

    const modal = document.getElementById('locationModal');
    const modalBody = document.getElementById('locationMapContent');

    modalBody.innerHTML = `
        <div class="location-info-header">
            <div class="destination-info">
                <h3><i class="fas fa-map-marker-alt"></i> Destination</h3>
                <p class="customer-name">${customerName}</p>
                <p class="customer-address">${customerAddress}</p>
            </div>
            <div class="current-location-info">
                <h3><i class="fas fa-location-arrow"></i> Your Location</h3>
                <p id="driverLocationText">Getting your location...</p>
            </div>
        </div>
        <div class="map-loading">
            <i class="fas fa-spinner fa-spin"></i> Initializing map...
        </div>
        <div id="realTimeMap" style="width: 100%; height: 450px; display: none;"></div>
    `;

    modal.classList.add('active');

    mapboxgl.accessToken = 'pk.eyJ1IjoicmhhemUiLCJhIjoiY21memQycHB5MDFybzJrc2d2MXZiejJ6bCJ9.SO6KCjBMT50xiSTvRy0cIw';

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const driverLat = position.coords.latitude;
                const driverLng = position.coords.longitude;

                updateRiderLocation(driverLat, driverLng);

                modalBody.querySelector('.map-loading').style.display = 'none';
                modalBody.querySelector('#realTimeMap').style.display = 'block';

                updateDriverLocationText(driverLng, driverLat);

                const map = new mapboxgl.Map({
                    container: 'realTimeMap',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [driverLng, driverLat],
                    zoom: 12
                });

                map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                const driverMarker = new mapboxgl.Marker({
                    color: '#3b82f6',
                    scale: 1.2
                })
                    .setLngLat([driverLng, driverLat])
                    .setPopup(new mapboxgl.Popup().setHTML('<strong>Your Location</strong>'))
                    .addTo(map);

                const customerMarker = new mapboxgl.Marker({
                    color: '#ef4444',
                    scale: 1.2
                })
                    .setLngLat([customerLng, customerLat])
                    .setPopup(new mapboxgl.Popup().setHTML(`<strong>${customerName}</strong><br>${customerAddress}`))
                    .addTo(map);

                map.on('load', () => {
                    fetch(`https://api.mapbox.com/directions/v5/mapbox/driving/${driverLng},${driverLat};${customerLng},${customerLat}?geometries=geojson&access_token=${mapboxgl.accessToken}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.routes && data.routes.length > 0) {
                                const route = data.routes[0].geometry;
                                const distance = (data.routes[0].distance / 1000).toFixed(2); const duration = Math.round(data.routes[0].duration / 60);
                                map.addLayer({
                                    id: 'route',
                                    type: 'line',
                                    source: {
                                        type: 'geojson',
                                        data: {
                                            type: 'Feature',
                                            properties: {},
                                            geometry: route
                                        }
                                    },
                                    layout: {
                                        'line-join': 'round',
                                        'line-cap': 'round'
                                    },
                                    paint: {
                                        'line-color': '#3b82f6',
                                        'line-width': 4,
                                        'line-opacity': 0.8
                                    }
                                });

                                const infoDiv = modalBody.querySelector('.location-info-header');
                                const routeInfo = document.createElement('div');
                                routeInfo.className = 'route-info';
                                routeInfo.innerHTML = `
                                    <div class="route-stat">
                                        <i class="fas fa-road"></i>
                                        <span>${distance} km</span>
                                    </div>
                                    <div class="route-stat">
                                        <i class="fas fa-clock"></i>
                                        <span>${duration} min</span>
                                    </div>
                                    <a href="https://www.google.com/maps/dir/?api=1&origin=${driverLat},${driverLng}&destination=${customerLat},${customerLng}&travelmode=driving" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-directions"></i> Open in Google Maps
                                    </a>
                                `;
                                infoDiv.appendChild(routeInfo);

                                const bounds = new mapboxgl.LngLatBounds();
                                bounds.extend([driverLng, driverLat]);
                                bounds.extend([customerLng, customerLat]);
                                map.fitBounds(bounds, { padding: 50 });
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching route:', error);
                            const bounds = new mapboxgl.LngLatBounds();
                            bounds.extend([driverLng, driverLat]);
                            bounds.extend([customerLng, customerLat]);
                            map.fitBounds(bounds, { padding: 50 });
                        });
                });

                window.locationWatchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        const newDriverLat = pos.coords.latitude;
                        const newDriverLng = pos.coords.longitude;

                        driverMarker.setLngLat([newDriverLng, newDriverLat]);

                        updateDriverLocationText(newDriverLng, newDriverLat);

                        fetch(`https://api.mapbox.com/directions/v5/mapbox/driving/${newDriverLng},${newDriverLat};${customerLng},${customerLat}?geometries=geojson&access_token=${mapboxgl.accessToken}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.routes && data.routes.length > 0) {
                                    const route = data.routes[0].geometry;
                                    const source = map.getSource('route');
                                    if (source) {
                                        map.getSource('route').setData({
                                            type: 'Feature',
                                            properties: {},
                                            geometry: route
                                        });
                                    }
                                }
                            })
                            .catch(error => console.error('Error updating route:', error));
                    },
                    (error) => {
                        console.error('Error watching location:', error);
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 10000,
                        timeout: 5000
                    }
                );

                window.deliveryMap = map;
            },
            (error) => {
                closeModal('locationModal');

                console.log('Location error, opening manual location modal:', error);
                showError('Unable to get automatic location. Please set your location manually.');

                setTimeout(() => {
                    openSetLocationModal();
                }, 300);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        closeModal('locationModal');

        showError('Geolocation not supported. Please set your location manually.');
        setTimeout(() => {
            openSetLocationModal();
        }, 300);
    }
}

function updateDriverLocationText(lng, lat) {
    const textElement = document.getElementById('driverLocationText');
    if (textElement) {
        fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=pk.eyJ1IjoicmhhemUiLCJhIjoiY21memQycHB5MDFybzJrc2d2MXZiejJ6bCJ9.SO6KCjBMT50xiSTvRy0cIw`)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    textElement.textContent = data.features[0].place_name;
                } else {
                    textElement.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            })
            .catch(error => {
                textElement.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            });
    }
}

function getGeolocationErrorMessage(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            return "Location permission denied. Please enable location access in your browser settings.";
        case error.POSITION_UNAVAILABLE:
            return "Location information is unavailable.";
        case error.TIMEOUT:
            return "Location request timed out.";
        default:
            return "An unknown error occurred.";
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .btn-icon.spinning {
        animation: spin 0.5s linear;
    }
`;
document.head.appendChild(style);

function tryAutomaticLocationTracking() {
    if (!navigator.geolocation) {
        console.log('Geolocation not supported - use manual location button');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log('Automatic location obtained');
            updateRiderLocation(position.coords.latitude, position.coords.longitude);
            startContinuousTracking();
        },
        (error) => {
            console.log('Automatic location not available, use manual button');
        },
        {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        }
    );
}

function openSetLocationModal() {
    const modal = document.getElementById('setLocationModal');
    if (modal) {
        document.getElementById('locationSearchInput').value = '';
        document.getElementById('manualLatitude').value = '';
        document.getElementById('manualLongitude').value = '';
        document.getElementById('setManualLocationBtn').style.display = 'none';
        document.getElementById('selectedLocationPreview').style.display = 'none';

        modal.classList.add('active');
    }
}

async function searchLocation() {
    const searchInput = document.getElementById('locationSearchInput');
    const query = searchInput.value.trim();

    if (!query) {
        showError('Please enter a location to search');
        return;
    }

    showSuccess('Searching for location...');

    try {
        const accessToken = 'pk.eyJ1IjoicmhhemUiLCJhIjoiY21memQycHB5MDFybzJrc2d2MXZiejJ6bCJ9.SO6KCjBMT50xiSTvRy0cIw';
        const response = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${accessToken}&limit=1`);
        const data = await response.json();

        if (data.features && data.features.length > 0) {
            const coords = data.features[0].center; const placeName = data.features[0].place_name;

            document.getElementById('manualLongitude').value = coords[0].toFixed(6);
            document.getElementById('manualLatitude').value = coords[1].toFixed(6);

            document.getElementById('selectedLocationName').textContent = placeName;
            document.getElementById('selectedLocationPreview').style.display = 'block';
            document.getElementById('setManualLocationBtn').style.display = 'block';

            showSuccess(`Found: ${placeName}`);
        } else {
            showError('Location not found. Please try a different search term.');
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        showError('Failed to search location. Please try again.');
    }
}

function setManualLocation() {
    const lat = parseFloat(document.getElementById('manualLatitude').value);
    const lng = parseFloat(document.getElementById('manualLongitude').value);

    if (isNaN(lat) || isNaN(lng)) {
        showError('Please search for a location first');
        return;
    }

    if (lat < -90 || lat > 90) {
        showError('Invalid latitude value');
        return;
    }

    if (lng < -180 || lng > 180) {
        showError('Invalid longitude value');
        return;
    }

    updateRiderLocation(lat, lng);

    closeModal('setLocationModal');

    showSuccess('Location set successfully! Customers can now track your position.');
}

function showLocationBanner() {
}

function closeBanner() {
}

function requestLocationPermission() {
}

function startLocationTracking() {
    if (!navigator.geolocation) {
        showError('Geolocation is not supported by your device');
        showLocationBanner();
        return;
    }

    console.log('Starting location tracking...');

    showSuccess('Requesting location access...');

    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log('Location obtained:', position.coords);
            updateRiderLocation(position.coords.latitude, position.coords.longitude);
            showSuccess('Location tracking enabled! Your position is being shared.');

            startContinuousTracking();
        },
        (error) => {
            console.error('Location error:', error);
            handleLocationError(error);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

function startContinuousTracking() {
    locationWatchId = navigator.geolocation.watchPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            console.log('Location update:', lat, lng);

            const now = Date.now();
            if (!lastLocationUpdate ||
                now - lastLocationUpdate > 30000 ||
                hasLocationChangedSignificantly(lat, lng)) {
                updateRiderLocation(lat, lng);
                lastLocationUpdate = now;
            }
        },
        (error) => {
            console.error('Location watch error:', error);
        },
        {
            enableHighAccuracy: true,
            maximumAge: 5000,
            timeout: 10000
        }
    );
}

let lastLat = null;
let lastLng = null;

function hasLocationChangedSignificantly(lat, lng) {
    if (lastLat === null || lastLng === null) {
        lastLat = lat;
        lastLng = lng;
        return true;
    }

    const R = 6371e3; const φ1 = lastLat * Math.PI / 180;
    const φ2 = lat * Math.PI / 180;
    const Δφ = (lat - lastLat) * Math.PI / 180;
    const Δλ = (lng - lastLng) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        Math.cos(φ1) * Math.cos(φ2) *
        Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    if (distance > 10) {
        lastLat = lat;
        lastLng = lng;
        return true;
    }

    return false;
}

async function updateRiderLocation(latitude, longitude) {
    try {
        const formData = new FormData();
        formData.append('latitude', latitude);
        formData.append('longitude', longitude);

        const response = await fetch('api/update_rider_location.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            console.log('Location updated:', result.location);
        } else {
            console.error('Failed to update location:', result.message);
        }
    } catch (error) {
        console.error('Error updating location:', error);
    }
}

function handleLocationError(error) {
    let message = '';
    switch (error.code) {
        case error.PERMISSION_DENIED:
            message = "Location permission denied. Use the location button to set your position manually.";
            showError(message);
            break;
        case error.POSITION_UNAVAILABLE:
            message = "Location information is unavailable. Use the location button to set manually.";
            showError(message);
            break;
        case error.TIMEOUT:
            message = "Location request timed out. Use the location button to set manually.";
            showError(message);
            break;
        default:
            message = "Unable to get location automatically. Use the location button to set manually.";
            showError(message);
    }
}

window.addEventListener('beforeunload', () => {
    if (locationWatchId) {
        navigator.geolocation.clearWatch(locationWatchId);
    }
});

let videoStream = null;
let capturedImageBlob = null;

function openProofOfDeliveryModal() {
    const modal = document.getElementById('proofOfDeliveryModal');
    if (modal) {
        resetProofOfDeliveryModal();
        modal.classList.add('active');
        initializeCamera();
    }
}

function resetProofOfDeliveryModal() {
    document.getElementById('videoStream').style.display = 'none';
    document.getElementById('captureCanvas').style.display = 'none';
    document.getElementById('noCameraMessage').style.display = 'none';
    document.getElementById('capturedImagePreview').style.display = 'none';
    document.getElementById('captureBtnCamera').style.display = 'none';
    document.getElementById('retakeBtnCamera').style.display = 'none';

    capturedImageBlob = null;
}

async function initializeCamera() {
    try {
        const hasCamera = navigator.mediaDevices && navigator.mediaDevices.getUserMedia;

        if (!hasCamera) {
            document.getElementById('noCameraMessage').style.display = 'block';
            document.getElementById('captureBtnCamera').style.display = 'none';
            return;
        }

        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
            audio: false
        });

        const video = document.getElementById('videoStream');
        video.srcObject = videoStream;
        video.style.display = 'block';

        video.play();

        document.getElementById('noCameraMessage').style.display = 'none';
        document.getElementById('captureBtnCamera').style.display = 'inline-block';

        document.getElementById('captureBtnCamera').onclick = capturePhoto;
        document.getElementById('retakeBtnCamera').onclick = retakePhoto;

    } catch (error) {
        console.error('Camera error:', error);
        document.getElementById('noCameraMessage').style.display = 'block';
        document.getElementById('captureBtnCamera').style.display = 'none';

        if (error.name === 'NotAllowedError') {
            showError('Camera permission denied. Cannot proceed without camera.');
        } else if (error.name === 'NotFoundError') {
            showError('No camera found on this device.');
        } else {
            showError('Unable to access camera.');
        }
    }
}

function startCamera() {
    const video = document.getElementById('videoStream');
    if (videoStream) {
        video.play();
    }
}

function capturePhoto() {
    const video = document.getElementById('videoStream');
    const canvas = document.getElementById('captureCanvas');
    const ctx = canvas.getContext('2d');

    if (video.videoWidth === 0 || video.videoHeight === 0) {
        showError('Camera stream not ready. Please wait a moment.');
        console.error('Video dimensions not ready:', video.videoWidth, video.videoHeight);
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);

    ctx.drawImage(video, 0, 0);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                console.log('Location obtained:', lat, lng);
                drawLocationOnCanvas(canvas, lat, lng, () => {
                    saveCanvasAsBlob(canvas);
                });
            },
            (error) => {
                console.warn('Location error, proceeding without location:', error);
                saveCanvasAsBlob(canvas);
            }
        );
    } else {
        saveCanvasAsBlob(canvas);
    }
}

function drawLocationOnCanvas(canvas, latitude, longitude, callback) {
    const ctx = canvas.getContext('2d');
    const timestamp = new Date().toLocaleString();
    const locationText = `📍 ${latitude.toFixed(6)}, ${longitude.toFixed(6)} • ${timestamp}`;

    ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
    ctx.fillRect(0, canvas.height - 50, canvas.width, 50);

    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(locationText, 15, canvas.height - 20);

    if (callback) callback();
}

function saveCanvasAsBlob(canvas) {
    canvas.toBlob(blob => {
        if (!blob) {
            showError('Failed to create image from canvas');
            console.error('Canvas toBlob returned null');
            return;
        }

        console.log('Blob created:', {
            size: blob.size,
            type: blob.type
        });

        capturedImageBlob = blob;

        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('capturedImage').src = e.target.result;
            document.getElementById('capturedImagePreview').style.display = 'block';
            document.getElementById('videoStream').style.display = 'none';
            document.getElementById('captureBtnCamera').style.display = 'none';
            document.getElementById('retakeBtnCamera').style.display = 'inline-block';
            showSuccess('Photo captured with location! You can retake or submit.');
        };
        reader.onerror = (error) => {
            console.error('FileReader error:', error);
            showError('Failed to preview image');
        };
        reader.readAsDataURL(blob);
    }, 'image/jpeg', 0.95);
}

function retakePhoto() {
    const video = document.getElementById('videoStream');
    video.style.display = 'block';
    video.play(); document.getElementById('capturedImagePreview').style.display = 'none';
    document.getElementById('captureBtnCamera').style.display = 'inline-block';
    document.getElementById('retakeBtnCamera').style.display = 'none';
    capturedImageBlob = null;
}

function stopCamera() {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    document.getElementById('videoStream').style.display = 'none';
    document.getElementById('captureBtnCamera').style.display = 'none';
    document.getElementById('retakeBtnCamera').style.display = 'none';
}

async function submitProofOfDelivery() {
    if (!capturedImageBlob) {
        showError('Please capture a photo first');
        console.error('No image blob captured');
        return;
    }

    if (!currentOrderId || !currentTrackingId) {
        showError('Invalid order or tracking information');
        console.error('Missing order or tracking ID');
        return;
    }

    try {
        stopCamera();

        const submitBtn = document.getElementById('submitProofBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const formData = new FormData();
        formData.append('order_id', currentOrderId);
        formData.append('tracking_id', currentTrackingId);
        formData.append('status', 'Delivered');
        formData.append('proof_image', capturedImageBlob, 'proof_of_delivery.jpg');

        console.log('Submitting proof with:', {
            order_id: currentOrderId,
            tracking_id: currentTrackingId,
            image_size: capturedImageBlob.size,
            image_type: capturedImageBlob.type
        });

        const response = await fetch('api/update_delivery_status.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        console.log('API Response:', result);

        if (result.success) {
            capturedImageBlob = null;
            closeModal('proofOfDeliveryModal');
            showSuccess('Order marked as delivered with proof!');
            loadDeliveries();
        } else {
            showError(result.message || 'Failed to submit proof of delivery');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error submitting proof:', error);
        showError('An error occurred: ' + error.message);
        document.getElementById('submitProofBtn').disabled = false;
        document.getElementById('submitProofBtn').innerHTML = '<i class="fas fa-check"></i> Submit Proof';
    }
}