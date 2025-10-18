

<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Bootstrap session/user and DB
include '../Components/auth_guard.php';

// Current user id from guard
$user_id = (int) $user['id'];

// Fetch fines list for a user (mysqli)
if (!function_exists('getUserFines')) {
  function getUserFines($connection, int $user_id): array {
    if (!$connection || !($connection instanceof mysqli)) {
      return [];
    }
  $sql = "SELECT fine_reason, fine_amount FROM fines WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) return [];
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) { $stmt->close(); return []; }
    $result = $stmt->get_result();
  $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    if ($result) { $result->free(); }
    $stmt->close();
    return $rows;
  }
}

// Fetch total fines amount for a user (mysqli)
if (!function_exists('getUserTotalFines')) {
  function getUserTotalFines($connection, int $user_id): float {
    if (!$connection || !($connection instanceof mysqli)) {
      return 0.0;
    }
  $sql = "SELECT COALESCE(SUM(fine_amount),0) AS total FROM fines WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) return 0.0;
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) { $stmt->close(); return 0.0; }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    if ($result) { $result->free(); }
    $stmt->close();
    return isset($row['total']) ? (float)$row['total'] : 0.0;
  }
}

$fines = getUserFines($connection, $user_id);
$total = getUserTotalFines($connection, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fines | StorySphere</title>

  <!-- Include CSS -->
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
  <link rel="stylesheet" href="../Datatables/2.1.4.css">
  <link rel="stylesheet" href="../Datatables/3.1.1.css">
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <h1>Outstanding Fines</h1>
      <p class="subtitle">Review your fines and clear overdue items</p>

      <div class="fines-summary">
        <h2>Total Due: <span>$<?= number_format($total, 2) ?></span></h2>
        <button class="pay-btn">Pay All Fines</button>
      </div>

      <table id="finesTable" class="display">
        <thead>
          <tr>
            <th>Reason</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($fines)): ?>
            <?php foreach ($fines as $f): ?>
              <tr>
                <td><?= htmlspecialchars($f['fine_reason']); ?></td>
                <td>$<?= number_format((float)$f['fine_amount'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </main>
  </div>

  <!-- JS for DataTables -->
  <script src="../Datatables/3.7.1.js"></script>
  <script src="../Datatables/2.1.4.js"></script>
  <!-- DataTables Buttons dependencies -->
  <script src="../Datatables/dependancy1.js"></script> <!-- dependancy1.js -->
  <script src="../Datatables/dependancy2.js"></script> <!-- dependancy2.js -->
  <script src="../Datatables/dependancy3.js"></script> <!-- dependancy3.js -->
  <script src="../Datatables/dependancy4.js"></script> <!-- dependancy4.js -->
  <script src="../Datatables/dependancy5.js"></script> <!-- dependancy5.js -->
  <script src="../Datatables/dependacy6.js"></script> <!-- dependacy6.js -->
  <script>
    $(document).ready(function() {
      $('#finesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
          { extend: 'copyHtml5', title: 'Fines' },
          { extend: 'csvHtml5', title: 'Fines' },
          { extend: 'excelHtml5', title: 'Fines' },
          { extend: 'pdfHtml5', title: 'Fines' },
          { extend: 'print', title: 'Fines' }
        ],
        pageLength: 5,
        lengthChange: true,
        ordering: true,
        searching: true,
        language: {
          emptyTable: 'No fines found for your account.'
        }
      });
    });
  </script>
</body>
</html>