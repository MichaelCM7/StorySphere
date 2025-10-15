<?php

interface SectionInterface
{
    public function getName(): string;
    public function getTitle(): string;
    public function renderContent(): string;
}



class TilesSection implements SectionInterface
{
    private array $tiles;

    public function __construct(array $tiles)
    {
        $this->tiles = $tiles;
    }

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
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($this->tiles as $tile): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card <?= $tile['color'] ?>">
                        <div class="icon">
                            <i class="<?= $tile['icon'] ?>"></i>
                        </div>
                        <div class="number"><?= $tile['value'] ?></div>
                        <div class="label"><?= $tile['label'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

class BookInventory implements SectionInterface
{
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

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
        // Fetch data from database
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
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['category']) ?></td>
                            <td>
                                <span class="badge bg-<?= $book['available_copies'] > 0 ? 'success' : 'danger' ?>">
                                    <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="books/edit.php?id=<?= $book['book_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3">
            <a href="books/manage.php" class="btn btn-primary">View All Books</a>
            <a href="books/add.php" class="btn btn-success">Add New Book</a>
        </div>
        <?php
        return ob_get_clean();
    }

    private function getRecentBooks(): array
    {
        // TODO: Replace with actual database query
        // Sample data for now
        return [
            [
                'book_id' => 1,
                'isbn' => '9780743273565',
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'category' => 'Fiction',
                'total_copies' => 3,
                'available_copies' => 2
            ],
            [
                'book_id' => 2,
                'isbn' => '9780061120084',
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'category' => 'Fiction',
                'total_copies' => 4,
                'available_copies' => 3
            ],
            [
                'book_id' => 3,
                'isbn' => '9780451524935',
                'title' => '1984',
                'author' => 'George Orwell',
                'category' => 'Fiction',
                'total_copies' => 5,
                'available_copies' => 4
            ]
        ];
    }
}

class Borrowings implements SectionInterface
{
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

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
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst($borrowing['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($borrowing['status'] === 'borrowed' || $borrowing['status'] === 'overdue'): ?>
                                    <a href="transactions/return.php?id=<?= $borrowing['borrowing_id'] ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-undo"></i> Return
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3">
            <a href="borrowings/all.php" class="btn btn-primary">View All Borrowings</a>
        </div>
        <?php
        return ob_get_clean();
    }

    private function getRecentBorrowings(): array
    {
        // TODO: Replace with actual database query
        return [
            [
                'borrowing_id' => 1,
                'member_name' => 'John Doe',
                'book_title' => 'The Great Gatsby',
                'borrow_date' => '2025-09-20',
                'due_date' => '2025-10-04',
                'status' => 'borrowed'
            ],
            [
                'borrowing_id' => 2,
                'member_name' => 'Sarah Smith',
                'book_title' => 'A Brief History of Time',
                'borrow_date' => '2025-09-25',
                'due_date' => '2025-10-09',
                'status' => 'borrowed'
            ],
            [
                'borrowing_id' => 3,
                'member_name' => 'John Doe',
                'book_title' => 'Thinking, Fast and Slow',
                'borrow_date' => '2025-09-15',
                'due_date' => '2025-09-29',
                'status' => 'overdue'
            ]
        ];
    }
}

class OverdueAlerts implements SectionInterface
{
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

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
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= $count ?> books</strong> are currently overdue
        </div>
        <?php if ($count > 0): ?>
            <ul class="list-group">
                <?php foreach (array_slice($overdueBooks, 0, 5) as $book): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($book['book_title']) ?></strong><br>
                            <small>Borrowed by: <?= htmlspecialchars($book['member_name']) ?></small><br>
                            <small class="text-danger">Overdue by <?= $book['days_overdue'] ?> days</small>
                        </div>
                        <span class="badge bg-danger"><?= $book['days_overdue'] ?> days</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end mt-3">
                <a href="reports/overdue.php" class="btn btn-danger">View All Overdue Books</a>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getOverdueBooks(): array
    {
        // TODO: Replace with actual database query
        return [
            [
                'book_title' => 'Thinking, Fast and Slow',
                'member_name' => 'John Doe',
                'days_overdue' => 7
            ],
            [
                'book_title' => 'The Da Vinci Code',
                'member_name' => 'Peter Mwangi',
                'days_overdue' => 4
            ]
        ];
    }
}

class PendingReservations implements SectionInterface
{
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

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
                <a href="reservations/manage.php" class="btn btn-info">Manage Reservations</a>
            </div>
        <?php else: ?>
            <p class="text-muted">No pending reservations</p>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getPendingReservations(): array
    {
        // TODO: Replace with actual database query
        return [
            [
                'book_title' => 'The Da Vinci Code',
                'member_name' => 'Sarah Smith',
                'reservation_date' => '2025-10-10'
            ],
            [
                'book_title' => 'Thinking, Fast and Slow',
                'member_name' => 'Grace Akinyi',
                'reservation_date' => '2025-10-12'
            ]
        ];
    }
}

class FineManagement implements SectionInterface
{
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

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
        $totalUnpaid = array_sum(array_column($fines, 'amount'));
        
