<?php
// Use a simple data provider pattern with graceful fallback to mock data.
// Later, swap the provider to a DB-backed function and keep the rest unchanged.
include 'mock_user_data.php';

// Placeholder for future backend integration. Replace the body of getAvailableBooks()
// to fetch from the database, keeping the return shape intact.
if (!function_exists('getAvailableBooks')) {
  function getAvailableBooks(): array {
    // TODO: Replace with DB query (e.g., SELECT title, author, category, image, status FROM books)
    // return fetchBooksFromDb();
    global $books; // fallback to mock data for now
    return $books ?? [];
  }
}

$availableBooks = getAvailableBooks();
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
      <div class="book-grid">
  <?php foreach($availableBooks as $book): ?>
          <div class="book-card">
            <img src="assets/images/<?= $book['image']; ?>" alt="">
            <span class="status <?= strtolower($book['status']); ?>"><?= $book['status']; ?></span>
            <h3><?= $book['title']; ?></h3>
            <p><?= $book['author']; ?></p>
            <small><?= $book['category']; ?></small>
            <button>View Details</button>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</body>
</html>