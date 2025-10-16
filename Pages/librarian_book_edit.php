<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/Librarian/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: librarian_books_list.php');
    exit;
}

$errors = [];
$success = '';

function sanitize($s) { return trim($s ?? ''); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = sanitize($_POST['isbn'] ?? '');
    $title = sanitize($_POST['title'] ?? '');
    $authorName = sanitize($_POST['author_name'] ?? '');
    $categoryName = sanitize($_POST['category_name'] ?? '');
    $publisher = sanitize($_POST['publisher'] ?? '');
    $publishedDate = sanitize($_POST['published_date'] ?? '');
    $pageCount = (int)($_POST['page_count'] ?? 0);
    $totalCopies = (int)($_POST['total_copies'] ?? 1);
    $availableCopies = (int)($_POST['available_copies'] ?? $totalCopies);
    $language = sanitize($_POST['language'] ?? 'en');
    $shelfLocation = sanitize($_POST['shelf_location'] ?? '');
    $bookCondition = sanitize($_POST['book_condition'] ?? 'good');

    if ($title === '') { $errors[] = 'Title is required.'; }
    if ($authorName === '') { $errors[] = 'Author is required.'; }
    if ($categoryName === '') { $errors[] = 'Category is required.'; }
    if ($totalCopies < 1) { $errors[] = 'Total copies must be at least 1.'; }
    if ($availableCopies < 0 || $availableCopies > $totalCopies) { $errors[] = 'Available copies must be between 0 and total copies.'; }

    if (!$errors) {
        $connection->begin_transaction();
        try {
            // Ensure author exists
            $authorId = null;
            $stmt = $connection->prepare('SELECT author_id FROM authors WHERE author_name = ? LIMIT 1');
            $stmt->bind_param('s', $authorName);
            $stmt->execute();
            $stmt->bind_result($authorIdRes);
            if ($stmt->fetch()) { $authorId = (int)$authorIdRes; }
            $stmt->close();
            if (!$authorId) {
                $stmt = $connection->prepare('INSERT INTO authors (author_name) VALUES (?)');
                $stmt->bind_param('s', $authorName);
                $stmt->execute();
                $authorId = $stmt->insert_id;
                $stmt->close();
            }

            // Ensure category exists
            $categoryId = null;
            $stmt = $connection->prepare('SELECT category_id FROM categories WHERE category_name = ? LIMIT 1');
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $stmt->bind_result($categoryIdRes);
            if ($stmt->fetch()) { $categoryId = (int)$categoryIdRes; }
            $stmt->close();
            if (!$categoryId) {
                $stmt = $connection->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->bind_param('s', $categoryName);
                $stmt->execute();
                $categoryId = $stmt->insert_id;
                $stmt->close();
            }

            // Update book
            $sql = 'UPDATE books SET isbn=?, title=?, author_id=?, publisher=?, published_date=?, page_count=?, category_id=?, language=?, total_copies=?, available_copies=?, shelf_location=?, book_condition=? WHERE book_id=?';
            $stmt = $connection->prepare($sql);
            $stmt->bind_param(
                'ssisssiisiisi',
                $isbn,
                $title,
                $authorId,
                $publisher,
                $publishedDate !== '' ? $publishedDate : null,
                $pageCount,
                $categoryId,
                $language,
                $totalCopies,
                $availableCopies,
                $shelfLocation,
                $bookCondition,
                $id
            );
            $stmt->execute();
            $stmt->close();

            $connection->commit();
            $success = 'Book updated successfully.';
        } catch (Throwable $e) {
            $connection->rollback();
            $errors[] = 'Failed to update book: ' . $e->getMessage();
        }
    }
}

// Load current book data
$stmt = $connection->prepare('SELECT b.*, COALESCE(a.author_name, "") AS author_name, COALESCE(c.category_name, "") AS category_name FROM books b LEFT JOIN authors a ON a.author_id = b.author_id LEFT JOIN categories c ON c.category_id = b.category_id WHERE b.book_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$book = $res->fetch_assoc();
$stmt->close();
if (!$book) {
    header('Location: librarian_books_list.php');
    exit;
}

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Edit Book');
$template->hero('Edit Book');
?>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card card-modern">
    <div class="card-body">
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? $book['title']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? $book['isbn']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Language</label>
                    <input type="text" name="language" class="form-control" value="<?= htmlspecialchars($_POST['language'] ?? $book['language']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Author *</label>
                    <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($_POST['author_name'] ?? $book['author_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category *</label>
                    <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($_POST['category_name'] ?? $book['category_name']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Publisher</label>
                    <input type="text" name="publisher" class="form-control" value="<?= htmlspecialchars($_POST['publisher'] ?? $book['publisher']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Published Date</label>
                    <input type="date" name="published_date" class="form-control" value="<?= htmlspecialchars($_POST['published_date'] ?? ($book['published_date'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Page Count</label>
                    <input type="number" name="page_count" class="form-control" min="0" value="<?= htmlspecialchars($_POST['page_count'] ?? ($book['page_count'] ?? '0')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Copies *</label>
                    <input type="number" name="total_copies" class="form-control" min="1" value="<?= htmlspecialchars($_POST['total_copies'] ?? $book['total_copies']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Available Copies *</label>
                    <input type="number" name="available_copies" class="form-control" min="0" value="<?= htmlspecialchars($_POST['available_copies'] ?? $book['available_copies']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shelf Location</label>
                    <input type="text" name="shelf_location" class="form-control" value="<?= htmlspecialchars($_POST['shelf_location'] ?? $book['shelf_location']) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Condition</label>
                    <?php $cond = $_POST['book_condition'] ?? $book['book_condition'] ?? 'good'; ?>
                    <select name="book_condition" class="form-select">
                        <?php $opts = ['excellent','good','fair','poor'];
                        foreach ($opts as $o) { $sel = $o === $cond ? 'selected' : ''; echo "<option value=\"$o\" $sel>" . ucfirst($o) . "</option>"; } ?>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-modern"><i class="bi bi-save me-1"></i> Save</button>
                <a class="btn btn-secondary btn-modern" href="librarian_books_list.php">Back</a>
            </div>
        </form>
    </div>
</div>

<?php $template->footer($config); ?>
