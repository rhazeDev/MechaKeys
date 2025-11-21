<?php
session_start();
require_once '../../conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$password = $_POST['password'] ?? '';
$address = trim($_POST['address'] ?? '');

if (empty($full_name) || empty($email) || empty($contact) || empty($password) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!preg_match('/^09[0-9]{9}$/', $contact)) {
    echo json_encode(['success' => false, 'message' => 'Contact number must be in format 09XXXXXXXXX']);
    exit;
}

if (strlen($password) < 8 || strlen($password) > 20) {
    echo json_encode(['success' => false, 'message' => 'Password must be 8-20 characters long']);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter']);
    exit;
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one lowercase letter']);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one number']);
    exit;
}

if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one special character']);
    exit;
}

try {
    $check_email = $conn->prepare("SELECT ID FROM users WHERE Email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    $check_email->close();

    $hashed_password = hash_password($password);
    $insert_rider = $conn->prepare("INSERT INTO users (FullName, Email, Contact, Address, Password, Role, DateCreated) VALUES (?, ?, ?, ?, ?, 'delivery', NOW())");
    $insert_rider->bind_param("sssss", $full_name, $email, $contact, $address, $hashed_password);

    if ($insert_rider->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Delivery rider account created successfully',
            'rider_id' => $conn->insert_id
        ]);
    } else {
        throw new Exception('Failed to create rider account: ' . $conn->error);
    }

    $insert_rider->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>