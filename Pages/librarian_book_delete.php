<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    // Prevent deletion if referenced by borrowing_records or reservations
    $hasRefs = false;

    $stmt = $connection->prepare('SELECT COUNT(*) FROM borrowing_records WHERE book_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($cnt1);
    $stmt->fetch();
    $stmt->close();
    if ($cnt1 > 0) { $hasRefs = true; }

    if (!$hasRefs) {
        $stmt = $connection->prepare('SELECT COUNT(*) FROM reservations WHERE book_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($cnt2);
        $stmt->fetch();
        $stmt->close();
        if ($cnt2 > 0) { $hasRefs = true; }
    }

    if (!$hasRefs) {
        $stmt = $connection->prepare('DELETE FROM books WHERE book_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
header('Location: librarian_books_list.php');
exit;
