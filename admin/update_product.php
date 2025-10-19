<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['product_id']) || empty($_POST['brand']) || empty($_POST['model']) || 
            empty($_POST['category']) || empty($_POST['description'])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        $product_id = intval($_POST['product_id']);
        $brand = $conn->real_escape_string($_POST['brand']);
        $model = $conn->real_escape_string($_POST['model']);
        $category = $conn->real_escape_string($_POST['category']);
        $description = $conn->real_escape_string($_POST['description']);

        // Validate variations
        if (!isset($_POST['variations']) || empty($_POST['variations'])) {
            echo json_encode(['success' => false, 'message' => 'At least one product variation is required']);
            exit;
        }

        // Start transaction
        $conn->begin_transaction();

        // Update product
        $update_product = "UPDATE products 
                          SET Brand = '$brand', Model = '$model', Description = '$description', Category = '$category' 
                          WHERE ProductID = $product_id";
        
        if (!$conn->query($update_product)) {
            throw new Exception('Failed to update product: ' . $conn->error);
        }

        // Get existing variation IDs
        $existing_variations = [];
        $existing_query = "SELECT VariationID FROM productvariations WHERE ProductID = $product_id";
        $existing_result = $conn->query($existing_query);
        while ($row = $existing_result->fetch_assoc()) {
            $existing_variations[] = $row['VariationID'];
        }

        $updated_variations = [];

        // Update or insert variations
        foreach ($_POST['variations'] as $variation) {
            $variation_id = isset($variation['variation_id']) ? intval($variation['variation_id']) : 0;
            $layout = isset($variation['layout']) ? $conn->real_escape_string($variation['layout']) : '';
            $switch_type = $conn->real_escape_string($variation['switch']);
            $color = $conn->real_escape_string($variation['color']);
            $price = floatval($variation['price']);
            $stock = intval($variation['stock']);

            if ($variation_id > 0 && in_array($variation_id, $existing_variations)) {
                // Update existing variation
                $update_variation = "UPDATE productvariations 
                                   SET Layout = '$layout', SwitchType = '$switch_type', Color = '$color', 
                                       Price = $price, StockQuantity = $stock 
                                   WHERE VariationID = $variation_id";
                
                if (!$conn->query($update_variation)) {
                    throw new Exception('Failed to update variation: ' . $conn->error);
                }
                $updated_variations[] = $variation_id;
            } else {
                // Insert new variation
                $insert_variation = "INSERT INTO productvariations (ProductID, Layout, SwitchType, Color, Price, StockQuantity) 
                                   VALUES ($product_id, '$layout', '$switch_type', '$color', $price, $stock)";
                
                if (!$conn->query($insert_variation)) {
                    throw new Exception('Failed to insert variation: ' . $conn->error);
                }
                $updated_variations[] = $conn->insert_id;
            }
        }

        // Delete variations that were removed
        $variations_to_delete = array_diff($existing_variations, $updated_variations);
        if (!empty($variations_to_delete)) {
            $delete_ids = implode(',', $variations_to_delete);
            $delete_variations = "DELETE FROM productvariations WHERE VariationID IN ($delete_ids)";
            if (!$conn->query($delete_variations)) {
                throw new Exception('Failed to delete variations: ' . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Product updated successfully!'
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
