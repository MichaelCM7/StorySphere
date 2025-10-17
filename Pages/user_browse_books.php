<?php
include '../Config/dbconnection.php';

// Capture search query from URL (GET)
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

// Helper: fallback to mock data when DB is unavailable or query fails
function getAvailableBooksMock(string $q = ''): array {
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
  // If searching, filter the mock results in-memory
  if ($q !== '') {
    $qLower = strtolower($q);
    $mock = array_values(array_filter($mock, function ($row) use ($qLower) {
      $fields = [
        $row['title'] ?? '',
        $row['author_name'] ?? '',
        $row['category_name'] ?? '',
        $row['publisher'] ?? '',
        $row['language'] ?? '',
      ];
      foreach ($fields as $f) {
        if (stripos((string)$f, $qLower) !== false) {
          return true;
        }
      }
      return false;
    }));
  }
  return $mock;
}

if (!function_exists('getAvailableBooks')) {
  function getAvailableBooks($connection, string $q = ''): array {
    // If DB connection variable isn't set or isn't mysqli, use mock
    if (!isset($connection) || !($connection instanceof mysqli)) {
      return getAvailableBooksMock($q);
    }

    $baseSql = "
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
    ";

    $where = '';
    $types = '';
    $params = [];

    if ($q !== '') {
      $where = " WHERE (
        b.title LIKE ? OR 
        a.author_name LIKE ? OR 
        c.category_name LIKE ? OR 
        b.publisher LIKE ? OR 
        b.language LIKE ?
      )";
      $pattern = '%' . $q . '%';
      $types = 'sssss';
      $params = [$pattern, $pattern, $pattern, $pattern, $pattern];
    }

    $order = ' ORDER BY b.title ASC';
    $sql = $baseSql . $where . $order;

    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
      // Prepare failed -> fallback to mock data
      return getAvailableBooksMock($q);
    }

    if (!empty($params)) {
      // bind_param requires references for call_user_func_array
      $bindParams = [];
      $bindParams[] = & $types;
      foreach ($params as $k => $v) {
        $bindParams[] = & $params[$k];
      }
      call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }

    if (!$stmt->execute()) {
      $stmt->close();
      return getAvailableBooksMock($q);
    }

    $result = $stmt->get_result();
    if ($result === false) {
      // get_result unavailable or failed; try manual bind_result as a fallback
      $meta = $stmt->result_metadata();
      if ($meta) {
        $fields = [];
        $row = [];
        while ($field = $meta->fetch_field()) {
          $fields[$field->name] = null;
          $row[$field->name] = & $fields[$field->name];
        }
        $meta->free();
        call_user_func_array([$stmt, 'bind_result'], array_values($row));
        $rows = [];
        while ($stmt->fetch()) {
          $rows[] = $fields;
        }
        $stmt->close();
        return $rows;
      }
      $stmt->close();
      return getAvailableBooksMock($q);
    }

    $rows = $result->fetch_all(MYSQLI_ASSOC) ?: [];
    $result->free();
    $stmt->close();
    return $rows;
  }
}

$availableBooks = getAvailableBooks($connection, $q);
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
      <form method="get" action="">
        <input type="text" name="q" class="search" placeholder="Search by title, author, or category..." value="<?= htmlspecialchars($q) ?>" />
        <button type="submit" style="display:none">Search</button>
      </form>
      
      <?php if (empty($availableBooks)): ?>
        <?php if ($q !== ''): ?>
          <p>No books match "<?= htmlspecialchars($q) ?>".</p>
        <?php else: ?>
          <p>No books available at the moment.</p>
        <?php endif; ?>
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
<script>
  (function() {
    const input = document.querySelector('input[name="q"]');
    if (!input) return;
    let t;
    input.addEventListener('input', function() {
      clearTimeout(t);
      t = setTimeout(() => {
        const form = input.form;
        if (!form) return;
        const url = new URL(window.location.href);
        const val = input.value.trim();
        if (val) {
          url.searchParams.set('q', val);
        } else {
          url.searchParams.delete('q');
        }
        window.location.assign(url.toString());
      }, 300);
    });
  })();
</script>
</html>
