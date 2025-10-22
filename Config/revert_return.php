<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once __DIR__ . '/dbconnection.php';

if (!isset($connection)) {
    echo json_encode(['success' => false, 'message' => 'Database connection not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$borrowingId = $input['borrowing_id'] ?? null;

if ($borrowingId === null) {
    echo json_encode(['success' => false, 'message' => 'Missing borrowing ID']);
    exit;
}

try {
    // Begin transaction
    $connection->begin_transaction();

    // Reset return_date and revert book_status_id to previous state
    $stmt = $connection->prepare('
        UPDATE borrowing_records 
        SET return_date = NULL,
            book_status_id = CASE 
                WHEN CURDATE() > due_date THEN 3  -- Overdue
                ELSE 1                            -- Borrowed
            END
        WHERE borrowing_id = ?
    ');

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $connection->error);
    }
    
    $stmt->bind_param('i', $borrowingId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    // Update the book's available copies
    $stmt2 = $connection->prepare('
        UPDATE books b
        JOIN borrowing_records br ON b.book_id = br.book_id
        SET b.available_copies = b.available_copies - 1
        WHERE br.borrowing_id = ?
    ');

    if (!$stmt2) {
        throw new Exception('Failed to prepare statement for book update: ' . $connection->error);
    }
    
    $stmt2->bind_param('i', $borrowingId);
    if (!$stmt2->execute()) {
        throw new Exception('Failed to execute book update: ' . $stmt2->error);
    }

    $connection->commit();
    
    $stmt->close();
    $stmt2->close();
    
    echo json_encode(['success' => true, 'message' => 'Book return reverted successfully']);
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to revert return: ' . $e->getMessage()]);
}