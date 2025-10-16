<?php
include '../Config/dbconnection.php';

// Helper: fallback to mock data when DB is unavailable or query fails
function getAvailableBooksMock(): array {
  $mock = [];
  $mockPath = __DIR__ . '/mock_user_data.php';
  if (file_exists($mockPath)) {
    include $mockPath; // expects $books
    if (!empty($books) && is_array($books)) {
      foreach ($books as $b) {
        $mock[] = [
          'title' => $b['title'] ?? 'Unknown',
          'author_name' => $b['author'] ?? 'Unknown',
          'publisher' => $b['publisher'] ?? 'N/A',
          'category_name' => $b['category'] ?? 'Uncategorized',
          'language' => $b['language'] ?? 'N/A',
          'available_copies' => $b['available_copies'] ?? 0,
          'total_copies' => $b['total_copies'] ?? 0,
          'book_condition' => $b['status'] ?? 'N/A',
        ];
      }
    }
  }
  return $mock;
}

if (!function_exists('getAvailableBooks')) {
  function getAvailableBooks($connection): array {
    // If DB connection variable isn't set or isn't mysqli, use mock
    if (!isset($connection) || !($connection instanceof mysqli)) {
      return getAvailableBooksMock();
    }

    $sql = "
      SELECT 
        b.book_id,
        b.title,
        b.publisher,
        b.published_date,
        b.language,
        b.total_copies,
        b.available_copies,
        b.book_condition,
        c.category_name,
        a.author_name
      FROM books b
      LEFT JOIN categories c ON b.category_id = c.category_id
      LEFT JOIN authors a ON b.author_id = a.author_id
      ORDER BY b.title ASC
    ";

    $result = $connection->query($sql);
    if ($result === false) {
      // Query failed (e.g., DB not selected) -> fallback to mock data
      return getAvailableBooksMock();
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    $result->free();
    return $rows;
  }
}

$availableBooks = getAvailableBooks($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Books | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <h1>Browse Books</h1>
      <input type="text" class="search" placeholder="Search by title, author, or category...">
      
      <?php if (empty($availableBooks)): ?>
        <p>No books available at the moment.</p>
      <?php else: ?>
        <div class="book-grid">
          <?php foreach($availableBooks as $book): ?>
            <div class="book-card">
              <h3><?= htmlspecialchars($book['title']); ?></h3>
              <p><strong>Author:</strong> <?= htmlspecialchars($book['author_name'] ?? 'Unknown'); ?></p>
              <p><strong>Publisher:</strong> <?= htmlspecialchars($book['publisher'] ?? 'N/A'); ?></p>
              <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></p>
              <p><strong>Language:</strong> <?= htmlspecialchars($book['language']); ?></p>
              <p><strong>Available Copies:</strong> <?= htmlspecialchars($book['available_copies']); ?> / <?= htmlspecialchars($book['total_copies']); ?></p>
              <p><strong>Condition:</strong> <?= htmlspecialchars($book['book_condition']); ?></p>
              <button>View Details</button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
