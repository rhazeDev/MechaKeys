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
        if (empty($_POST['brand']) || empty($_POST['model']) || empty($_POST['category']) || empty($_POST['description'])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        $brand = $conn->real_escape_string($_POST['brand']);
        $model = $conn->real_escape_string($_POST['model']);
        $category = $conn->real_escape_string($_POST['category']);
        $description = $conn->real_escape_string($_POST['description']);

        if (!isset($_POST['variations']) || empty($_POST['variations'])) {
            echo json_encode(['success' => false, 'message' => 'At least one product variation is required']);
            exit;
        }

        if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
            echo json_encode(['success' => false, 'message' => 'At least one product image is required']);
            exit;
        }

        $conn->begin_transaction();

        $insert_product = "INSERT INTO products (Brand, Model, Description, Category, TotalSold, ProductImageID) 
                          VALUES ('$brand', '$model', '$description', '$category', 0, 0)";

        if (!$conn->query($insert_product)) {
            throw new Exception('Failed to insert product: ' . $conn->error);
        }

        $product_id = $conn->insert_id;

        $update_image_id = "UPDATE products SET ProductImageID = $product_id WHERE ProductID = $product_id";
        $conn->query($update_image_id);

        $product_folder = str_replace(' ', '', $brand) . str_replace(' ', '', $model);
        $upload_dir = "../../products/" . strtolower($category) . "s/" . $product_folder . "/";

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $uploaded_images = 0;
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $new_filename = "image" . ($uploaded_images + 1) . "." . $file_extension;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $image_path = "mechakeys/products/" . strtolower($category) . "s/" . $product_folder . "/" . $new_filename;
                    $insert_image = "INSERT INTO productimages (ProductImageID, Path) VALUES ($product_id, '$image_path')";

                    if (!$conn->query($insert_image)) {
                        throw new Exception('Failed to insert image: ' . $conn->error);
                    }
                    $uploaded_images++;
                }
            }
        }

        if ($uploaded_images === 0) {
            throw new Exception('Failed to upload any images');
        }

        foreach ($_POST['variations'] as $variation) {
            $layout = isset($variation['layout']) ? $conn->real_escape_string($variation['layout']) : '';
            $switch_type = $conn->real_escape_string($variation['switch']);
            $color = $conn->real_escape_string($variation['color']);
            $price = floatval($variation['price']);
            $stock = intval($variation['stock']);

            $insert_variation = "INSERT INTO productvariations (ProductID, Layout, SwitchType, Color, Price, StockQuantity) 
                                VALUES ($product_id, '$layout', '$switch_type', '$color', $price, $stock)";

            if (!$conn->query($insert_variation)) {
                throw new Exception('Failed to insert variation: ' . $conn->error);
            }
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully!',
            'product_id' => $product_id
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