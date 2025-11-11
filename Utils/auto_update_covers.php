<?php
/**
 * Auto-update cover images for books missing them
 * This can be called:
 * 1. Via cron/scheduled task
 * 2. After adding new books
 * 3. On-demand from admin panel
 */

// Silent mode - no output
$silent = isset($argv[1]) && $argv[1] === '--silent';

// Set timeout
set_time_limit(300);

// Navigate to Config directory
chdir(__DIR__ . '/../Config');
require_once __DIR__ . '/../Config/dbconnection.php';
require_once __DIR__ . '/../Utils/GoogleBooks.php';

// Get books without covers
$stmt = $connection->prepare("
    SELECT book_id, title, isbn, publisher, description, page_count
    FROM books 
    WHERE (cover_image_url IS NULL OR cover_image_url = '') 
    AND title IS NOT NULL 
    ORDER BY book_id ASC 
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();

$updated = 0;

while ($book = $result->fetch_assoc()) {
    $bookId = $book['book_id'];
    $title = $book['title'];
    $isbn = $book['isbn'];
    
    $coverUrl = null;
    $publisher = $book['publisher'] ?? '';
    $description = $book['description'] ?? '';
    $pageCount = (int)($book['page_count'] ?? 0);
    $metaNeedsUpdate = ($publisher === '' || $description === '' || $pageCount === 0);
    
    // Try ISBN first
    if (!empty($isbn)) {
        try {
            $bookData = GoogleBooksClient::getByIsbn($isbn);
            if ($bookData) {
                if (!empty($bookData['thumbnail'])) {
                    $coverUrl = $bookData['thumbnail'];
                }
                if ($metaNeedsUpdate) {
                    $publisher = $publisher !== '' ? $publisher : ($bookData['publisher'] ?? $publisher);
                    $description = $description !== '' ? $description : ($bookData['description'] ?? $description);
                    $pageCount = $pageCount > 0 ? $pageCount : (int)($bookData['page_count'] ?? $pageCount);
                }
            }
        } catch (Exception $e) {
            // Silent fail
        }
    }
    
    // Try title if ISBN didn't work
    if (!$coverUrl && !empty($title)) {
        try {
            $results = GoogleBooksClient::search($title, null, 1);
            if (!empty($results[0])) {
                $g = $results[0];
                if (!empty($g['thumbnail'])) {
                    $coverUrl = $g['thumbnail'];
                }
                if ($metaNeedsUpdate) {
                    $publisher = $publisher !== '' ? $publisher : ($g['publisher'] ?? $publisher);
                    $description = $description !== '' ? $description : ($g['description'] ?? $description);
                    $pageCount = $pageCount > 0 ? $pageCount : (int)($g['page_count'] ?? $pageCount);
                }
            }
        } catch (Exception $e) {
            // Silent fail
        }
    }
    
    // Update if we found any enrichment (cover or metadata)
    if ($coverUrl || $metaNeedsUpdate) {
        $safeCover = $coverUrl ?? '';
        $safePublisher = $publisher ?? '';
        $safeDescription = $description ?? '';
        $pc = (int)$pageCount;

        $sql = "UPDATE books
                SET cover_image_url = COALESCE(NULLIF(?, ''), cover_image_url),
                    publisher = COALESCE(NULLIF(?, ''), publisher),
                    description = COALESCE(NULLIF(?, ''), description),
                    page_count = CASE WHEN ? > 0 THEN ? ELSE page_count END
                WHERE book_id = ?";
        $update = $connection->prepare($sql);
        $update->bind_param('sssiii', $safeCover, $safePublisher, $safeDescription, $pc, $pc, $bookId);
        if ($update->execute()) {
            $updated++;
            if (!$silent) {
                echo "Enriched book #{$bookId}: {$title}\n";
            }
        }
        $update->close();
    }
    
    // Small delay to be nice to API
    usleep(200000); // 200ms
}

$stmt->close();
$connection->close();

if (!$silent) {
    echo "\nDone! Updated {$updated} book(s).\n";
}

// Return count for external scripts
exit($updated);
