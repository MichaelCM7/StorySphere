<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/Librarian/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Books');
$template->hero('Books');

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
                <a href="librarian_book_create.php" class="btn btn-success btn-modern"><i class="bi bi-plus-circle me-1"></i> Add Book</a>
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
                        <a class="btn btn-sm btn-primary" href="librarian_book_edit.php?id=<?= (int)$book['book_id'] ?>"><i class="bi bi-pencil"></i></a>
                        <a class="btn btn-sm btn-danger" href="librarian_book_delete.php?id=<?= (int)$book['book_id'] ?>" onclick="return confirm('Delete this book?');"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $template->footer($config); ?>
