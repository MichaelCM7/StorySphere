<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Fines');
$template->hero('Fines');

$tab = $_GET['tab'] ?? 'unpaid';
$allowedTabs = ['unpaid','paid','waived'];
if (!in_array($tab, $allowedTabs, true)) { $tab = 'unpaid'; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $fineId = (int)$_POST['fine_id'];
    $action = $_POST['action'];
    if ($fineId > 0 && in_array($action, ['mark_paid','mark_waived'], true)) {
        try {
            $status = $action === 'mark_paid' ? 'paid' : 'waived';
            $stmt = $connection->prepare('UPDATE fines SET payment_status = ? WHERE fine_id = ?');
            $stmt->bind_param('si', $status, $fineId);
            $stmt->execute();
            $stmt->close();
            $success = 'Fine updated successfully.';
        } catch (Throwable $e) {
            $error = 'Failed to update fine: ' . $e->getMessage();
        }
    } elseif ($action === 'create_fine') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['fine_amount'] ?? 0);
        $bookId = (int)($_POST['book_id'] ?? 0) ?: null; // Can be null
        $reason = trim($_POST['fine_reason'] ?? '');

        if ($userId > 0 && $amount > 0 && $reason !== '') {
            try {
                // Find the latest borrowing_id for this user and book if provided
                $borrowingId = null;
                if ($bookId !== null) {
                    $res = $connection->query("SELECT borrowing_id FROM borrowing_records WHERE user_id = $userId AND book_id = $bookId ORDER BY issue_date DESC LIMIT 1");
                    if ($res && $row = $res->fetch_assoc()) { $borrowingId = (int)$row['borrowing_id']; }
                }

                $stmt = $connection->prepare('INSERT INTO fines (user_id, borrowing_id, fine_amount, fine_reason, payment_status) VALUES (?, ?, ?, ?, "unpaid")');
                $stmt->bind_param('iids', $userId, $borrowingId, $amount, $reason);
                $stmt->execute();
                $stmt->close();
                $success = 'Fine created successfully.';
            } catch (Throwable $e) {
                $error = 'Failed to create fine: ' . $e->getMessage();
            }
        } else {
            $error = 'Please select a member and provide a valid amount and reason.';
        }
    }
}

function fetchFines(mysqli $connection, string $status): array {
    $rows = [];
    $stmt = $connection->prepare('SELECT f.fine_id, f.fine_amount, COALESCE(f.fine_reason, "") AS fine_reason, f.created_at, CONCAT(u.first_name, " ", u.last_name) AS member_name, br.borrowing_id, b.title AS book_title FROM fines f JOIN users u ON u.user_id = f.user_id LEFT JOIN borrowing_records br ON br.borrowing_id = f.borrowing_id LEFT JOIN books b ON b.book_id = br.book_id WHERE f.payment_status = ? ORDER BY f.created_at DESC LIMIT 200');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $rows[] = $row; }
    $stmt->close();
    return $rows;
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
$sql_books = "SELECT book_id, title FROM books WHERE is_deleted = 0 ORDER BY title";
if ($res = $connection->query($sql_books)) {
    while ($row = $res->fetch_assoc()) {
        $allBooks[] = $row;
    }
    $res->free();
}

$unpaid = $tab === 'unpaid' ? fetchFines($connection, 'unpaid') : [];
$paid = $tab === 'paid' ? fetchFines($connection, 'paid') : [];
$waived = $tab === 'waived' ? fetchFines($connection, 'waived') : [];

?>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-modern mb-4">
    <div class="card-body">
        <h5 class="mb-3">Create New Fine</h5>
        <form method="post">
            <input type="hidden" name="action" value="create_fine">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Select Member</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Choose a member --</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int)$member['user_id'] ?>"><?= htmlspecialchars($member['member_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Book (Optional)</label>
                    <select name="book_id" class="form-select">
                        <option value="">-- No specific book --</option>
                        <?php foreach ($allBooks as $book): ?>
                            <option value="<?= (int)$book['book_id'] ?>"><?= htmlspecialchars($book['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fine Amount (KES)</label>
                    <input type="number" name="fine_amount" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Reason</label>
                    <input type="text" name="fine_reason" class="form-control" placeholder="e.g., Damaged book cover" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-modern mt-3"><i class="bi bi-plus-circle me-1"></i> Add Fine</button>
        </form>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link <?= $tab==='unpaid'?'active':'' ?>" href="?tab=unpaid">Unpaid</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='paid'?'active':'' ?>" href="?tab=paid">Paid</a></li>
    <li class="nav-item"><a class="nav-link <?= $tab==='waived'?'active':'' ?>" href="?tab=waived">Waived</a></li>
</ul>

<div class="card card-modern">
    <div class="card-body">
        <?php
        $rows = $tab==='unpaid' ? $unpaid : ($tab==='paid' ? $paid : $waived);
        if (count($rows) === 0) {
            echo '<p class="text-muted mb-0">No fines in this category.</p>';
        } else {
        ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['member_name']) ?></td>
                        <td><?= htmlspecialchars($row['book_title'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['fine_reason']) ?></td>
                        <td><span class="badge text-bg-warning text-dark">KES <?= number_format((float)$row['fine_amount'], 2) ?></span></td>
                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <?php if ($tab === 'unpaid'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="fine_id" value="<?= (int)$row['fine_id'] ?>">
                                    <input type="hidden" name="action" value="mark_paid">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check2-circle"></i> Mark Paid</button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Waive this fine?');">
                                    <input type="hidden" name="fine_id" value="<?= (int)$row['fine_id'] ?>">
                                    <input type="hidden" name="action" value="mark_waived">
                                    <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-slash-circle"></i> Waive</button>
                                </form>
                            <?php else: ?>
                                <span class="badge text-bg-<?= $tab==='paid' ? 'success' : 'secondary' ?> text-white"><?= ucfirst($tab) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
</div>

<?php $template->footer($config); ?>
