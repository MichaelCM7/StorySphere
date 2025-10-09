<?php include 'mock-data.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fines | LibraryHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <?php include 'includes/sidebar.php'; ?>
    <main>
      <h1>Outstanding Fines</h1>
      <p class="subtitle">Review your fines and clear your overdue items</p>

      <div class="fines-summary">
        <h2>Total Due: <span>$<?= $user['fines'] ?></span></h2>
        <button class="pay-btn">Pay All Fines</button>
      </div>

      <div class="fine-list">
        <div class="fine-item">
          <div>
            <h3>1984</h3>
            <p>Overdue by 5 days</p>
          </div>
          <span class="fine-amount">$4.00</span>
        </div>
        <div class="fine-item">
          <div>
            <h3>The Great Gatsby</h3>
            <p>Overdue by 3 days</p>
          </div>
          <span class="fine-amount">$2.50</span>
        </div>
        <div class="fine-item">
          <div>
            <h3>To Kill a Mockingbird</h3>
            <p>Lost book replacement fee</p>
          </div>
          <span class="fine-amount">$6.00</span>
        </div>
      </div>
    </main>
  </div>
</body>
</html>