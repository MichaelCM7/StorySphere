<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';
require_once __DIR__ . '/../Config/dbconnection.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Reports');
$template->hero('Reports', 'Key metrics and data exports');

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

// Overdue books: borrowing_records not returned and due_date < today
$overdue = [];
$sql = "SELECT br.borrowing_id, b.title AS book_title, CONCAT(u.first_name,' ',u.last_name) AS member_name, 
               br.issue_date, br.due_date, DATEDIFF(CURDATE(), br.due_date) AS days_overdue
        FROM borrowing_records br
        JOIN books b ON b.book_id = br.book_id
        JOIN users u ON u.user_id = br.user_id
        WHERE br.return_date IS NULL AND br.due_date < CURDATE()
        ORDER BY br.due_date ASC";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) { $overdue[] = $r; }
    $res->free();
}

// Fines report
$fines = [];
$totalFinesCollected = 0;
$totalFinesPending = 0;

$sql = "SELECT CONCAT(u.first_name, ' ', u.last_name) AS member_name, f.fine_amount, f.fine_reason, f.payment_status, f.created_at
        FROM fines f
        JOIN users u ON u.user_id = f.user_id
        ORDER BY f.created_at DESC";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $fines[] = $r;
        if ($r['payment_status'] === 'paid') {
            $totalFinesCollected += (float)$r['fine_amount'];
        } elseif ($r['payment_status'] === 'unpaid') {
            $totalFinesPending += (float)$r['fine_amount'];
        }
    }
    $res->free();
}

// Lost books report
$lostBooks = [];
$sql = "SELECT CONCAT(u.first_name, ' ', u.last_name) AS member_name, b.title AS book_title, br.issue_date
        FROM borrowing_records br
        JOIN books b ON b.book_id = br.book_id
        JOIN users u ON u.user_id = br.user_id
        WHERE br.book_status_id = 4
        ORDER BY br.issue_date DESC";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $lostBooks[] = $r;
    }
    $res->free();
}

// Damaged books report
$damagedBooks = [];
$sql = "SELECT title, book_condition, shelf_location
        FROM books
        WHERE book_condition = 'poor'
        ORDER BY title";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $damagedBooks[] = $r;
    }
    $res->free();
}

// Popularity report (most borrowed books)
$popularity = [];
$sql = "SELECT b.title, COUNT(br.book_id) AS borrow_count
        FROM borrowing_records br
        JOIN books b ON b.book_id = br.book_id
        GROUP BY br.book_id, b.title
        ORDER BY borrow_count DESC
        LIMIT 10";
if ($res = $connection->query($sql)) {
    while ($r = $res->fetch_assoc()) {
        $popularity[] = $r;
    }
    $res->free();
}

?>

<div class="row mb-4">
    <div class="col-md-2dot4 mb-3">
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
    <div class="col-md-2dot4 mb-3">
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
    <div class="col-md-2dot4 mb-3">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Overdue Books</h6>
                    <h3 class="text-danger"><?= number_format(count($overdue)) ?></h3>
                </div>
                <i class="bi bi-exclamation-circle" style="font-size:2rem; color: #dc3545;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2dot4 mb-3">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Lost Books</h6>
                    <h3 class="text-danger"><?= number_format(count($lostBooks)) ?></h3>
                </div>
                <i class="bi bi-book-half" style="font-size:2rem; color: #dc3545;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-2dot4 mb-3">
        <div class="card card-modern p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6>Most Borrowed</h6>
                    <h3 class="text-truncate" title="<?= htmlspecialchars($popularity[0]['title'] ?? 'N/A') ?>" style="max-width: 150px;"><?= htmlspecialchars($popularity[0]['title'] ?? 'N/A') ?></h3>
                </div>
                <i class="bi bi-star-fill" style="font-size:2rem; color: #ffc107;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-modern mb-4">
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

<div class="card card-modern">
    <div class="card-body">
        <h5 class="mb-3">Overdue Books</h5>
        <?php if (count($overdue) === 0): ?>
            <p class="text-muted mb-0">No overdue books.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Book</th>
                            <th>Borrowed</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdue as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['member_name']) ?></td>
                                <td><?= htmlspecialchars($row['book_title']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['due_date'])) ?></td>
                                <td><span class="badge text-bg-danger"><?= (int)$row['days_overdue'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card card-modern mt-4">
    <div class="card-body">
        <h5 class="mb-3">Fines Report</h5>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card card-modern p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Total Fines Collected</h6>
                            <h3>KES <?= number_format($totalFinesCollected, 2) ?></h3>
                        </div>
                        <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card card-modern p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Pending Fines</h6>
                            <h3>KES <?= number_format($totalFinesPending, 2) ?></h3>
                        </div>
                        <i class="bi bi-cash-coin" style="font-size:2rem; color: #dc3545;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td><?= htmlspecialchars($fine['member_name']) ?></td>
                            <td>KES <?= number_format($fine['fine_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($fine['fine_reason']) ?></td>
                            <td><span class="badge text-bg-<?= $fine['payment_status'] === 'paid' ? 'success' : ($fine['payment_status'] === 'unpaid' ? 'warning' : 'secondary') ?>"><?= ucfirst($fine['payment_status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($fine['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card card-modern mt-4">
    <div class="card-body">
        <h5 class="mb-3">Damaged Books Report</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Condition</th>
                        <th>Shelf Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($damagedBooks as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><span class="badge text-bg-warning"><?= ucfirst($book['book_condition']) ?></span></td>
                            <td><?= htmlspecialchars($book['shelf_location']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card card-modern mt-4">
    <div class="card-body">
        <h5 class="mb-3">Lost Books Report</h5>
        <?php if (empty($lostBooks)): ?>
            <p class="text-muted mb-0">No lost books found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Book Title</th>
                        <th>Date Issued</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lostBooks as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['member_name']) ?></td>
                            <td><?= htmlspecialchars($book['book_title']) ?></td>
                            <td><?= date('M d, Y', strtotime($book['issue_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card card-modern mt-4">
    <div class="card-body">
        <h5 class="mb-3">Book Popularity Report</h5>
        <?php if (empty($popularity)): ?>
            <p class="text-muted mb-0">No borrowing records found.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Book Title</th>
                        <th>Total Borrows</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($popularity as $index => $book): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= (int)$book['borrow_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $template->footer($config); ?>
