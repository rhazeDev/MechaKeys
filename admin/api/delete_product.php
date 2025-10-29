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

        if (!isset($data['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $product_id = intval($data['product_id']);

        $conn->begin_transaction();

        $images_query = "SELECT Path FROM productimages WHERE ProductImageID = $product_id";
        $images_result = $conn->query($images_query);

        while ($image = $images_result->fetch_assoc()) {
            $file_path = "../" . $image['Path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $delete_images = "DELETE FROM productimages WHERE ProductImageID = $product_id";
        if (!$conn->query($delete_images)) {
            throw new Exception('Failed to delete product images: ' . $conn->error);
        }

        $delete_variations = "DELETE FROM productvariations WHERE ProductID = $product_id";
        if (!$conn->query($delete_variations)) {
            throw new Exception('Failed to delete product variations: ' . $conn->error);
        }

        $delete_product = "DELETE FROM products WHERE ProductID = $product_id";
        if (!$conn->query($delete_product)) {
            throw new Exception('Failed to delete product: ' . $conn->error);
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully!'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>