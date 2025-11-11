<?php
include '../Config/dbconnection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$gid = isset($_GET['gid']) ? trim((string)$_GET['gid']) : '';
$isExternal = false;
$book = null;

if ($id > 0 && isset($connection) && ($connection instanceof mysqli)) {
    $sql = "
      SELECT 
        b.book_id,
        b.isbn,
        b.google_books_id,
        b.title,
        a.author_name,
        c.category_name,
        b.publisher,
        b.published_date,
        b.page_count,
        b.description,
        b.cover_image_url,
        b.language,
        b.total_copies,
        b.available_copies,
        b.shelf_location,
        b.book_condition
      FROM books b
      LEFT JOIN authors a ON b.author_id = a.author_id
      LEFT JOIN categories c ON b.category_id = c.category_id
      WHERE b.book_id = ?
      LIMIT 1
    ";
    $stmt = $connection->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res) {
                $book = $res->fetch_assoc();
                $res->free();
            }
        }
        $stmt->close();
    }
} elseif ($gid !== '') {
    // External (Google) detail view
    $isExternal = true;
    $gbPath = __DIR__ . '/../Utils/GoogleBooks.php';
    include_once $gbPath;
    // Try to load API key (optional)
    $apiKey = $google_books_api_key ?? null;
    if (!isset($google_books_api_key)) {
        $sec = __DIR__ . '/../Secure/secureInfo.php';
        if (file_exists($sec)) include_once $sec;
        $apiKey = $google_books_api_key ?? null;
    }
    $book = GoogleBooksClient::getByGoogleId($gid, $apiKey);
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $book ? h($book['title']) . ' | Book Details' : 'Book Details' ?></title>
  <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
  <style>
    .details-page { max-width: 1000px; margin: 0 auto; padding: 16px; }
    .details-header { display:flex; align-items:center; gap:12px; }
    .external-badge { font-size:12px; background:#eef; color:#224; padding:2px 6px; border-radius:4px; }
    .details-wrap { display:flex; gap:24px; flex-wrap:wrap; margin-top:16px; }
    .cover-pane { flex:0 0 260px; }
    .cover-pane img { max-width:100%; height:auto; border:1px solid #ddd; box-shadow:0 2px 6px rgba(0,0,0,0.15); }
    .meta-pane { flex:1 1 300px; }
    .meta-pane p { margin:4px 0; }
    .description { margin-top:14px; background:#fafafa; border:1px solid #eee; padding:12px; border-radius:6px; white-space:pre-line; }
    .back-link { margin-top:16px; display:inline-block; }
  </style>
  
</head>
<body>
  <div class="container">
    <?php include '../Components/user_navbar.php'; ?>
    <main>
      <?php include '../Components/user_header.php'; ?>
      <div class="details-page">
        <a class="back-link" href="user_browse_books.php">&larr; Back to Browse</a>

        <?php if (!$book): ?>
          <h1>Book not found</h1>
          <p>We couldn't find details for this book. Please go back and try again.</p>
        <?php else: ?>
          <div class="details-header">
            <h1><?= h($book['title'] ?? 'Untitled'); ?></h1>
            <?php if ($isExternal): ?><span class="external-badge">External</span><?php endif; ?>
          </div>
          <div class="details-wrap">
            <div class="cover-pane">
              <?php 
                $img = $book['cover_image_url'] ?? ($book['thumbnail'] ?? '');
                if ($img): ?>
                <img src="<?= h($img) ?>" alt="<?= h($book['title'] ?? 'Cover') ?>" />
              <?php endif; ?>
            </div>
            <div class="meta-pane">
              <p><strong>Author:</strong> <?= h($book['author_name'] ?? 'Unknown'); ?></p>
              <p><strong>Publisher:</strong> <?= h($book['publisher'] ?? 'N/A'); ?></p>
              <p><strong>Category:</strong> <?= h($book['category_name'] ?? 'Uncategorized'); ?></p>
              <p><strong>Language:</strong> <?= h($book['language'] ?? 'N/A'); ?></p>
              <?php if (!empty($book['page_count'])): ?><p><strong>Pages:</strong> <?= h($book['page_count']); ?></p><?php endif; ?>
              <?php if (!$isExternal): ?>
                <p><strong>Copies:</strong> <?= h($book['available_copies'] ?? '0'); ?> / <?= h($book['total_copies'] ?? '0'); ?></p>
                <p><strong>Condition:</strong> <?= h($book['book_condition'] ?? 'N/A'); ?></p>
                <?php if (!empty($book['shelf_location'])): ?><p><strong>Shelf:</strong> <?= h($book['shelf_location']); ?></p><?php endif; ?>
              <?php else: ?>
                <?php if (!empty($book['preview_link'])): ?>
                  <p><a href="<?= h($book['preview_link']) ?>" target="_blank" rel="noopener">Preview on Google Books</a></p>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($book['description'])): ?>
            <div class="description">
              <h3>Description</h3>
              <p><?= h($book['description']); ?></p>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
