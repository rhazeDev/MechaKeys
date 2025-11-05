<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;

if ($latitude == 0 || $longitude == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid location coordinates']);
    exit;
}

$rider_id = $_SESSION['user_id'];
$location = $longitude . ',' . $latitude;
try {
    $update_location = $conn->prepare("UPDATE users SET Location = ? WHERE ID = ?");
    $update_location->bind_param("si", $location, $rider_id);

    if ($update_location->execute()) {
        $update_location->close();
        $conn->close();

        echo json_encode([
            'success' => true,
            'message' => 'Location updated successfully',
            'location' => $location
        ]);
    } else {
        throw new Exception('Failed to update location');
    }
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>