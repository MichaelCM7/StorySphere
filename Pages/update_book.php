<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../Config/dbconnection.php";

header('Content-Type: application/json');

if (!isset($conn) && isset($connection)) {
    $conn = $connection;
}

if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Database connection ($conn) not initialized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing JSON',
            'raw_input' => file_get_contents('php://input')
        ]);
        exit;
    }

    $book_id = $input['book_id'] ?? null;
    $title = $input['title'] ?? null;
    $author_id = $input['author_id'] ?? null;
    $category_id = $input['category_id'] ?? null;
    $isbn = $input['isbn'] ?? null;

    if (!$book_id || !$title || !$author_id || !$category_id || !$isbn) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Update the book record
    $stmt = $conn->prepare("
        UPDATE books 
        SET title = ?, author_id = ?, category_id = ?, isbn = ?
        WHERE book_id = ?
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("siisi", $title, $author_id, $category_id, $isbn, $book_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
