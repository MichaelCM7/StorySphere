<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
ob_clean(); // clear any stray whitespace
require "../Config/dbconnection.php";

if (!isset($connection) || $connection === null) {
    echo json_encode([
        'success' => false,
        'message' => '❌ Database connection failed — $connection is null'
    ]);
    exit;
}

if (!isset($connection)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }

    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $role = $input['role'] ?? null;

    if (!$name || !$email || !$role) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Split full name into first and last
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '';

    // Generate a default password (you can change this)
    $default_password = password_hash('123456', PASSWORD_BCRYPT);

    // Insert user
    $stmt = $connection->prepare("
        INSERT INTO users (first_name, last_name, email, password_hash, role_id)
        VALUES (?, ?, ?, ?, (SELECT role_id FROM roles WHERE role_name = ?))
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $connection->error]);
        exit;
    }

    $stmt->bind_param("sssss", $first_name, $last_name, $email, $default_password, $role);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
    }

    $stmt->close();
    $connection->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
