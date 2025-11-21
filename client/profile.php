<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../conn.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$stmt = $conn->prepare("SELECT FullName, Email, Contact, Address, Location FROM users WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);
        $location = trim($_POST['location']);

        if (!preg_match('/^09\d{9}$/', $contact)) {
            $error = 'Invalid contact number. Must start with 09 and be exactly 11 digits long.';
        } elseif (empty($address)) {
            $error = 'Address is required.';
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET Contact = ?, Address = ?, Location = ? WHERE ID = ?");
            $update_stmt->bind_param("sssi", $contact, $address, $location, $user_id);

            if ($update_stmt->execute()) {
                $message = 'Profile updated successfully!';
                $user['Contact'] = $contact;
                $user['Address'] = $address;
                $user['Location'] = $location;

                if (!empty($contact) && !empty($address) && isset($_GET['from']) && $_GET['from'] === 'checkout') {
                    header('Location: checkout.php');
                    exit;
                }
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
            $update_stmt->close();
        }
    }

    if ($_POST['action'] === 'change_password') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $pass_stmt = $conn->prepare("SELECT Password FROM users WHERE ID = ?");
        $pass_stmt->bind_param("i", $user_id);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $pass_data = $pass_result->fetch_assoc();
        $pass_stmt->close();
        $stored_password = $pass_data['Password'];
        $is_hash = is_password_hash($stored_password);
        $old_ok = false;
        if ($is_hash && verify_password($old_password, $stored_password)) {
            $old_ok = true;
            if (needs_rehash_password($stored_password)) {
                $rehash = hash_password($old_password);
                $rehash_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE ID = ?");
                $rehash_stmt->bind_param("si", $rehash, $user_id);
                $rehash_stmt->execute();
                $rehash_stmt->close();
            }
        } elseif ($stored_password === $old_password) {
            $old_ok = true;
        }

        if (!$old_ok) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $hashed_new = hash_password($new_password);
            $update_pass_stmt = $conn->prepare("UPDATE users SET Password = ? WHERE ID = ?");
            $update_pass_stmt->bind_param("si", $hashed_new, $user_id);

            if ($update_pass_stmt->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
            $update_pass_stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | MechaKeys</title>
    <link href="../css/client.css" rel="stylesheet">
    <link href="../css/profile.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <script
        src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>
    <link rel="stylesheet"
        href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css"
        type="text/css">
</head>

<body>
    <?php include 'components/navbar.php'; ?>

    <main class="profile-main">
        <div class="profile-container">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-user-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3 class="profile-username"><?php echo htmlspecialchars($user['FullName']); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($user['Email']); ?></p>
                </div>

                <nav class="profile-nav">
                    <a href="#profile-info" class="profile-nav-item active" data-tab="profile-info">
                        <i class="fas fa-user"></i>
                        <span>Profile Information</span>
                    </a>
                    <a href="#change-password" class="profile-nav-item" data-tab="change-password">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="cart.php" class="profile-nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>My Cart</span>
                    </a>
                    <a href="../logout.php" class="profile-nav-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="profile-content">
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Information Tab -->
                <div class="profile-section active" id="profile-info">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Profile Information
                        </h2>
                        <p class="section-subtitle">Update your personal information and contact details</p>
                    </div>

                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="location" id="location"
                            value="<?php echo htmlspecialchars($user['Location'] ?? ''); ?>">

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="fullname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <input type="text" id="fullname" name="fullname" class="form-input"
                                    value="<?php echo htmlspecialchars($user['FullName']); ?>" readonly>
                                <small class="form-hint">Name cannot be changed</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input"
                                    value="<?php echo htmlspecialchars($user['Email']); ?>" readonly>
                                <small class="form-hint">Email cannot be changed</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="contact" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Contact Number
                                </label>
                                <input type="text" id="contact" name="contact" class="form-input"
                                    value="<?php echo htmlspecialchars($user['Contact']); ?>" placeholder="09XXXXXXXXX"
                                    pattern="^09\d{9}$" maxlength="11" required>
                                <small class="form-hint">Must start with 09 and be exactly 11 digits (e.g.,
                                    09123456789)</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Address
                                </label>

                                <!-- Pick Location Button (shown when no address) -->
                                <div id="pickLocationContainer"
                                    style="<?php echo !empty($user['Address']) ? 'display: none;' : ''; ?>">
                                    <button type="button" class="btn-pick-location-main" onclick="openMapModal()">
                                        <i class="fas fa-map-marked-alt"></i>
                                        Pick Location on Map
                                    </button>
                                    <small class="form-hint">Click to select your address on the map</small>
                                </div>

                                <!-- Address Field (shown after picking location) -->
                                <div id="addressFieldContainer"
                                    style="<?php echo empty($user['Address']) ? 'display: none;' : ''; ?>">
                                    <textarea id="address" name="address" class="form-input form-textarea" rows="4"
                                        placeholder="Your address from map" style="resize: vertical;"
                                        required><?php echo htmlspecialchars($user['Address']); ?></textarea>
                                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                                        <button type="button" class="btn-change-location" onclick="openMapModal()">
                                            <i class="fas fa-map-marked-alt"></i>
                                            Change Location
                                        </button>
                                    </div>
                                    <small class="form-hint">You can edit the address or change location on the
                                        map</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="profile-section" id="change-password">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-lock"></i>
                            Change Password
                        </h2>
                        <p class="section-subtitle">Ensure your account is using a strong password</p>
                    </div>

                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="old_password" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Current Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="old_password" name="old_password" class="form-input"
                                        placeholder="Enter your current password" required>
                                    <button type="button" class="toggle-password" data-target="old_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="new_password" name="new_password" class="form-input"
                                        placeholder="Enter your new password" minlength="6" required>
                                    <button type="button" class="toggle-password" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-hint">Must be at least 6 characters</small>
                            </div>

                            <div class="form-group full-width">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-check-circle"></i>
                                    Confirm New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="form-input" placeholder="Confirm your new password" minlength="6"
                                        required>
                                    <button type="button" class="toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Map Modal -->
        <div id="mapModal" class="map-modal">
            <div class="map-modal-content">
                <div class="map-modal-header">
                    <h3><i class="fas fa-map-marker-alt"></i> Pick Your Location</h3>
                    <button type="button" class="map-modal-close" onclick="closeMapModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="map-modal-body">
                    <div class="map-search-container">
                        <div id="geocoder" class="geocoder"></div>
                    </div>
                    <div id="map" class="map-container"></div>
                    <div class="map-info">
                        <i class="fas fa-info-circle"></i>
                        Click on the map or search for a location to set your address
                    </div>
                </div>
                <div class="map-modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeMapModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn-confirm" onclick="confirmLocation()">
                        <i class="fas fa-check"></i> Confirm Location
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicmhhemUiLCJhIjoiY21memQycHB5MDFybzJrc2d2MXZiejJ6bCJ9.SO6KCjBMT50xiSTvRy0cIw';
        let map;
        let marker;
        let selectedLocation = {
            lng: null,
            lat: null,
            address: ''
        };

        function initMap() {
            const defaultCenter = [120.5935, 18.1978];
            const existingLocation = document.getElementById('location').value;
            let center = defaultCenter;

            if (existingLocation) {
                const coords = existingLocation.split(',');
                if (coords.length === 2) {
                    center = [parseFloat(coords[0]), parseFloat(coords[1])];
                }
            }

            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: center,
                zoom: 14
            });

            map.on('load', function () {
                console.log('Map loaded successfully');
            });

            map.addControl(new mapboxgl.NavigationControl(), 'top-right');

            const geocoder = new MapboxGeocoder({
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                marker: false,
                placeholder: 'Search for your address...',
                countries: 'ph', proximity: {
                    longitude: 120.5935,
                    latitude: 18.1978
                }
            });

            document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

            marker = new mapboxgl.Marker({
                draggable: true,
                color: '#ff4444'
            })
                .setLngLat(center)
                .addTo(map);

            marker.on('dragend', function () {
                const lngLat = marker.getLngLat();
                updateSelectedLocation(lngLat.lng, lngLat.lat);
            });

            map.on('click', function (e) {
                marker.setLngLat([e.lngLat.lng, e.lngLat.lat]);
                updateSelectedLocation(e.lngLat.lng, e.lngLat.lat);
            });

            geocoder.on('result', function (e) {
                const lngLat = e.result.center;
                marker.setLngLat(lngLat);
                selectedLocation.lng = lngLat[0];
                selectedLocation.lat = lngLat[1];
                selectedLocation.address = e.result.place_name;
            });

            updateSelectedLocation(center[0], center[1]);
        }

        async function updateSelectedLocation(lng, lat) {
            selectedLocation.lng = lng;
            selectedLocation.lat = lat;

            try {
                const response = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?access_token=${mapboxgl.accessToken}`
                );
                const data = await response.json();
                if (data.features && data.features.length > 0) {
                    selectedLocation.address = data.features[0].place_name;
                }
            } catch (error) {
                console.error('Error reverse geocoding:', error);
                selectedLocation.address = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
            }
        }

        function openMapModal() {
            const modal = document.getElementById('mapModal');
            modal.style.display = 'flex';

            setTimeout(() => {
                if (!map) {
                    console.log('Initializing map...');
                    initMap();
                } else {
                    console.log('Resizing existing map...');
                    map.resize();
                }
            }, 300);
        }

        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
        }

        function confirmLocation() {
            if (selectedLocation.lng && selectedLocation.lat) {
                document.getElementById('address').value = selectedLocation.address;

                document.getElementById('location').value = `${selectedLocation.lng},${selectedLocation.lat}`;

                document.getElementById('pickLocationContainer').style.display = 'none';
                document.getElementById('addressFieldContainer').style.display = 'block';

                closeMapModal();
            } else {
                alert('Please select a location on the map');
            }
        }

        window.onclick = function (event) {
            const modal = document.getElementById('mapModal');
            if (event.target === modal) {
                closeMapModal();
            }
        }

        const contactInput = document.getElementById('contact');
        if (contactInput) {
            contactInput.addEventListener('input', function (e) {
                this.value = this.value.replace(/\D/g, '');

                if (this.value.length > 11) {
                    this.value = this.value.slice(0, 11);
                }
            });

            contactInput.addEventListener('blur', function (e) {
                const value = this.value;
                if (value && !value.match(/^09\d{9}$/)) {
                    this.setCustomValidity('Contact number must start with 09 and be exactly 11 digits long.');
                    this.reportValidity();
                } else {
                    this.setCustomValidity('');
                }
            });

            contactInput.addEventListener('input', function (e) {
                this.setCustomValidity('');
            });
        }

        const profileForm = document.querySelector('form[action=""][method="POST"]');
        if (profileForm && profileForm.querySelector('input[name="action"][value="update_profile"]')) {
            profileForm.addEventListener('submit', function (e) {
                const contact = document.getElementById('contact').value;

                if (!contact.match(/^09\d{9}$/)) {
                    e.preventDefault();
                    alert('❌ Invalid contact number. Must start with 09 and be exactly 11 digits long (e.g., 09123456789)');
                    return false;
                }
            });
        }

        document.querySelectorAll('.profile-nav-item[data-tab]').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = item.getAttribute('data-tab');

                document.querySelectorAll('.profile-nav-item').forEach(nav => nav.classList.remove('active'));
                document.querySelectorAll('.profile-section').forEach(section => section.classList.remove('active'));

                item.classList.add('active');
                document.getElementById(targetTab).classList.add('active');

                window.location.hash = targetTab;
            });
        });

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const targetNav = document.querySelector(`[data-tab="${hash}"]`);
                if (targetNav) {
                    targetNav.click();
                }
            }

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileTrigger = document.querySelector('.profile-trigger');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (profileTrigger && dropdownMenu) {
                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                document.addEventListener('click', function (e) {
                    if (profileDropdown && !profileDropdown.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                dropdownMenu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>

</html>