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
    // Call stored procedure ReturnBook
    $stmt = $connection->prepare('CALL ReturnBook(?)');
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $connection->error);
    }
    
    $stmt->bind_param('i', $borrowingId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }
    
    // Clear any result sets
    while ($stmt->more_results() && $stmt->next_result()) {
        /* clear results */ 
    }
    
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Book returned successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to return book: ' . $e->getMessage()]);
}