<?php include 'mock-data.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Books | LibraryHub</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <?php include 'includes/sidebar.php'; ?>
    <main>
      <h1>Browse Books</h1>
      <input type="text" class="search" placeholder="Search by title, author, or category...">
      <div class="book-grid">
        <?php foreach($books as $book): ?>
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