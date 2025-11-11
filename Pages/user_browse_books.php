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

// If no local results and the user searched, try Google Books as a fallback
if (empty($availableBooks) && $q !== '') {
  $gbPath = __DIR__ . '/../Utils/GoogleBooks.php';
  if (file_exists($gbPath)) {
    include_once $gbPath;
    // Use API key from Secure/secureInfo.php if available (constants.php already includes secureInfo)
    $apiKey = $google_books_api_key ?? null;
    $googleResults = GoogleBooksClient::search($q, $apiKey, 12);
    if (!empty($googleResults)) {
      $availableBooks = $googleResults;
    }
  }
}
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
      
      <div id="resultsArea">
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
              <?php if (!empty($book['thumbnail'])): ?>
                <div class="book-thumb">
                  <img src="<?= htmlspecialchars($book['thumbnail']) ?>" alt="<?= htmlspecialchars($book['title']) ?> cover" />
                </div>
              <?php endif; ?>
              <div class="book-meta">
                <h3><?= htmlspecialchars($book['title']); ?> <?php if (($book['source'] ?? '') === 'google'): ?><small class="external-badge">External</small><?php endif; ?></h3>
                <p><strong>Author:</strong> <?= htmlspecialchars($book['author_name'] ?? 'Unknown'); ?></p>
                <p><strong>Publisher:</strong> <?= htmlspecialchars($book['publisher'] ?? 'N/A'); ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></p>
                <p><strong>Language:</strong> <?= htmlspecialchars($book['language']); ?></p>
                <p><strong>Available Copies:</strong> <?= htmlspecialchars($book['available_copies']); ?> / <?= htmlspecialchars($book['total_copies']); ?></p>
                <p><strong>Condition:</strong> <?= htmlspecialchars($book['book_condition']); ?></p>
                <?php if (!empty($book['description'])): ?>
                  <p class="book-desc"><?= htmlspecialchars(mb_strimwidth($book['description'], 0, 250, '...')); ?></p>
                <?php endif; ?>
                        <div class="book-actions">
                          <?php if (!empty($book['preview_link'])): ?>
                            <a href="<?= htmlspecialchars($book['preview_link']) ?>" target="_blank" rel="noopener">Preview</a>
                          <?php endif; ?>
                          <?php
                            // Show import button for Google-sourced entries when user is admin or librarian
                            $canImport = false;
                            if (session_status() === PHP_SESSION_NONE) session_start();
                            $role_id = $_SESSION['role_id'] ?? null;
                            if (in_array($role_id, [1,2], true) && ($book['source'] ?? '') === 'google') {
                              $canImport = true;
                            }
                          ?>
                          <?php if ($canImport): ?>
                            <button class="import-btn" data-book='<?= json_encode(array_intersect_key($book, array_flip(["title","author_name","isbn","google_id","publisher","published_date","page_count","description","thumbnail","category_name","language"]))) ?>'>Import</button>
                          <?php endif; ?>
                          <button>View Details</button>
                        </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      </div>
    </main>
  </div>
</body>
<script>
  (function() {
    const input = document.querySelector('input[name="q"]');
    if (!input) return;
    let t;
      // Shared search function used by both live input (debounced) and Enter key
      let currentController = null;
      const MIN_SEARCH_CHARS = 2;

      async function performSearchForValue(val) {
        const url = new URL(window.location.href);
        const resultsContainer = document.querySelector('#resultsArea');
        if (!resultsContainer) return;

        // If query is short, show a hint and skip network fetch
        if (val.length > 0 && val.length < MIN_SEARCH_CHARS) {
          resultsContainer.innerHTML = '<p class="search-hint">Type ' + MIN_SEARCH_CHARS + '+ characters to search</p>';
          history.replaceState({}, '', url.toString());
          return;
        }

        // Abort previous request if any
        if (currentController) {
          try { currentController.abort(); } catch(_) {}
        }
        currentController = new AbortController();
        const signal = currentController.signal;

        // Show loading state
        resultsContainer.innerHTML = '<p class="loading">Searching...</p>';

        if (val) {
          url.searchParams.set('q', val);
        } else {
          url.searchParams.delete('q');
        }
        if (val) {
          url.searchParams.set('q', val);
        } else {
          url.searchParams.delete('q');
        }
        try {
          const res = await fetch(url.toString(), { cache: 'no-store', signal });
          if (!res.ok) {
            window.location.assign(url.toString());
            return;
          }
          const text = await res.text();
          const parser = new DOMParser();
          const doc = parser.parseFromString(text, 'text/html');
          const newResults = doc.querySelector('#resultsArea');
          const cur = document.querySelector('#resultsArea');
          if (newResults && cur) {
            cur.innerHTML = newResults.innerHTML;
            history.replaceState({}, '', url.toString());
          } else {
            window.location.assign(url.toString());
          }
        } catch (err) {
          if (err.name === 'AbortError') {
            // expected when a newer request starts; do nothing
          } else {
            window.location.assign(url.toString());
          }
        }
      }

      input.addEventListener('input', function() {
        clearTimeout(t);
        t = setTimeout(() => {
          const val = input.value.trim();
          performSearchForValue(val);
        }, 300);
      });

      // Also trigger search immediately on Enter key (keypress/keydown handler)
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(t);
          const val = input.value.trim();
          performSearchForValue(val);
        }
      });
  })();

  // Import handler for Google-sourced items
  (function(){
    document.addEventListener('click', async function(e){
      const btn = e.target.closest('.import-btn');
      if (!btn) return;
      e.preventDefault();
      let payload;
      try {
        payload = JSON.parse(btn.getAttribute('data-book'));
      } catch(err){
        alert('Invalid import data');
        return;
      }
      if (!payload || !payload.title) {
        alert('Missing title');
        return;
      }
      btn.disabled = true;
      const originalText = btn.textContent;
      btn.textContent = 'Importing...';
      try {
        const resp = await fetch('../Pages/import_google_book.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await resp.json();
        if (json.success) {
          alert('Imported: book_id=' + json.book_id);
          btn.remove();
        } else {
          alert('Import failed: ' + (json.message || 'unknown'));
          btn.disabled = false;
          btn.textContent = originalText;
        }
      } catch(err){
        alert('Import error: ' + err.message);
        btn.disabled = false;
        btn.textContent = originalText;
      }
    });
  })();
</script>
</html>
