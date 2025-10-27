<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    $query = "SELECT ID, FullName, Contact, Email 
              FROM users 
              WHERE Role = 'delivery' 
              ORDER BY FullName ASC";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $riders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $riders[] = $row;
    }

    echo json_encode($riders);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch delivery riders: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>