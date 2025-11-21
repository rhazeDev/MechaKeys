<?php
session_start();
include '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $query = "SELECT pv.*, p.Brand as ProductBrand, p.Model as ProductModel, p.Category as ProductCategory
              FROM productvariations pv
              JOIN products p ON pv.ProductID = p.ProductID
              ORDER BY pv.StockQuantity ASC, p.Brand ASC, p.Model ASC";

    $result = $conn->query($query);

    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[] = [
            'VariationID' => $row['VariationID'],
            'ProductBrand' => $row['ProductBrand'],
            'ProductModel' => $row['ProductModel'],
            'Layout' => $row['Layout'],
            'SwitchType' => $row['SwitchType'],
            'Color' => $row['Color'],
            'Category' => $row['ProductCategory'] ?? '',
            'Price' => $row['Price'],
            'StockQuantity' => $row['StockQuantity']
        ];
    }

    echo json_encode([
        'success' => true,
        'inventory' => $inventory
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>