<?php
include 'mock_user_data.php';

// Future backend hook: replace getUserFinesSummary() and getUserFinesList() with DB queries.
if (!function_exists('getUserFinesSummary')) {
  function getUserFinesSummary(): array {
    global $user; // fallback to mock data
    return ['total' => $user['fines'] ?? 0];
  }
}

if (!function_exists('getUserFinesList')) {
  function getUserFinesList(): array {
    // For now, use inline mock items already in the template or extend mock_user_data.php later
    return [
      ['title' => '1984', 'note' => 'Overdue by 5 days', 'amount' => 4.00],
      ['title' => 'The Great Gatsby', 'note' => 'Overdue by 3 days', 'amount' => 2.50],
      ['title' => 'To Kill a Mockingbird', 'note' => 'Lost book replacement fee', 'amount' => 6.00],
    ];
  }
}

$finesSummary = getUserFinesSummary();
$fines = getUserFinesList();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fines | StorySphere</title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <h1>Outstanding Fines</h1>
      <p class="subtitle">Review your fines and clear your overdue items</p>

      <div class="fines-summary">
  <h2>Total Due: <span>$<?= number_format($finesSummary['total'], 2) ?></span></h2>
        <button class="pay-btn">Pay All Fines</button>
      </div>

      <div class="fine-list">
        <?php foreach ($fines as $f): ?>
          <div class="fine-item">
            <div>
              <h3><?= htmlspecialchars($f['title']) ?></h3>
              <p><?= htmlspecialchars($f['note']) ?></p>
            </div>
            <span class="fine-amount">$<?= number_format($f['amount'], 2) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</body>
</html>