<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
ob_clean(); // Clear stray whitespace

require "../Config/dbconnection.php";

if (!isset($connection)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

// 1️⃣ Get input
$title = trim($input['title'] ?? '');
$author_name = trim($input['author'] ?? '');
$category_id = (int)($input['category'] ?? 0);
$isbn = trim($input['isbn'] ?? '');

// 2️⃣ Validate required fields
if (!$title || !$author_name || !$category_id || !$isbn) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// 3️⃣ Validate category exists
$stmt_cat = $connection->prepare("SELECT category_id FROM categories WHERE category_id = ?");
$stmt_cat->bind_param("i", $category_id);
$stmt_cat->execute();
$stmt_cat->store_result();
if ($stmt_cat->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
    $stmt_cat->close();
    exit;
}
$stmt_cat->close();

// 4️⃣ Check if author exists
$stmt_author = $connection->prepare("SELECT author_id FROM authors WHERE author_name = ?");
$stmt_author->bind_param("s", $author_name);
$stmt_author->execute();
$stmt_author->store_result();
$stmt_author->bind_result($author_id);

if ($stmt_author->num_rows > 0) {
    $stmt_author->fetch(); // Author exists
} else {
    // Insert new author
    $stmt_insert = $connection->prepare("INSERT INTO authors (author_name) VALUES (?)");
    $stmt_insert->bind_param("s", $author_name);
    $stmt_insert->execute();
    $author_id = $stmt_insert->insert_id;
    $stmt_insert->close();
}
$stmt_author->close();

// 5️⃣ Insert the book
$stmt_book = $connection->prepare("INSERT INTO books (title, author_id, category_id, isbn) VALUES (?, ?, ?, ?)");
$stmt_book->bind_param("siis", $title, $author_id, $category_id, $isbn);

if ($stmt_book->execute()) {
    echo json_encode(['success' => true, 'message' => 'Book added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt_book->error]);
}

$stmt_book->close();
$connection->close();
?>
