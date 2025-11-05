<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Borrowings');
$template->hero('Borrowings');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_borrowing') {
        $issueDate = trim($_POST['issue_date'] ?? '');

        if ($userId > 0 && $bookId > 0 && $issueDate !== '') {
            try {
                $dueDate = date('Y-m-d', strtotime($issueDate . ' + 14 days'));
                $stmt = $connection->prepare('INSERT INTO borrowing_records (user_id, book_id, issue_date, due_date, book_status_id) VALUES (?, ?, ?, ?, 1)');
                $stmt->bind_param('iiss', $userId, $bookId, $issueDate, $dueDate);
                $stmt->execute();
                $stmt->close();

                $updateStmt = $connection->prepare('UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?');
                $updateStmt->bind_param('i', $bookId);
                $updateStmt->execute();
                $updateStmt->close();

                $success = 'Borrowing record added successfully.';
            } catch (Throwable $e) {
                $error = 'Failed to add borrowing record: ' . $e->getMessage();
            }
        } else {
            $error = 'Please fill all the fields.';
        }
    } elseif ($action === 'extend_due_date') {
        $borrowingId = (int)($_POST['extend_borrowing_id'] ?? 0);

        if ($borrowingId > 0) {
            try {
                $stmt = $connection->prepare('UPDATE borrowing_records SET due_date = DATE_ADD(due_date, INTERVAL 7 DAY) WHERE borrowing_id = ?');
                $stmt->bind_param('i', $borrowingId);
                $stmt->execute();
                $stmt->close();

                $success = 'Due date extended successfully.';
            } catch (Throwable $e) {
                $error = 'Failed to extend due date: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid borrowing ID.';
        }
    }
}

// Fetch members for the create fine form
$members = [];
$sql = "SELECT user_id, CONCAT(first_name, ' ', last_name) AS member_name FROM users WHERE role_id = 3 AND is_deleted = 0 ORDER BY first_name, last_name";
if ($res = $connection->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $members[] = $row;
    }
    $res->free();
}

// Fetch all books for the create fine form
$allBooks = [];
$sql_books = "SELECT book_id, title FROM books WHERE is_deleted = 0 AND available_copies > 0 ORDER BY title";
if ($res = $connection->query($sql_books)) {
    while ($row = $res->fetch_assoc()) {
        $allBooks[] = $row;
    }
    $res->free();
}

?>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-modern mb-4">
    <div class="card-body">
        <h5 class="mb-3">Add New Borrowing Record</h5>
        <form method="post">
            <input type="hidden" name="action" value="add_borrowing">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Select Member</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Choose a member --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int)$member['user_id'] ?>"><?= htmlspecialchars($member['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Select Book</label>
                    <select name="book_id" class="form-select" required>
                        <option value="">-- Select a book --</option>
                        <?php foreach ($allBooks as $book): ?>
                            <option value="<?= (int)$book['book_id'] ?>"><?= htmlspecialchars($book['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-modern mt-3"><i class="bi bi-plus-circle me-1"></i> Add Borrowing</button>
        </form>
    </div>
</div>

<?php
$section = new BorrowingsSection();
echo '<div class="card card-modern"><div class="card-body">';
echo $section->renderContent();
echo '</div></div>';

$template->footer($config);
?>
