<?php include 'mock_user_data.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>

      <div class="stats">
        <div class="card"><h2><?= $user['borrowed_books'] ?></h2><p>Borrowed Books</p></div>
        <div class="card"><h2><?= $user['pending_returns'] ?></h2><p>Pending Returns</p></div>
        <div class="card"><h2>$<?= $user['fines'] ?></h2><p>Outstanding Fines</p></div>
        <div class="card"><h2><?= $user['books_read'] ?></h2><p>Books Read</p></div>
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