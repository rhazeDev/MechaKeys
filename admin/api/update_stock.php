<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['variation_id']) || !isset($data['stock'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $variation_id = intval($data['variation_id']);
        $stock = intval($data['stock']);

        if ($stock < 0) {
            echo json_encode(['success' => false, 'message' => 'Stock quantity cannot be negative']);
            exit;
        }

        $update_query = "UPDATE productvariations SET StockQuantity = $stock WHERE VariationID = $variation_id";

        if ($conn->query($update_query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Stock updated successfully!'
            ]);
        } else {
            throw new Exception('Failed to update stock: ' . $conn->error);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>