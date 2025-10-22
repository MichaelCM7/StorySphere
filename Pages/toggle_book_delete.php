<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require "../Config/dbconnection.php";

if (!isset($connection)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $book_id = $input['book_id'] ?? null;
    $is_deleted = $input['is_deleted'] ?? null;

    if ($book_id === null || $is_deleted === null) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    $stmt = $connection->prepare("UPDATE books SET is_deleted = ? WHERE book_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $connection->error]);
        exit;
    }

    $stmt->bind_param("ii", $is_deleted, $book_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Book status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
    }

    $stmt->close();
    $connection->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
