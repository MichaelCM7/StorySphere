<?php

// Reusable OOP Components for Librarian pages (DB-backed)
// - SectionInterface: contract for dashboard/section components
// - StatsTilesSection: statistics tiles computed from DB
// - BookInventorySection, BorrowingsSection, OverdueAlertsSection,
//   PendingReservationsSection, FineManagementSection: sections fetching real data
// - LibrarianTemplate: page template similar to Components/Template.php but with librarian nav

interface SectionInterface
{
    public function getName(): string;
    public function getTitle(): string;
    public function renderContent(): string;
}

abstract class DbBackedSection implements SectionInterface
{
    protected ?\mysqli $db = null;

    public function __construct($database = null)
    {
        if ($database instanceof \mysqli) {
            $this->db = $database;
        }
    }

    protected function getDb(): \mysqli
    {
        if ($this->db instanceof \mysqli) {
            return $this->db;
        }
        
        require __DIR__ . '/../Config/dbconnection.php'; // defines $connection
        $this->db = $connection; // from included file
        return $this->db;
    }
}

class StatsTilesSection extends DbBackedSection
{
    public function getName(): string
    {
        return 'statistics';
    }

    public function getTitle(): string
    {
        return 'Dashboard Overview';
    }

    public function renderContent(): string
    {
        $db = $this->getDb();

        // Total titles (number of distinct books)
        $totalBooks = 0;
        if ($res = $db->query("SELECT COUNT(*) AS cnt FROM books")) {
            $row = $res->fetch_assoc();
            $totalBooks = (int)($row['cnt'] ?? 0);
            $res->free();
        }

        // Available copies (sum of available_copies)
        $availableBooks = 0;
        if ($res = $db->query("SELECT COALESCE(SUM(available_copies),0) AS sum_avail FROM books")) {
            $row = $res->fetch_assoc();
            $availableBooks = (int)($row['sum_avail'] ?? 0);
            $res->free();
        }

        // Borrowed (currently issued or overdue and not returned)
        $borrowedBooks = 0;
        if ($res = $db->query("SELECT COUNT(*) AS cnt FROM borrowing_records WHERE return_date IS NULL")) {
            $row = $res->fetch_assoc();
            $borrowedBooks = (int)($row['cnt'] ?? 0);
            $res->free();
        }

        // Overdue (not returned and overdue status)
        $overdueBooks = 0;
        if ($res = $db->query("SELECT COUNT(*) AS cnt FROM borrowing_records WHERE return_date IS NULL AND (book_status_id = 3 OR due_date < CURDATE())")) {
            $row = $res->fetch_assoc();
            $overdueBooks = (int)($row['cnt'] ?? 0);
            $res->free();
        }

        $tiles = [
            [ 'icon' => 'bi bi-book', 'value' => number_format($totalBooks), 'label' => 'Total Books' ],
            [ 'icon' => 'bi bi-check-circle', 'value' => number_format($availableBooks), 'label' => 'Available Books' ],
            [ 'icon' => 'bi bi-journal-check', 'value' => number_format($borrowedBooks), 'label' => 'Books Borrowed' ],
            [ 'icon' => 'bi bi-exclamation-triangle', 'value' => number_format($overdueBooks), 'label' => 'Overdue Books' ],
        ];

        ob_start();
        ?>
        <div class="row">
            <?php foreach ($tiles as $tile): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card card-modern bg-light text-dark">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6><?= htmlspecialchars($tile['label']) ?></h6>
                                <h3><?= htmlspecialchars($tile['value']) ?></h3>
                            </div>
                            <i class="<?= htmlspecialchars($tile['icon']) ?>" style="font-size:2rem;"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

class BookInventorySection extends DbBackedSection
{
    public function getName(): string
    {
        return 'book-inventory';
    }

    public function getTitle(): string
    {
        return 'Book Inventory';
    }

    public function renderContent(): string
    {
        $books = $this->getRecentBooks();

        ob_start();
        ?>
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
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['isbn'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['title'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['author'] ?? '') ?></td>
                            <td><?= htmlspecialchars($book['category'] ?? '') ?></td>
                            <td>
                                <span class="badge text-bg-<?= (int)($book['available_copies'] ?? 0) > 0 ? 'success' : 'danger' ?>">
                                    <?= (int)($book['available_copies'] ?? 0) ?> / <?= (int)($book['total_copies'] ?? 0) ?>
                                </span>
                            </td>
                            <td>
                                <a href="librarian_book_edit.php?id=<?= (int)($book['book_id'] ?? 0) ?>" class="btn btn-sm btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3">
            <a href="librarian_books_crud.php" class="btn btn-dark btn-modern">View All Books</a>
            <a href="librarian_books_crud.php?action=create" class="btn btn-success btn-modern">Add New Book</a>
        </div>
        <?php
        return ob_get_clean();
    }

    private function getRecentBooks(): array
    {
        $db = $this->getDb();
        $data = [];
        $sql = "SELECT b.book_id, b.isbn, b.title, COALESCE(a.author_name,'') AS author, COALESCE(c.category_name,'') AS category, b.total_copies, b.available_copies
                FROM books b
                LEFT JOIN authors a ON a.author_id = b.author_id
                LEFT JOIN categories c ON c.category_id = b.category_id
                ORDER BY b.book_id DESC
                LIMIT 10";
        if ($res = $db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->free();
        }
        return $data;
    }
}

class BorrowingsSection extends DbBackedSection
{
    public function getName(): string
    {
        return 'borrowings';
    }

    public function getTitle(): string
    {
        return 'Recent Borrowings';
    }

    public function renderContent(): string
    {
        $borrowings = $this->getRecentBorrowings();

        ob_start();
        ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Book Title</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowings as $borrowing): ?>
                        <tr>
                            <td><?= htmlspecialchars($borrowing['member_name']) ?></td>
                            <td><?= htmlspecialchars($borrowing['book_title']) ?></td>
                            <td><?= date('M d, Y', strtotime($borrowing['borrow_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($borrowing['due_date'])) ?></td>
                            <td>
                                <?php
                                $statusClass = match($borrowing['status']) {
                                    'borrowed' => 'success',
                                    'overdue' => 'danger',
                                    'returned' => 'secondary',
                                    default => 'info'
                                };
                                ?>
                                <span class="badge text-bg-<?= $statusClass ?>">
                                    <?= ucfirst($borrowing['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($borrowing['status'] === 'borrowed' || $borrowing['status'] === 'overdue'): ?>
                                    <a href="#" class="btn btn-sm btn-warning" title="Return">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3">
            <a href="librarian_borrowings.php" class="btn btn-dark btn-modern">View All Borrowings</a>
        </div>
        <?php
        return ob_get_clean();
    }

    private function getRecentBorrowings(): array
    {
        $db = $this->getDb();
        $data = [];
        $sql = "SELECT br.borrowing_id,
                       CONCAT(u.first_name, ' ', u.last_name) AS member_name,
                       b.title AS book_title,
                       br.issue_date AS borrow_date,
                       br.due_date,
                       br.return_date,
                       br.book_status_id
                FROM borrowing_records br
                JOIN users u ON u.user_id = br.user_id
                JOIN books b ON b.book_id = br.book_id
                ORDER BY br.issue_date DESC
                LIMIT 10";
        if ($res = $db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $status = 'borrowed';
                if (!empty($row['return_date'])) {
                    $status = 'returned';
                } elseif ((int)$row['book_status_id'] === 3 || (isset($row['due_date']) && strtotime($row['due_date']) < strtotime(date('Y-m-d')))) {
                    $status = 'overdue';
                }
                $data[] = [
                    'borrowing_id' => (int)$row['borrowing_id'],
                    'member_name' => $row['member_name'] ?? '',
                    'book_title' => $row['book_title'] ?? '',
                    'borrow_date' => $row['borrow_date'],
                    'due_date' => $row['due_date'],
                    'status' => $status,
                ];
            }
            $res->free();
        }
        return $data;
    }
}

class OverdueAlertsSection extends DbBackedSection
{
    public function getName(): string
    {
        return 'overdue-alerts';
    }

    public function getTitle(): string
    {
        return 'Overdue Alerts';
    }

    public function renderContent(): string
    {
        $overdueBooks = $this->getOverdueBooks();
        $count = count($overdueBooks);

        ob_start();
        ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong><?= (int)$count ?> books</strong> are currently overdue
        </div>
        <?php if ($count > 0): ?>
            <ul class="list-group">
                <?php foreach (array_slice($overdueBooks, 0, 5) as $book): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($book['book_title']) ?></strong><br>
                            <small>Borrowed by: <?= htmlspecialchars($book['member_name']) ?></small><br>
                            <small class="text-danger">Overdue by <?= (int)$book['days_overdue'] ?> days</small>
                        </div>
                        <span class="badge text-bg-danger"><?= (int)$book['days_overdue'] ?> days</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end mt-3">
                <a href="librarian_overdue_list.php" class="btn btn-danger btn-modern">View All Overdue Books</a>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getOverdueBooks(): array
    {
        $db = $this->getDb();
        $data = [];
        $sql = "SELECT b.title AS book_title,
                       CONCAT(u.first_name,' ',u.last_name) AS member_name,
                       GREATEST(DATEDIFF(CURDATE(), br.due_date), 0) AS days_overdue
                FROM borrowing_records br
                JOIN users u ON u.user_id = br.user_id
                JOIN books b ON b.book_id = br.book_id
                WHERE br.return_date IS NULL AND (br.book_status_id = 3 OR br.due_date < CURDATE())
                ORDER BY br.due_date ASC
                LIMIT 20";
        if ($res = $db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = [
                    'book_title' => $row['book_title'] ?? '',
                    'member_name' => $row['member_name'] ?? '',
                    'days_overdue' => (int)($row['days_overdue'] ?? 0),
                ];
            }
            $res->free();
        }
        return $data;
    }
}

class PendingReservationsSection extends DbBackedSection
{
    public function getName(): string
    {
        return 'pending-reservations';
    }

    public function getTitle(): string
    {
        return 'Pending Reservations';
    }

    public function renderContent(): string
    {
        $reservations = $this->getPendingReservations();

        ob_start();
        ?>
        <?php if (count($reservations) > 0): ?>
            <ul class="list-group">
                <?php foreach ($reservations as $reservation): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($reservation['book_title']) ?></strong><br>
                        <small>Reserved by: <?= htmlspecialchars($reservation['member_name']) ?></small><br>
                        <small class="text-muted">Date: <?= date('M d, Y', strtotime($reservation['reservation_date'])) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end mt-3">
                <a href="librarian_reservations.php" class="btn btn-info btn-modern text-white">Manage Reservations</a>
            </div>
        <?php else: ?>
            <p class="text-muted">No pending reservations</p>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getPendingReservations(): array
    {
        $db = $this->getDb();
        $data = [];
        $sql = "SELECT b.title AS book_title,
                       CONCAT(u.first_name,' ',u.last_name) AS member_name,
                       r.reservation_date
                FROM reservations r
                JOIN users u ON u.user_id = r.user_id
                JOIN books b ON b.book_id = r.book_i
                WHERE r.status = 'active'
                ORDER BY r.reservation_date DESC
                LIMIT 20";
        if ($res = $db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = [
                    'book_title' => $row['book_title'] ?? '',
                    'member_name' => $row['member_name'] ?? '',
                    'reservation_date' => $row['reservation_date'],
                ];
            }
            $res->free();
        }
        return $data;
    }
}

class FineManagementSection extends DbBackedSection
{
    public function getName(): string
    {
        return 'fine-management';
    }

    public function getTitle(): string
    {
        return 'Fine Management';
    }

    public function renderContent(): string
    {
        $fines = $this->getUnpaidFines();
        $totalUnpaid = array_sum(array_map(fn($f) => (float)$f['amount'], $fines));

        ob_start();
        ?>
        <div class="alert alert-warning">
            <i class="bi bi-cash-coin me-2"></i>
            Total unpaid fines: <strong>KES <?= number_format($totalUnpaid, 2) ?></strong>
        </div>
        <?php if (count($fines) > 0): ?>
            <ul class="list-group">
                <?php foreach (array_slice($fines, 0, 5) as $fine): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($fine['member_name']) ?></strong><br>
                            <small><?= htmlspecialchars($fine['reason']) ?></small>
                        </div>
                        <span class="badge text-bg-warning text-dark">KES <?= number_format($fine['amount'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end mt-3">
                <a href="librarian_fines_manage.php" class="btn btn-warning btn-modern">Manage All Fines</a>
            </div>
        <?php else: ?>
            <p class="text-muted">No unpaid fines</p>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getUnpaidFines(): array
    {
        $db = $this->getDb();
        $data = [];
        $sql = "SELECT CONCAT(u.first_name,' ',u.last_name) AS member_name,
                       f.fine_amount AS amount,
                       COALESCE(f.fine_reason,'') AS reason
                FROM fines f
                JOIN users u ON u.user_id = f.user_id
                WHERE f.payment_status = 'unpaid'
                ORDER BY f.created_at DESC
                LIMIT 50";
        if ($res = $db->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = [
                    'member_name' => $row['member_name'] ?? '',
                    'amount' => (float)($row['amount'] ?? 0),
                    'reason' => $row['reason'] ?? '',
                ];
            }
            $res->free();
        }
        return $data;
    }
}

class LibrarianTemplate
{
    // Navigation bar tailored for Librarian pages
    public function navArea(array $config): void
    {
        ?>
        <!-- Use user-style navbar structure for consistent styling -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <div class="navbar">
          <div class="logo">
            <h2><i class="fa-solid fa-book"></i> <?= htmlspecialchars($config['Website_Name'] ?? 'StorySphere') ?></h2>
            <p>Library Management</p>
          </div>

          <ul class="nav-links">
            <li><a href="librarian_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="librarian_books_crud.php"><i class="fa-solid fa-book-open"></i> Books</a></li>
            <li><a href="librarian_borrowings.php"><i class="fa-solid fa-book-reader"></i> Borrowings</a></li>
            <li><a href="librarian_fines_manage.php"><i class="fa-solid fa-coins"></i> Fines</a></li>
            <li><a href="librarian_reservations.php"><i class="fa-solid fa-calendar-plus"></i> Reservations</a></li>
            <li><a href="librarian_reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></li>
            <li><a href="librarian_profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
          </ul>

                    <div class="cta">
                        <a class="btn btn-info btn-modern text-white" href="librarian_reservations.php"><i class="fa-solid fa-calendar-plus"></i> Manage Reservations</a>
                    </div>

          <div class="logout">
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
          </div>
        </div>
        <?php
    }

    public function documentStart(array $config, string $pageTitle = ''): void
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>
                <?php
                echo $pageTitle ? htmlspecialchars($pageTitle) . ' – ' . htmlspecialchars($config['Website_Name'] ?? 'StorySphere') : htmlspecialchars($config['Website_Name'] ?? 'StorySphere');
                ?>
            </title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <!-- DataTables local CSS (ensure placed after base styles) -->
            <link rel="stylesheet" href="../Datatables/3.1.1.css">
            <?php
            // Include user styles for navbar consistency (cache-busted by filemtime)
            $userCssPath = __DIR__ . '/../user_style.css';
            $userCssVer = file_exists($userCssPath) ? filemtime($userCssPath) : time();
            ?>
            <link rel="stylesheet" href="../user_style.css?v=<?= $userCssVer ?>">
            <style>
                body { background-color: #f8f9fa; min-height: 100vh; display: flex; flex-direction: column; }
                .content { padding: 20px; flex: 1; }
                .footer { background-color: #fff; padding: 15px 20px; border-top: 1px solid #ddd; text-align: center; margin-top: auto; }
                .card-modern { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s ease; }
                .card-modern:hover { transform: translateY(-5px); }
                .btn-modern { border-radius: 8px; padding: 10px 20px; font-weight: 500; }
            </style>
        </head>
        <body>
        <div class="container content mt-4">
        <?php
    }

    public function hero(string $title): void
    {
        ?>
        <div class="mb-4">
            <h2><?= htmlspecialchars($title) ?></h2>
        </div>
        <?php
    }

    public function footer(array $config): void
    {
        ?>
        </div>
        <div class="footer mt-4">
            &copy; <?= date('Y'); ?> <?= htmlspecialchars($config['Website_Name'] ?? 'StorySphere'); ?>
        </div>
        <!-- Load scripts: jQuery (local) -> DataTables core -> optional dependencies -->
        <script src="../Datatables/3.7.1.js"></script>
        <script src="../Datatables/2.1.4.js"></script>
        <script src="../Datatables/dependancy1.js"></script>
        <script src="../Datatables/dependancy2.js"></script>
        <script src="../Datatables/dependancy3.js"></script>
        <script src="../Datatables/dependancy4.js"></script>
        <script src="../Datatables/dependancy5.js"></script>
        <script src="../Datatables/dependacy6.js"></script>

        <script>
        // Robust initializer: wait for jQuery and DataTables, then initialize tables inside cards
        (function() {
            function initDataTablesOnce() {
                if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable)) {
                    return false;
                }
                try {
                    var $ = window.jQuery;
                    // initialize any table that isn't already a DataTable
                    $('table').each(function(i, tbl) {
                        var $tbl = $(tbl);
                        if (!$tbl.attr('id')) {
                            $tbl.attr('id', 'tbl-auto-' + i + '-' + Date.now());
                        }
                        if (!$.fn.DataTable.isDataTable($tbl)) {
                            $tbl.DataTable({ pageLength: 25, lengthChange: false, responsive: true });
                        }
                    });
                    console.log('DataTables initialized on page tables');
                    return true;
                } catch (e) {
                    console.warn('Error initializing DataTables', e);
                    return false;
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    var tries = 0;
                    (function waitForDt(){
                        if (initDataTablesOnce() || ++tries > 50) return;
                        setTimeout(waitForDt, 150);
                    })();
                });
            } else {
                var tries = 0;
                (function waitForDt(){
                    if (initDataTablesOnce() || ++tries > 50) return;
                    setTimeout(waitForDt, 150);
                })();
            }
        })();
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}