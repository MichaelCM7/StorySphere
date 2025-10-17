<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Reports');
$template->hero('Librarian Reports');

// Total books in stock (sum of total_copies)
$totalBooksInStock = 0;
if ($res = $connection->query("SELECT COALESCE(SUM(total_copies),0) AS total FROM books")) {
    $row = $res->fetch_assoc();
    $totalBooksInStock = (int)($row['total'] ?? 0);
    $res->free();
}

// Pending returns: borrowing_records not returned and due_date >= today
$pending = [];
$sql = "SELECT br.borrowing_id, b.title AS book_title, CONCAT(u.first_name,' ',u.last_name) AS member_name, br.issue_date, br.due_date
        FROM borrowing_records br
        JOIN books b ON b.book_id = br.book_id
        JOIN users u ON u.user_id = br.user_id
        WHERE br.return_date IS NULL AND br.due_date >= CURDATE()
        ORDER BY br.due_date ASC";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) { $pending[] = $r; }
    $res->free();
}

?>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Total Books in Stock</h6>
                    <h3><?= number_format($totalBooksInStock) ?></h3>
                </div>
                <i class="bi bi-book" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Pending Returns</h6>
                    <h3><?= number_format(count($pending)) ?></h3>
                </div>
                <i class="bi bi-journal-check" style="font-size:2rem;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Pending Return Books</h5>
        <?php if (count($pending) === 0): ?>
            <p class="text-muted mb-0">No pending returns.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Borrowed</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['member_name']) ?></td>
                                <td><?= htmlspecialchars($row['book_title']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $template->footer($config); ?>
