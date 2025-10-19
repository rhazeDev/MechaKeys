<?php
session_start();
include '../conn.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $query = "SELECT p.*, 
              (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
              (SELECT SUM(StockQuantity) FROM productvariations WHERE ProductID = p.ProductID) as TotalStock,
              (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
              (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice
              FROM products p 
              ORDER BY p.ProductID DESC";
    
    $result = $conn->query($query);
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Format price display
        $minPrice = floatval($row['MinPrice']);
        $maxPrice = floatval($row['MaxPrice']);
        $priceDisplay = ($minPrice == $maxPrice) ? 
                        number_format($minPrice, 2) : 
                        number_format($minPrice, 2) . ' - ' . number_format($maxPrice, 2);
        
        $products[] = [
            'ProductID' => $row['ProductID'],
            'Brand' => $row['Brand'],
            'Model' => $row['Model'],
            'Description' => $row['Description'],
            'Category' => ucfirst($row['Category']),
            'Price' => $priceDisplay,
            'MinPrice' => $minPrice,
            'MaxPrice' => $maxPrice,
            'TotalSold' => $row['TotalSold'],
            'TotalStock' => $row['TotalStock'] ?? 0,
            'ImagePath' => $row['ImagePath']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
