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

$rider_id = intval($_POST['rider_id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$password = $_POST['password'] ?? '';
$address = trim($_POST['address'] ?? '');

if ($rider_id <= 0 || empty($full_name) || empty($email) || empty($contact) || empty($address)) {
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

    if (!empty($password)) {
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
}

try {
    $check_email = $conn->prepare("SELECT ID FROM users WHERE Email = ? AND ID != ?");
    $check_email->bind_param("si", $email, $rider_id);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists for another user']);
        exit;
    }
    $check_email->close();

    if (!empty($password)) {
        $hashed_password = hash_password($password);
        $update_rider = $conn->prepare("UPDATE users SET FullName = ?, Email = ?, Contact = ?, Address = ?, Password = ? WHERE ID = ? AND Role = 'delivery'");
        $update_rider->bind_param("sssssi", $full_name, $email, $contact, $address, $hashed_password, $rider_id);
    } else {
        $update_rider = $conn->prepare("UPDATE users SET FullName = ?, Email = ?, Contact = ?, Address = ? WHERE ID = ? AND Role = 'delivery'");
        $update_rider->bind_param("ssssi", $full_name, $email, $contact, $address, $rider_id);
    }

    if ($update_rider->execute()) {
        if ($update_rider->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Rider information updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No changes were made'
            ]);
        }
    } else {
        throw new Exception('Failed to update rider: ' . $conn->error);
    }

    $update_rider->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>