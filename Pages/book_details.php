<?php
// Public endpoint: returns JSON details for a single book by id
header('Content-Type: application/json');

require_once __DIR__ . '/../Config/dbconnection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

if (!isset($connection) || !($connection instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$sql = "
    SELECT 
        b.book_id,
        b.isbn,
        b.google_books_id,
        b.title,
        a.author_name,
        c.category_name,
        b.publisher,
        b.published_date,
        b.page_count,
        b.description,
        b.cover_image_url,
        b.language,
        b.total_copies,
        b.available_copies,
        b.shelf_location,
        b.book_condition
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN categories c ON b.category_id = c.category_id
    WHERE b.book_id = ?
    LIMIT 1
";

$stmt = $connection->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query prepare failed']);
    exit;
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query execute failed']);
    exit;
}

$res = $stmt->get_result();
if ($res && $row = $res->fetch_assoc()) {
    $res->free();
    $stmt->close();
    echo json_encode(['success' => true, 'book' => $row]);
    exit;
}

// Fallback: bind_result way if get_result is not available
$meta = $stmt->result_metadata();
if ($meta) {
    $fields = [];
    $binds = [];
    while ($field = $meta->fetch_field()) {
        $fields[$field->name] = null;
        $binds[] = & $fields[$field->name];
    }
    $meta->free();
    call_user_func_array([$stmt, 'bind_result'], $binds);
    if ($stmt->fetch()) {
        $stmt->close();
        echo json_encode(['success' => true, 'book' => $fields]);
        exit;
    }
}

$stmt->close();
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Not found']);
exit;
?>
