<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Overdue Books');
$template->hero('Overdue Books');

$success = '';
$error = '';

// Handle return action via stored procedure ReturnBook
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_borrowing_id'])) {
    $borrowingId = (int)$_POST['return_borrowing_id'];
    if ($borrowingId > 0) {
        try {
            // Call stored procedure ReturnBook
            $stmt = $connection->prepare('CALL ReturnBook(?)');
            $stmt->bind_param('i', $borrowingId);
            $stmt->execute();
            // Some drivers require fetching/clearing result sets after CALL
            while ($stmt->more_results() && $stmt->next_result()) { /* clear multi results */ }
            $stmt->close();
            $success = 'Book return processed successfully.';
        } catch (Throwable $e) {
            $error = 'Failed to process return: ' . $e->getMessage();
        }
    }
}

// Fetch overdue borrowings
$rows = [];
$sql = "SELECT br.borrowing_id,
               b.title AS book_title,
               b.book_id,
               CONCAT(u.first_name,' ',u.last_name) AS member_name,
               u.user_id,
               br.issue_date,
               br.due_date,
               DATEDIFF(CURDATE(), br.due_date) AS days_overdue
        FROM borrowing_records br
        JOIN users u ON u.user_id = br.user_id
        JOIN books b ON b.book_id = br.book_id
        WHERE br.return_date IS NULL AND (br.book_status_id = 3 OR br.due_date < CURDATE())
        ORDER BY br.due_date ASC";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $res->free();
}
?>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-modern">
    <div class="card-body">
        <?php if (count($rows) === 0): ?>
            <p class="text-muted mb-0">No overdue books.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Due</th>
                        <th>Days Overdue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['member_name']) ?></td>
                            <td><?= htmlspecialchars($row['book_title']) ?></td>
                            <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            <td><span class="badge text-bg-danger"><?= (int)$row['days_overdue'] ?></span></td>
                            <td>
                                <form method="post" class="d-inline" onsubmit="return confirm('Process return for this borrowing?');">
                                    <input type="hidden" name="return_borrowing_id" value="<?= (int)$row['borrowing_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        <i class="bi bi-arrow-counterclockwise"></i> Return
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $template->footer($config); ?>
<!-- DataTables assets (local) -->
<link rel="stylesheet" href="../Datatables/3.1.1.css">
<!-- jQuery (required) -->
<script src="../Datatables/3.7.1.js"></script>
<!-- DataTables core -->
<script src="../Datatables/2.1.4.js"></script>
<!-- Optional extensions (JSZip/pdfmake) - used for export buttons if needed -->
<script src="../Datatables/dependancy1.js"></script>
<script src="../Datatables/dependancy2.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) console.log('jQuery version', window.jQuery.fn && window.jQuery.fn.jquery);
    if (window.jQuery) console.log('DataTable plugin present:', !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable));
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        window.jQuery('table').each(function(i, el){
            if (!el.id) el.id = 'datatable-overdue-' + i;
            if (!window.jQuery.fn.DataTable.isDataTable('#' + el.id)) {
                window.jQuery('#' + el.id).DataTable({ pageLength: 25, order: [[3, 'asc']] });
            }
        });
    }
});
</script>
