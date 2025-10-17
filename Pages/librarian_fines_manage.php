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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['fine_id'])) {
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
