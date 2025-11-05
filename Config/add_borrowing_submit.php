<?php
require_once __DIR__ . '/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? '';
    $bookId = $_POST['book_id'] ?? '';
    $issueDate = $_POST['issue_date'] ?? '';
    $dueDate = $_POST['due_date'] ?? '';

    if (empty($userId) || empty($bookId) || empty($issueDate) || empty($dueDate)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $db = getDbConnection();

    // Check if the user and book exist
    $userCheck = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $userCheck->bind_param('i', $userId);
    $userCheck->execute();
    $userResult = $userCheck->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Member ID.']);
        exit;
    }

    $bookCheck = $db->prepare("SELECT book_id FROM books WHERE book_id = ? AND available_copies > 0");
    $bookCheck->bind_param('i', $bookId);
    $bookCheck->execute();
    $bookResult = $bookCheck->get_result();

    if ($bookResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Book ID or no available copies.']);
        exit;
    }

    // Insert the new borrowing record
    $insertQuery = "INSERT INTO borrowing_records (user_id, book_id, issue_date, due_date, book_status_id) VALUES (?, ?, ?, ?, 1)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bind_param('iiss', $userId, $bookId, $issueDate, $dueDate);

    if ($stmt->execute()) {
        // Decrement the available copies of the book
        $updateBookQuery = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?";
        $updateStmt = $db->prepare($updateBookQuery);
        $updateStmt->bind_param('i', $bookId);
        $updateStmt->execute();

        echo json_encode(['success' => true, 'message' => 'Borrowing record added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add borrowing record.']);
    }

    $stmt->close();
    $db->close();
}
?>