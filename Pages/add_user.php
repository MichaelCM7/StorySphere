<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
ob_clean();
require "../Config/dbconnection.php";

if (!isset($connection) || $connection === null) {
    echo json_encode(['success' => false, 'message' => '❌ Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Decode JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// Get input fields
$first_name   = trim($input['first_name'] ?? '');
$last_name    = trim($input['last_name'] ?? '');
$email        = trim($input['email'] ?? '');
$role         = trim($input['role'] ?? '');
$phone_number = trim($input['phone_number'] ?? '');

if (!$first_name || !$last_name || !$email || !$role || !$phone_number) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Generate default password and hash it
$default_password = 'story@123';
$password_hash = password_hash($default_password, PASSWORD_BCRYPT);

// Prepare statement
$stmt = $connection->prepare("
    INSERT INTO users (first_name, last_name, email, phone_number, password_hash, role_id)
    VALUES (?, ?, ?, ?, ?, (SELECT role_id FROM roles WHERE role_name = ?))
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $connection->error]);
    exit;
}

// Bind parameters
$stmt->bind_param(
    "ssssss", 
    $first_name, 
    $last_name,  
    $email, 
    $phone_number, 
    $password_hash, 
    $role
);

// Execute
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
}

$stmt->close();
$connection->close();
