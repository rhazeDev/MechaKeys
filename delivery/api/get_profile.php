<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$rider_id = $_SESSION['user_id'];

try {
    $profile_query = $conn->prepare("SELECT ID, FullName, Email, Contact, Address, Location FROM users WHERE ID = ? AND Role = 'delivery'");
    $profile_query->bind_param("i", $rider_id);
    $profile_query->execute();
    $result = $profile_query->get_result();
    
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        $profile_query->close();
        $conn->close();
        
        echo json_encode([
            'success' => true,
            'profile' => [
                'full_name' => $profile['FullName'],
                'email' => $profile['Email'],
                'contact' => $profile['Contact'] ?? 'Not set',
                'address' => $profile['Address'] ?? 'Not set',
                'location' => $profile['Location'] ?? ''
            ]
        ]);
    } else {
        throw new Exception('Profile not found');
    }
} catch (Exception $e) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
