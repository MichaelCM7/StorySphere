<?php
require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php";

// Initialize metric variables
$total_books = 0;
$borrowed_books = 0;
$total_users = 0;
// Initialize as string formatted with 0.00 for currency display
$total_penalty_kes = '0.00'; 

// --- 1. Fetch Total Books Count ---
$sql_total_books = "SELECT COUNT(book_id) AS total_books FROM books";
$result_total_books = $connection->query($sql_total_books);
if ($result_total_books && $row = $result_total_books->fetch_assoc()) {
    $total_books = (int)$row['total_books'];
}

// --- 2. Fetch Books Borrowed (Active Loans) ---
// Assumes book_status_id 1 is 'Currently Borrowed' and 3 is 'Overdue' (matching admin_reports logic)
$sql_borrowed_books = "SELECT COUNT(borrowing_id) AS borrowed_books FROM borrowing_records WHERE book_status_id IN (1, 3)";
$result_borrowed_books = $connection->query($sql_borrowed_books);
if ($result_borrowed_books && $row = $result_borrowed_books->fetch_assoc()) {
    $borrowed_books = (int)$row['borrowed_books'];
}

// --- 3. Fetch Total Users Count ---
$sql_total_users = "SELECT COUNT(user_id) AS total_users FROM users";
$result_total_users = $connection->query($sql_total_users);
if ($result_total_users && $row = $result_total_users->fetch_assoc()) {
    $total_users = (int)$row['total_users'];
}

// --- 4. Fetch Total Outstanding Penalty (Unpaid Fines) ---
// This calculates the sum of all fines where payment_status is 'unpaid'.
$sql_total_penalty = "SELECT COALESCE(SUM(fine_amount), 0) AS total_penalty FROM fines WHERE payment_status = 'unpaid'";
$result_total_penalty = $connection->query($sql_total_penalty);
if ($result_total_penalty && $row = $result_total_penalty->fetch_assoc()) {
    // Format the currency amount to two decimal places
    $total_penalty_kes = number_format((float)$row['total_penalty'], 2, '.', ',');
}

// Close the database connection before proceeding with HTML
$connection->close();

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Dashboard');
$template->hero('Dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card card-modern bg-light text-dark">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Books</h6>
                        <h3 id="totalBooks"><?php echo $total_books; ?></h3>
                    </div>
                    <i class="bi bi-book" style="font-size:2rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-modern bg-light text-dark">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Books Borrowed</h6>
                        <h3 id="borrowedBooks"><?php echo $borrowed_books; ?></h3>
                    </div>
                    <i class="bi bi-journal-check" style="font-size:2rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-modern bg-light text-dark">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Users</h6>
                        <h3 id="totalUsers"><?php echo $total_users; ?></h3>
                    </div>
                    <i class="bi bi-people" style="font-size:2rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-modern bg-light text-dark">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Penalty Income</h6>
                        <h3 id="totalPenalty">KES <?php echo $total_penalty_kes; ?></h3>
                    </div>
                    <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 mb-2">
            <a href="admin_manage_books.php" class="btn btn-dark btn-modern w-100">Manage Books</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="admin_reports.php" class="btn btn-dark btn-modern w-100">Generate Reports</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="admin_profile.php" class="btn btn-dark btn-modern w-100">Profile</a>
        </div>
    </div>
</body>
</html>


<?php
$template->footer($config);
?>
