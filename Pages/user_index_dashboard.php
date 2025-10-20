<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

include '../Components/auth_guard.php';
include 'mock_user_data.php';

// Future backend hook: replace getUserDashboardStats() to fetch from DB.
if (!function_exists('getUserDashboardStats')) {
  function getUserDashboardStats(): array {
    // Use mock stats only, never override $user from auth_guard
    global $mock_user;
    return [
      'borrowed_books' => $mock_user['borrowed_books'] ?? 0,
      'pending_returns' => $mock_user['pending_returns'] ?? 0,
      'fines' => $mock_user['fines'] ?? 0,
      'books_read' => $mock_user['books_read'] ?? 0,
      // Display name should come from DB user via header, not here
      'name' => $mock_user['name'] ?? 'Reader',
    ];
  }
}

$stats = getUserDashboardStats();

// If a ?uid is provided in the URL, use it; otherwise use the logged-in user id
$target_uid = isset($_GET['uid']) && ctype_digit((string)$_GET['uid']) ? (int)$_GET['uid'] : ($user['id'] ?? null);
if ($target_uid !== null && isset($connection) && $connection instanceof mysqli) {
    $stmt = $connection->prepare("SELECT COALESCE(SUM(fine_amount),0) AS total FROM fines WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $target_uid);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            if ($res) { $res->free(); }
            if (isset($row['total'])) {
                $stats['fines'] = (float)$row['total'];
            }
        }
        $stmt->close();
    }
}
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
  <?php foreach(($recent_activity ?? []) as $act): ?>
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