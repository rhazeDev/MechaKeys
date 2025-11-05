<?php
session_start();
include '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$rider_query = $conn->prepare("SELECT * FROM users WHERE ID = ?");
$rider_query->bind_param("i", $user_id);
$rider_query->execute();
$rider_result = $rider_query->get_result();
$rider_info = $rider_result->fetch_assoc();
$rider_query->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard - MechaKeys</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/delivery.css">
    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
</head>

<body>
    <div class="delivery-container">
        <!-- Mobile Header -->
        <header class="delivery-header">
            <div class="header-content">
                <h1 class="header-title">
                    Deliveries
                </h1>
                <div class="header-actions">
                    <button class="btn-icon" id="setLocationBtn" title="Set Location">
                        <i class="fas fa-map-marker-alt"></i>
                    </button>
                    <button class="btn-icon" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn-icon" id="profileBtn" title="Profile">
                        <i class="fas fa-user-circle"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Pending</span>
                    <span class="stat-value" id="pendingCount">0</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon picked">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Picked Up</span>
                    <span class="stat-value" id="pickedCount">0</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon transit">
                    <i class="fas fa-road"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">In Transit</span>
                    <span class="stat-value" id="transitCount">0</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon delivered">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Delivered</span>
                    <span class="stat-value" id="deliveredCount">0</span>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="filters-section">
            <div class="filter-group">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Deliveries</option>
                    <option value="Assigned">Assigned (Ready to Pick Up)</option>
                    <option value="Picked">Order Picked</option>
                    <option value="In Transit">On the Way</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
        </section>

        <!-- Deliveries List -->
        <section class="deliveries-section">
            <div id="deliveriesList" class="deliveries-grid">
                <!-- Deliveries will be loaded here -->
            </div>
        </section>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="modal-close" onclick="closeModal('orderModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="orderDetails" class="modal-body">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('orderModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Delivery Status</h2>
                <button class="modal-close" onclick="closeModal('statusModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="status-options">
                    <div class="status-option" data-status="Picked" onclick="updateDeliveryStatus('Picked')">
                        <i class="fas fa-box"></i>
                        <span>Order Picked</span>
                        <p class="status-desc">I've picked up the order</p>
                    </div>
                    <div class="status-option" data-status="In Transit" onclick="updateDeliveryStatus('In Transit')">
                        <i class="fas fa-truck"></i>
                        <span>On the Way</span>
                        <p class="status-desc">I'm heading to delivery</p>
                    </div>
                    <div class="status-option" data-status="Delivered" onclick="updateDeliveryStatus('Delivered')">
                        <i class="fas fa-check-circle"></i>
                        <span>Delivered</span>
                        <p class="status-desc">Order delivered successfully</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>My Profile</h2>
                <button class="modal-close" onclick="closeModal('profileModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h3 id="profileName"></h3>
                        <p id="profileEmail"></p>
                        <p id="profilePhone"></p>
                        <p id="profileAddress"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" onclick="logoutDelivery()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Location Map Modal -->
    <div id="locationModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-route"></i> Navigation to Customer</h2>
                <button class="modal-close" onclick="closeModal('locationModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="locationMapContent" class="modal-body modal-map-body">
                <!-- Map content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('locationModal')">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Set Your Location Modal -->
    <div id="setLocationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-map-pin"></i> Set Your Current Location</h2>
                <button class="modal-close" onclick="closeModal('setLocationModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="manual-location-info">
                    <p style="color: #64748b; margin-bottom: 1.5rem; text-align: center;">
                        <i class="fas fa-info-circle"></i> 
                        Search for your current location to share it with customers
                    </p>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1e293b;">
                            <i class="fas fa-search-location"></i> Search for your location:
                        </label>
                        <input type="text" id="locationSearchInput" 
                               placeholder="Enter address, landmark, or place name..." 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;">
                        <button id="searchLocationBtn" class="btn btn-primary" style="margin-top: 0.75rem; width: 100%;">
                            <i class="fas fa-search"></i> Search Location
                        </button>
                    </div>
                    
                    <!-- Hidden coordinates fields -->
                    <input type="hidden" id="manualLatitude">
                    <input type="hidden" id="manualLongitude">
                    
                    <button id="setManualLocationBtn" class="btn btn-success" style="margin-top: 0.75rem; width: 100%; display: none;">
                        <i class="fas fa-check"></i> Confirm Location
                    </button>
                    
                    <div id="selectedLocationPreview" style="display: none; background: #f0fdf4; border: 2px solid #86efac; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                        <p style="margin: 0; color: #166534; font-weight: 600;">
                            <i class="fas fa-map-marker-alt"></i> 
                            <span id="selectedLocationName"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/alert.js"></script>
    <!-- Mapbox JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <script src="js/delivery.js"></script>
</body>

</html>