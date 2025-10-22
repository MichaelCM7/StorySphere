<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../Config/dbconnection.php";

header('Content-Type: application/json');

// ✅ Step 1: Check connection file path
$path = realpath(__DIR__ . '/../Config/dbconnection.php');
if (!$path) {
    echo json_encode(['success' => false, 'message' => 'Database connection file not found']);
    exit;
}
require $path;

if (!isset($conn) && isset($connection)) {
    $conn = $connection;
}

if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Database connection ($conn) not initialized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // ✅ Step 3: Show what input is being received
    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing JSON',
            'raw_input' => file_get_contents('php://input')
        ]);
        exit;
    }

    $user_id = $input['user_id'] ?? null;
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $phone_number = $input['phone_number'] ?? null;
    $role = $input['role'] ?? null;

    if (!$user_id || !$name || !$email || !$role || !$phone_number) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
    }

    // ✅ Step 4: Split full name into first & last (optional)
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '';

    // ✅ Step 5: Prepare statement
    $stmt = $conn->prepare("
    UPDATE users 
    SET first_name = ?, last_name = ?, email = ?, phone_number = ?, 
        role_id = (SELECT role_id FROM roles WHERE role_name = ?) 
    WHERE user_id = ?
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone_number, $role, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