        ob_start();
        ?>
        <div class="alert alert-warning">
            <i class="fas fa-money-bill-wave me-2"></i>
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
                        <span class="badge bg-warning text-dark">KES <?= number_format($fine['amount'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end mt-3">
                <a href="fines/manage.php" class="btn btn-warning">Manage All Fines</a>
            </div>
        <?php else: ?>
            <p class="text-muted">No unpaid fines</p>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    private function getUnpaidFines(): array
    {
        // TODO: Replace with actual database query
        return [
            [
                'member_name' => 'John Doe',
                'amount' => 70.00,
                'reason' => 'Book overdue by 7 days'
            ],
            [
                'member_name' => 'Peter Mwangi',
                'amount' => 40.00,
                'reason' => 'Book overdue by 4 days'
            ]
        ];
    }
}


class LibraryDashboard
{
    private string $title = "StorySphere - Librarian Dashboard";
    private array $sections;

    public function __construct()
    {
        // Initialize statistics tiles
        $tiles = [
            [
                'icon' => 'fas fa-book',
                'value' => '1,247',
                'label' => 'Total Books',
                'color' => 'primary'
            ],
            [
                'icon' => 'fas fa-check-circle',
                'value' => '892',
                'label' => 'Available Books',
                'color' => 'success'
            ],
            [
                'icon' => 'fas fa-book-reader',
                'value' => '78',
                'label' => 'Books Borrowed',
                'color' => 'warning'
            ],
            [
                'icon' => 'fas fa-exclamation-triangle',
                'value' => '12',
                'label' => 'Overdue Books',
                'color' => 'danger'
            ]
        ];

        $this->sections = [
            'statistics' => new TilesSection($tiles),
            'book-inventory' => new BookInventory(),
            'borrowings' => new Borrowings(),
            'overdue-alerts' => new OverdueAlerts(),
            'pending-reservations' => new PendingReservations(),
            'fine-management' => new FineManagement(),
        ];
    }

    public function renderPage(): void
    {
        $this->head();
        echo "<body>\n";
        $this->sidebar();
        echo "    <div class=\"main-content\">\n";
        $this->topBar();

        foreach ($this->sections as $sectionName => $section) {
            echo $this->renderSection($section);
        }

        $this->footer();
        echo "    </div>\n"; // Close main-content
        echo "</body>\n</html>";
    }

    private function head(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->title ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="../../assets/css/dashboard.css" rel="stylesheet">
        </head>
        <?php
    }

    private function sidebar(): void
    {
        ?>
        <div class="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-book-open"></i> StorySphere</h4>
                <small>Librarian Portal</small>
            </div>
            <nav>
                <a href="#" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="books/manage.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    <span>Manage Books</span>
                </a>
                <a href="transactions/issue.php" class="nav-item">
                    <i class="fas fa-hand-holding"></i>
                    <span>Issue Book</span>
                </a>
                <a href="transactions/return.php" class="nav-item">
                    <i class="fas fa-undo"></i>
                    <span>Return Book</span>
                </a>
                <a href="members/manage.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Manage Members</span>
                </a>
                <a href="reports/overdue.php" class="nav-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Overdue Books</span>
                </a>
                <a href="reservations/manage.php" class="nav-item">
                    <i class="fas fa-bookmark"></i>
                    <span>Reservations</span>
                </a>
                <a href="fines/manage.php" class="nav-item">
                    <i class="fas fa-money-bill"></i>
                    <span>Fines</span>
                </a>
                <a href="reports/analytics.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <a href="../auth/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
        <?php
    }

    private function topBar(): void
    {
        ?>
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" placeholder="Search books, members, ISBN...">
            </div>
            <div class="user-info">
                <div>
                    <div class="fw-bold">Admin User</div>
                    <small class="text-muted">Librarian</small>
                </div>
                <div class="user-avatar">AU</div>
            </div>
        </div>
        <?php
    }

    private function footer(): void
    {
        ?>
        <footer class="mt-5 py-3 text-center text-muted">
            <p>&copy; <?= date('Y') ?> StorySphere. All rights reserved.</p>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/js/dashboard.js"></script>
        <?php
    }

    private function renderSection(SectionInterface $section): string
    {
        ob_start();
        ?>
        <div class="content-card">
            <h5><i class="fas fa-chart-bar me-2"></i><?= $section->getTitle() ?></h5>
            <?= $section->renderContent() ?>
        </div>
        <?php
        return ob_get_clean();
    }
}


// Session check (For authentication here)
session_start();
// if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'librarian') {
//     header('Location: ../../auth/login.php');
//     exit();
// }

$dashboard = new LibraryDashboard();
$dashboard->renderPage();
?>