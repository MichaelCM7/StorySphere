<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require "../Config/dbconnection.php";

if (!isset($connection)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing JSON', 'raw' => $raw]);
    exit;
}

$book_id      = (int)($input['book_id'] ?? 0);
$title        = trim($input['title'] ?? '');
$author_name  = trim($input['author'] ?? '');
$category_id  = (int)($input['category'] ?? 0);
$isbn         = trim($input['isbn'] ?? '');

if (!$book_id || !$title || !$author_name || !$category_id || !$isbn) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields',
        'received' => $input
    ]);
    exit;
}

// 🔍 Find or create author
$stmt_author = $connection->prepare("SELECT author_id FROM authors WHERE author_name = ?");
$stmt_author->bind_param("s", $author_name);
$stmt_author->execute();
$stmt_author->store_result();
$stmt_author->bind_result($author_id);

if ($stmt_author->num_rows > 0) {
    $stmt_author->fetch();
} else {
    $stmt_insert = $connection->prepare("INSERT INTO authors (author_name) VALUES (?)");
    $stmt_insert->bind_param("s", $author_name);
    $stmt_insert->execute();
    $author_id = $stmt_insert->insert_id;
    $stmt_insert->close();
}
$stmt_author->close();

// ✅ Update only the editable fields
$stmt = $connection->prepare("
    UPDATE books 
    SET title = ?, author_id = ?, category_id = ?, isbn = ?
    WHERE book_id = ?
");
$stmt->bind_param("siisi", $title, $author_id, $category_id, $isbn, $book_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'SQL execute failed: ' . $stmt->error]);
}

$stmt->close();
$connection->close();
?>
