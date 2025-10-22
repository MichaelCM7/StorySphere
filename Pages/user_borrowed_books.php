<?php


ini_set('display_errors', '1');
error_reporting(E_ALL);
include '../Components/auth_guard.php';
//include 'mock_user_data.php';
require_once __DIR__ . '/../Config/dbconnection.php';

//Replace getBorrowedBooks() body with DB query.
if (!function_exists('getBorrowedBooks')) {
  function getBorrowedBooks(): array {
    global $connection; // Database connection
    
    // Get the logged-in user's ID from session
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        return [];
    }
    
    try {
        $sql = "SELECT 
                    b.title,
                    b.author_id,
                    a.author_name as author,
                    br.due_date,
                    bs.status_name as status
                FROM borrowing_records br
                INNER JOIN books b ON br.book_id = b.book_id
                LEFT JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN book_statuses bs ON br.book_status_id = bs.book_status_id
                WHERE br.user_id = ? 
                AND br.is_deleted = 0
                AND br.return_date IS NULL
                ORDER BY br.issue_date DESC";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $borrowed_books = [];
        while ($row = $result->fetch_assoc()) {
            // Map database status names to display format
            $status = $row['status'];
            if ($status === 'Currently Borrowed') {
                $status = 'Active';
            }
            $borrowed_books[] = [
                'title' => htmlspecialchars($row['title']),
                'author' => htmlspecialchars($row['author'] ?? 'Unknown Author'),
                'due' => date('M d, Y', strtotime($row['due_date'])),
                'status' => $status
            ];
        }
        
        $stmt->close();
        return $borrowed_books;
        
    } catch (Exception $e) {
        error_log("Error fetching borrowed books: " . $e->getMessage());
        return [];
    }
  }
}

$borrowed = getBorrowedBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrowed Books | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <h1>Borrowed Books</h1>
      <div class="borrowed-list">
  <?php foreach($borrowed as $b): ?>
          <div class="borrow-item">
            <div>
              <h3><?= $b['title']; ?></h3>
              <p><?= $b['author']; ?></p>
              <small>Due: <?= $b['due']; ?></small>
            </div>
            <span class="badge <?= strtolower(str_replace(' ', '-', $b['status'])); ?>"><?= $b['status']; ?></span>
            <button>Return Book</button>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</body>
</html>