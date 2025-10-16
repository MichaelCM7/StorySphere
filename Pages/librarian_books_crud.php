<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/Librarian/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Manage Books');
$template->hero('Manage Books');

$errors = [];
$success = '';

function sanitize($s) { return trim($s ?? ''); }

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

// Helpers for author/category resolution
function ensureAuthor(mysqli $connection, string $authorName): int {
    $authorId = 0;
    $stmt = $connection->prepare('SELECT author_id FROM authors WHERE author_name = ? LIMIT 1');
    $stmt->bind_param('s', $authorName);
    $stmt->execute();
    $stmt->bind_result($aid);
    if ($stmt->fetch()) { $authorId = (int)$aid; }
    $stmt->close();
    if ($authorId === 0) {
        $stmt = $connection->prepare('INSERT INTO authors (author_name) VALUES (?)');
        $stmt->bind_param('s', $authorName);
        $stmt->execute();
        $authorId = $stmt->insert_id;
        $stmt->close();
    }
    return $authorId;
}
function ensureCategory(mysqli $connection, string $categoryName): int {
    $categoryId = 0;
    $stmt = $connection->prepare('SELECT category_id FROM categories WHERE category_name = ? LIMIT 1');
    $stmt->bind_param('s', $categoryName);
    $stmt->execute();
    $stmt->bind_result($cid);
    if ($stmt->fetch()) { $categoryId = (int)$cid; }
    $stmt->close();
    if ($categoryId === 0) {
        $stmt = $connection->prepare('INSERT INTO categories (category_name) VALUES (?)');
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $categoryId = $stmt->insert_id;
        $stmt->close();
    }
    return $categoryId;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
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
                $authorId = ensureAuthor($connection, $authorName);
                $categoryId = ensureCategory($connection, $categoryName);

                if ($action === 'create') {
                    $sql = 'INSERT INTO books (isbn, title, author_id, publisher, published_date, page_count, category_id, language, total_copies, available_copies, shelf_location, book_condition)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
                    $stmt = $connection->prepare($sql);
                    $stmt->bind_param(
                        'ssisssiisiis',
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
                        $bookCondition
                    );
                } else { // update
                    if ($id <= 0) { throw new Exception('Invalid book ID for update.'); }
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
                }

                $stmt->execute();
                $stmt->close();
                $connection->commit();

                $success = $action === 'create' ? 'Book created successfully.' : 'Book updated successfully.';
                $action = 'list'; // return to list
            } catch (Throwable $e) {
                $connection->rollback();
                $errors[] = 'Save failed: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        if ($id > 0) {
            $hasRefs = false;
            $stmt = $connection->prepare('SELECT COUNT(*) FROM borrowing_records WHERE book_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($cnt1);
            $stmt->fetch();
            $stmt->close();
            if ($cnt1 > 0) { $hasRefs = true; }

            if (!$hasRefs) {
                $stmt = $connection->prepare('SELECT COUNT(*) FROM reservations WHERE book_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->bind_result($cnt2);
                $stmt->fetch();
                $stmt->close();
                if ($cnt2 > 0) { $hasRefs = true; }
            }

            if ($hasRefs) {
                $errors[] = 'Cannot delete book with existing borrowing or reservation records.';
            } else {
                $stmt = $connection->prepare('DELETE FROM books WHERE book_id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                $success = 'Book deleted successfully.';
            }
        }
        $action = 'list';
    }
}

// If editing, load book data
$editBook = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $connection->prepare('SELECT b.*, COALESCE(a.author_name, "") AS author_name, COALESCE(c.category_name, "") AS category_name FROM books b LEFT JOIN authors a ON a.author_id = b.author_id LEFT JOIN categories c ON c.category_id = b.category_id WHERE b.book_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editBook = $res->fetch_assoc() ?: [];
    $stmt->close();
    if (!$editBook) {
        $errors[] = 'Book not found for editing.';
        $action = 'list';
    }
}

// Messages
if ($success) {
    echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
}
if ($errors) {
    echo '<div class="alert alert-danger"><ul class="mb-0">';
    foreach ($errors as $err) echo '<li>' . htmlspecialchars($err) . '</li>';
    echo '</ul></div>';
}

// Render create/edit form
if ($action === 'create' || $action === 'edit') {
    $isEdit = $action === 'edit';
    ?>
    <div class="card card-modern mb-4">
        <div class="card-body">
            <h5 class="mb-3"><?= $isEdit ? 'Edit Book' : 'Add New Book' ?></h5>
            <form method="post">
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? ($editBook['title'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($_POST['isbn'] ?? ($editBook['isbn'] ?? '')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Language</label>
                        <input type="text" name="language" class="form-control" value="<?= htmlspecialchars($_POST['language'] ?? ($editBook['language'] ?? 'en')) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Author *</label>
                        <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($_POST['author_name'] ?? ($editBook['author_name'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category *</label>
                        <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($_POST['category_name'] ?? ($editBook['category_name'] ?? '')) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Publisher</label>
                        <input type="text" name="publisher" class="form-control" value="<?= htmlspecialchars($_POST['publisher'] ?? ($editBook['publisher'] ?? '')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Published Date</label>
                        <input type="date" name="published_date" class="form-control" value="<?= htmlspecialchars($_POST['published_date'] ?? ($editBook['published_date'] ?? '')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Page Count</label>
                        <input type="number" name="page_count" class="form-control" min="0" value="<?= htmlspecialchars($_POST['page_count'] ?? ($editBook['page_count'] ?? '0')) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Total Copies *</label>
                        <input type="number" name="total_copies" class="form-control" min="1" value="<?= htmlspecialchars($_POST['total_copies'] ?? ($editBook['total_copies'] ?? '1')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Available Copies *</label>
                        <input type="number" name="available_copies" class="form-control" min="0" value="<?= htmlspecialchars($_POST['available_copies'] ?? ($editBook['available_copies'] ?? ($_POST['total_copies'] ?? '1'))) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shelf Location</label>
                        <input type="text" name="shelf_location" class="form-control" value="<?= htmlspecialchars($_POST['shelf_location'] ?? ($editBook['shelf_location'] ?? '')) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Condition</label>
                        <select name="book_condition" class="form-select">
                            <?php $cond = $_POST['book_condition'] ?? ($editBook['book_condition'] ?? 'good'); $opts = ['excellent','good','fair','poor']; foreach ($opts as $o) { $sel = $o === $cond ? 'selected' : ''; echo "<option value=\"$o\" $sel>" . ucfirst($o) . "</option>"; } ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-modern"><i class="bi bi-save me-1"></i> Save</button>
                    <a class="btn btn-secondary btn-modern" href="librarian_books_crud.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
}

// List view
if ($action === 'list') {
    $search = trim($_GET['q'] ?? '');
    $sql = "SELECT b.book_id, b.isbn, b.title, COALESCE(a.author_name,'') AS author, COALESCE(c.category_name,'') AS category, b.total_copies, b.available_copies
            FROM books b
            LEFT JOIN authors a ON a.author_id = b.author_id
            LEFT JOIN categories c ON c.category_id = b.category_id";
    $params = [];
    $types = '';
    if ($search !== '') {
        $sql .= " WHERE b.title LIKE ? OR a.author_name LIKE ? OR b.isbn LIKE ?";
        $like = '%' . $search . '%';
        $params = [$like, $like, $like];
        $types = 'sss';
    }
    $sql .= " ORDER BY b.book_id DESC LIMIT 100";

    $data = [];
    if ($types) {
        $stmt = $connection->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) { $data[] = $row; }
        $stmt->close();
    } else {
        if ($res = $connection->query($sql)) {
            while ($row = $res->fetch_assoc()) { $data[] = $row; }
            $res->free();
        }
    }
    ?>
    <div class="card card-modern mb-3">
        <div class="card-body">
            <form class="row g-2" method="get">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Search title, author, ISBN" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-6 text-end">
                    <a href="librarian_books_crud.php?action=create" class="btn btn-success btn-modern"><i class="bi bi-plus-circle me-1"></i> Add Book</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td>
                            <span class="badge text-bg-<?= (int)$book['available_copies'] > 0 ? 'success' : 'danger' ?>">
                                <?= (int)$book['available_copies'] ?> / <?= (int)$book['total_copies'] ?>
                            </span>
                        </td>
                        <td class="d-flex gap-1">
                            <a class="btn btn-sm btn-primary" href="librarian_books_crud.php?action=edit&id=<?= (int)$book['book_id'] ?>"><i class="bi bi-pencil"></i></a>
                            <form method="post" onsubmit="return confirm('Delete this book?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$book['book_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

$template->footer($config);
