<?php
include '../Components/auth_guard.php';
include 'mock_user_data.php';

// Future backend hook: replace getBorrowedBooks() body with DB query.
if (!function_exists('getBorrowedBooks')) {
  function getBorrowedBooks(): array {
    global $borrowed_books; // fallback to mock data
    return $borrowed_books ?? [];
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