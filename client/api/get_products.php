<?php
session_start();
require_once __DIR__ . '/../../conn.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$layoutRaw = isset($_GET['layout']) ? trim($_GET['layout']) : '';
$layout = '';
if (!empty($layoutRaw)) {
    $layout = preg_replace('/[^0-9]/', '', $layoutRaw);
}
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '';
if (!empty($searchRaw)) {
    $search = $searchRaw;
}

$sql = "SELECT
            p.ProductID,
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
            p.TotalSold,
            (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
            (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice,
            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations
        FROM products p
        LEFT JOIN productvariations pv ON p.ProductID = pv.ProductID";

$where = [];
$params = [];
$types = '';
if (!empty($category)) {
    $where[] = "p.Category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($layout)) {
    $where[] = "pv.Layout = ?";
    $params[] = (int) $layout;
    $types .= 'i';
}
if (!empty($brand)) {
    $where[] = "p.Brand = ?";
    $params[] = $brand;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "(LOWER(p.Brand) LIKE ? OR LOWER(p.Model) LIKE ? OR LOWER(p.Description) LIKE ? OR LOWER(CONCAT(p.Brand, ' ', p.Model)) LIKE ? OR LOWER(pv.SwitchType) LIKE ? OR LOWER(pv.Color) LIKE ?)";
    $like = '%' . strtolower($search) . '%';
    for ($i = 0; $i < 6; $i++)
        $params[] = $like;
    $types .= 'ssssss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY p.ProductID
          ORDER BY p.TotalSold DESC, p.ProductID DESC";

try {
    if (!empty($where)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
    } else {
        $result = $conn->query($sql);
    }

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
                $imagePath = substr($imagePath, strlen('mechakeys/'));
            }
            $minPrice = floatval($row['MinPrice'] ?? 0);
            $maxPrice = floatval($row['MaxPrice'] ?? 0);
            $priceDisplay = '';
            if ($minPrice > 0 && $maxPrice > 0) {
                if ($minPrice == $maxPrice) {
                    $priceDisplay = '₱' . number_format($minPrice, 2);
                } else {
                    $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                }
            } else {
                $priceDisplay = 'Price not available';
            }

            $brandPart = isset($row['Brand']) && trim($row['Brand']) !== '' && strtoupper(trim($row['Brand'])) !== 'N/A' ? trim($row['Brand']) . ' ' : '';

            $rawVariations = $row['Variations'] ?? '';
            $cat = strtolower(trim($row['Category']));
            $blankCats = ['switches', 'keycaps', 'accessories'];
            $processedVariations = '';
            if ($rawVariations) {
                $parts = array_map('trim', explode(';', $rawVariations));
                $newParts = [];
                foreach ($parts as $p) {
                    $subparts = array_map('trim', explode('|', $p));
                    $cleanSub = [];
                    foreach ($subparts as $sp) {
                        if ($sp === '')
                            continue;
                        if (strtoupper($sp) === 'N/A' && in_array($cat, $blankCats))
                            continue;
                        $cleanSub[] = $sp;
                    }
                    if (!empty($cleanSub))
                        $newParts[] = implode(' | ', $cleanSub);
                }
                $processedVariations = implode('; ', $newParts);
            }

            $products[] = [
                'id' => $row['ProductID'],
                'name' => $brandPart . $row['Model'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'price' => $priceDisplay,
                'image' => $imagePath ? ('../' . $imagePath) : null,
                'specs' => $processedVariations ? $processedVariations : $row['Category'],
                'total_sold' => $row['TotalSold']
            ];
        }
    }

    echo json_encode(['success' => true, 'products' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
<?php
session_start();
require_once __DIR__ . '/../../conn.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$layoutRaw = isset($_GET['layout']) ? trim($_GET['layout']) : '';
$layout = '';
if (!empty($layoutRaw)) {
    $layout = preg_replace('/[^0-9]/', '', $layoutRaw);
}
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '';
if (!empty($searchRaw)) {
    $search = $searchRaw;
}

$sql = "SELECT
            p.ProductID,
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
            p.TotalSold,
            (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
            (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice,
            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations
        FROM products p
        LEFT JOIN productvariations pv ON p.ProductID = pv.ProductID";

$where = [];
$params = [];
$types = '';
if (!empty($category)) {
    $where[] = "p.Category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($layout)) {
    $where[] = "pv.Layout = ?";
    $params[] = (int) $layout;
    $types .= 'i';
}
if (!empty($brand)) {
    $where[] = "p.Brand = ?";
    $params[] = $brand;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "(LOWER(p.Brand) LIKE ? OR LOWER(p.Model) LIKE ? OR LOWER(p.Description) LIKE ? OR LOWER(CONCAT(p.Brand, ' ', p.Model)) LIKE ? OR LOWER(pv.SwitchType) LIKE ? OR LOWER(pv.Color) LIKE ?)";
    $like = '%' . strtolower($search) . '%';
    for ($i = 0; $i < 6; $i++)
        $params[] = $like;
    $types .= 'ssssss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY p.ProductID
          ORDER BY p.TotalSold DESC, p.ProductID DESC";

try {
    if (!empty($where)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
    } else {
        $result = $conn->query($sql);
    }

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
                $imagePath = substr($imagePath, strlen('mechakeys/'));
            }
            $minPrice = floatval($row['MinPrice'] ?? 0);
            $maxPrice = floatval($row['MaxPrice'] ?? 0);
            $priceDisplay = '';
            if ($minPrice > 0 && $maxPrice > 0) {
                if ($minPrice == $maxPrice) {
                    $priceDisplay = '₱' . number_format($minPrice, 2);
                } else {
                    $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                }
            } else {
                $priceDisplay = 'Price not available';
            }

            $brandPart = isset($row['Brand']) && trim($row['Brand']) !== '' && strtoupper(trim($row['Brand'])) !== 'N/A' ? trim($row['Brand']) . ' ' : '';
            $products[] = [
                'id' => $row['ProductID'],
                'name' => $brandPart . $row['Model'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'price' => $priceDisplay,
                'image' => $imagePath ? ('../' . $imagePath) : null,
                'specs' => $row['Variations'] ? $row['Variations'] : $row['Category'],
                'total_sold' => $row['TotalSold']
            ];
        }
    }

    echo json_encode(['success' => true, 'products' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
<?php
session_start();
require_once __DIR__ . '/../../conn.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$layoutRaw = isset($_GET['layout']) ? trim($_GET['layout']) : '';
$layout = '';
if (!empty($layoutRaw)) {
    $layout = preg_replace('/[^0-9]/', '', $layoutRaw);
}
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '';
if (!empty($searchRaw)) {
    $search = $searchRaw;
}

$sql = "SELECT
            p.ProductID,
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
            p.TotalSold,
            (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
            (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice,
            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations
        FROM products p
        LEFT JOIN productvariations pv ON p.ProductID = pv.ProductID";

$where = [];
$params = [];
$types = '';
if (!empty($category)) {
    $where[] = "p.Category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($layout)) {
    $where[] = "pv.Layout = ?";
    $params[] = (int) $layout;
    $types .= 'i';
}
if (!empty($brand)) {
    $where[] = "p.Brand = ?";
    $params[] = $brand;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "(LOWER(p.Brand) LIKE ? OR LOWER(p.Model) LIKE ? OR LOWER(p.Description) LIKE ? OR LOWER(CONCAT(p.Brand, ' ', p.Model)) LIKE ? OR LOWER(pv.SwitchType) LIKE ? OR LOWER(pv.Color) LIKE ?)";
    $like = '%' . strtolower($search) . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssssss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY p.ProductID
          ORDER BY p.TotalSold DESC, p.ProductID DESC";

try {
    if (!empty($where)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
    } else {
        $result = $conn->query($sql);
    }

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
                $imagePath = substr($imagePath, strlen('mechakeys/'));
            }
            $minPrice = floatval($row['MinPrice'] ?? 0);
            $maxPrice = floatval($row['MaxPrice'] ?? 0);
            $priceDisplay = '';
            if ($minPrice > 0 && $maxPrice > 0) {
                if ($minPrice == $maxPrice) {
                    $priceDisplay = '₱' . number_format($minPrice, 2);
                } else {
                    $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                }
            } else {
                $priceDisplay = 'Price not available';
            }

            $brandPart = isset($row['Brand']) && trim($row['Brand']) !== '' && strtoupper(trim($row['Brand'])) !== 'N/A' ? trim($row['Brand']) . ' ' : '';
            $products[] = [
                'id' => $row['ProductID'],
                'name' => $brandPart . $row['Model'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'price' => $priceDisplay,
                'image' => $imagePath ? ('../' . $imagePath) : null,
                'specs' => $row['Variations'] ? $row['Variations'] : $row['Category'],
                'total_sold' => $row['TotalSold']
            ];
        }
    }

    echo json_encode(['success' => true, 'products' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
<?php
session_start();
require_once __DIR__ . '/../../conn.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$layoutRaw = isset($_GET['layout']) ? trim($_GET['layout']) : '';
$layout = '';
if (!empty($layoutRaw)) {
    $layout = preg_replace('/[^0-9]/', '', $layoutRaw);
}
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '';
if (!empty($searchRaw)) {
    $search = $searchRaw;
}

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

$sql = "SELECT
            p.ProductID,
            p.Brand,
            p.Model,
            p.Description,
            p.Category,
            p.TotalSold,
            (SELECT Path FROM productimages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,
            (SELECT MIN(Price) FROM productvariations WHERE ProductID = p.ProductID) as MinPrice,
            (SELECT MAX(Price) FROM productvariations WHERE ProductID = p.ProductID) as MaxPrice,
            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations
        FROM products p
        LEFT JOIN productvariations pv ON p.ProductID = pv.ProductID";

$where = [];
$params = [];
$types = '';
if (!empty($category)) {
    $where[] = "p.Category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($layout)) {
    $where[] = "pv.Layout = ?";
    $params[] = (int) $layout;
    $types .= 'i';
}
if (!empty($brand)) {
    $where[] = "p.Brand = ?";
    $params[] = $brand;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "(LOWER(p.Brand) LIKE ? OR LOWER(p.Model) LIKE ? OR LOWER(p.Description) LIKE ? OR LOWER(CONCAT(p.Brand, ' ', p.Model)) LIKE ? OR LOWER(pv.SwitchType) LIKE ? OR LOWER(pv.Color) LIKE ?)";
    $like = '%' . strtolower($search) . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssssss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY p.ProductID
          ORDER BY p.TotalSold DESC, p.ProductID DESC";

try {
    $debugInfo = [
        'sql' => $sql,
        'types' => $types,
        'params' => $params
    ];
    if (!empty($where)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
    } else {
        $result = $conn->query($sql);
    }

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
                $imagePath = substr($imagePath, strlen('mechakeys/'));
            }
            $minPrice = floatval($row['MinPrice'] ?? 0);
            $maxPrice = floatval($row['MaxPrice'] ?? 0);
            $priceDisplay = '';
            if ($minPrice > 0 && $maxPrice > 0) {
                if ($minPrice == $maxPrice) {
                    $priceDisplay = '₱' . number_format($minPrice, 2);
                } else {
                    $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                }
            } else {
                $priceDisplay = 'Price not available';
            }

            $brandPart = isset($row['Brand']) && trim($row['Brand']) !== '' && strtoupper(trim($row['Brand'])) !== 'N/A' ? trim($row['Brand']) . ' ' : '';
            $products[] = [
                'id' => $row['ProductID'],
                'name' => $brandPart . $row['Model'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'price' => $priceDisplay,
                'image' => $imagePath ? ('../' . $imagePath) : null,
                'specs' => $row['Variations'] ? $row['Variations'] : $row['Category'],
                'total_sold' => $row['TotalSold']
            ];
        }
    }

    $out = ['success' => true, 'products' => $products];
    if ($debug)
        $out['debug'] = $debugInfo;
    echo json_encode($out);
} catch (Exception $e) {
    $out = ['success' => false, 'message' => $e->getMessage()];
    if ($debug)
        $out['debug'] = $debugInfo;
    echo json_encode($out);
}

$conn->close();
?>
<?php
session_start();
require_once __DIR__ . '/../../conn.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$layoutRaw = isset($_GET['layout']) ? trim($_GET['layout']) : '';
$layout = '';
if (!empty($layoutRaw)) {
    $layout = preg_replace('/[^0-9]/', '', $layoutRaw);
}
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = '';
if (!empty($searchRaw)) {
    $search = $searchRaw;
}

$sql = "SELECT \
            p.ProductID,\n+            p.Brand,\n+            p.Model,\n+            p.Description,\n+            p.Category,\n+            p.TotalSold,\n+            (SELECT Path FROM ProductImages WHERE ProductImageID = p.ProductImageID LIMIT 1) as ImagePath,\n+            (SELECT MIN(Price) FROM ProductVariations WHERE ProductID = p.ProductID) as MinPrice,\n+            (SELECT MAX(Price) FROM ProductVariations WHERE ProductID = p.ProductID) as MaxPrice,\n+            GROUP_CONCAT(DISTINCT CONCAT(pv.Layout, '% | ', pv.SwitchType, ' | ', pv.Color) SEPARATOR '; ') as Variations\n+        FROM Products p\n+        LEFT JOIN ProductVariations pv ON p.ProductID = pv.ProductID";

$where = [];
$params = [];
$types = '';
if (!empty($category)) {
    $where[] = "p.Category = ?";
    $params[] = $category;
    $types .= 's';
}
if (!empty($layout)) {
    $where[] = "pv.Layout = ?";
    $params[] = (int) $layout;
    $types .= 'i';
}
if (!empty($brand)) {
    $where[] = "p.Brand = ?";
    $params[] = $brand;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "(LOWER(p.Brand) LIKE ? OR LOWER(p.Model) LIKE ? OR LOWER(p.Description) LIKE ? OR LOWER(CONCAT(p.Brand, ' ', p.Model)) LIKE ? OR LOWER(pv.SwitchType) LIKE ? OR LOWER(pv.Color) LIKE ?)";
    $like = '%' . strtolower($search) . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssssss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY p.ProductID\n+          ORDER BY p.TotalSold DESC, p.ProductID DESC";

try {
    if (!empty($where)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
    } else {
        $result = $conn->query($sql);
    }

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['ImagePath'];
            if ($imagePath && strpos($imagePath, 'mechakeys/') === 0) {
                $imagePath = substr($imagePath, strlen('mechakeys/'));
            }
            $minPrice = floatval($row['MinPrice'] ?? 0);
            $maxPrice = floatval($row['MaxPrice'] ?? 0);
            $priceDisplay = '';
            if ($minPrice > 0 && $maxPrice > 0) {
                if ($minPrice == $maxPrice) {
                    $priceDisplay = '₱' . number_format($minPrice, 2);
                } else {
                    $priceDisplay = '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2);
                }
            } else {
                $priceDisplay = 'Price not available';
            }

            $brandPart = isset($row['Brand']) && trim($row['Brand']) !== '' && strtoupper(trim($row['Brand'])) !== 'N/A' ? trim($row['Brand']) . ' ' : '';
            $products[] = [
                'id' => $row['ProductID'],
                'name' => $brandPart . $row['Model'],
                'description' => $row['Description'],
                'category' => $row['Category'],
                'price' => $priceDisplay,
                'image' => $imagePath ? ('../' . $imagePath) : null,
                'specs' => $row['Variations'] ? $row['Variations'] : $row['Category'],
                'total_sold' => $row['TotalSold']
            ];
        }
    }

    echo json_encode(['success' => true, 'products' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>