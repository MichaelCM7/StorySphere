<?php
include 'mock_user_data.php';

// Future backend hook: replace getUserDashboardStats() to fetch from DB.
if (!function_exists('getUserDashboardStats')) {
  function getUserDashboardStats(): array {
    global $user; // fallback to mock data
    return [
      'borrowed_books' => $user['borrowed_books'] ?? 0,
      'pending_returns' => $user['pending_returns'] ?? 0,
      'fines' => $user['fines'] ?? 0,
      'books_read' => $user['books_read'] ?? 0,
      'name' => $user['name'] ?? 'Reader',
    ];
  }
}

$stats = getUserDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>

      <div class="stats">
  <div class="card"><h2><?= $stats['borrowed_books'] ?></h2><p>Borrowed Books</p></div>
  <div class="card"><h2><?= $stats['pending_returns'] ?></h2><p>Pending Returns</p></div>
  <div class="card"><h2>$<?= $stats['fines'] ?></h2><p>Outstanding Fines</p></div>
  <div class="card"><h2><?= $stats['books_read'] ?></h2><p>Books Read</p></div>
      </div>

      <section class="activity">
        <h3>Recent Activity</h3>
        <?php foreach($recent_activity as $act): ?>
          <div class="activity-item">
            <strong><?= $act['action']; ?>:</strong> <?= $act['book']; ?> 
            <span><?= $act['time']; ?></span>
          </div>
        <?php endforeach; ?>
      </section>
    </main>
  </div>
</body>
</html>