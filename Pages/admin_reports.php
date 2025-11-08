<?php
// reports_dashboard.php - Displays key metrics and detailed borrowing reports.

// Include necessary configuration and components.
// NOTE: Assuming these files contain the necessary DB connection logic and Template class.
require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php";

// 1. Initialize variables for metrics and table content
$total_books = 0;
$total_penalty_kes = 0; // Will calculate outstanding (unpaid) fines
$report_table_rows_html = '';
$COLSPAN_COUNT = 5; // Columns in the detailed report table

// --- 2. Fetch Metric Data ---

// Query 1: Total Books Count
$sql_total_books = "SELECT COUNT(book_id) AS total_books FROM books";
$result_total_books = $connection->query($sql_total_books);
if ($result_total_books && $row = $result_total_books->fetch_assoc()) {
    $total_books = (int)$row['total_books'];
}

// Query 2: Total Outstanding Penalty (Unpaid Fines)
// We sum all fines that have not yet been marked as paid.
$sql_total_penalty = "SELECT COALESCE(SUM(fine_amount), 0) AS total_penalty FROM fines WHERE payment_status = 'unpaid'";
$result_total_penalty = $connection->query($sql_total_penalty);
if ($result_total_penalty && $row = $result_total_penalty->fetch_assoc()) {
    $total_penalty_kes = number_format((float)$row['total_penalty'], 2, '.', ',');
}

// Query 3: Total Revenue from Paid Fines
$sql_total_revenue = "SELECT COALESCE(SUM(fine_amount), 0) AS total_revenue FROM fines WHERE payment_status = 'paid'";
$result_total_revenue = $connection->query($sql_total_revenue);
$total_revenue_kes = "0.00";
if ($result_total_revenue && $row = $result_total_revenue->fetch_assoc()) {
    $total_revenue_kes = number_format((float)$row['total_revenue'], 2, '.', ',');
}

// Template setup (assuming $config is defined in constants.php)
$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Library Reports');
$template->hero('Library Reports');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Reports Dashboard</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    
    <style>
        /* General Styles */
        html{
            padding: 0;
            margin: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
            padding: 0px;
        }
        .container {
            max-width: 1200px;
            margin: 0;
        }

        .card {
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        .card h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .card h6 {
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            margin: 0;
        }
        .card h3 {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-top: 5px;
        }

        /* Metric Card Specifics */
        .metric-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .metric-card i {
            color: #3b82f6; /* A nice blue color for icons */
            font-size: 2.5rem !important;
        }

        /* Table Styles (DataTables styling) */
        #detailed-reports-table {
            width: 100% !important; 
            border-collapse: collapse;
        }
        .table-dark th {
            background-color: #1f2937;
            color: white;
            font-weight: 600;
            padding: 12px;
            text-align: left;
        }
        #detailed-reports-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        #detailed-reports-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        #detailed-reports-table tbody tr:hover {
            background-color: #eff6ff;
        }
        .text-danger { color: #ef4444 !important; }
        .text-success { color: #10b981 !important; }
        .fw-bold { font-weight: 700; }

        /* DataTables Custom Styling */
        .dataTables_wrapper { padding: 1rem 0; }
        .dataTables_filter input, .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
        }
        .dataTables_paginate .paginate_button.current, 
        .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background-color: #3b82f6;
            color: white !important;
            border-color: #3b82f6;
        }

        /* Utility classes (mimicking Bootstrap) */
        .row { display: flex; flex-wrap: wrap; margin-left: -15px; margin-right: -15px; }
        .col-12 { flex: 0 0 100%; max-width: 100%; padding: 0 15px; }
        .col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0 15px; }
        @media (max-width: 768px) {
            .col-md-6 { flex: 0 0 100%; max-width: 100%; }
        }
        .mb-3 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .p-4 { padding: 1.5rem; }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .table-responsive { overflow-x: auto; width: 100%; }
    </style>

</head>
<body>

<div class="container">

    <div class="row mb-4">
        <!-- Total Books Card -->
        <div class="col-md-4 mb-3">
            <div class="card metric-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Books in Library</h6>
                        <h3 id="totalBooks"><?php echo $total_books; ?></h3>
                    </div>
                    <i class="bi bi-book"></i>
                </div>
            </div>
        </div>

        <!-- Total Outstanding Fine -->
        <div class="col-md-4 mb-3">
            <div class="card metric-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Outstanding Fine</h6>
                        <h3 id="totalPenalty">KES <?php echo $total_penalty_kes; ?></h3>
                    </div>
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>

        <!-- ✅ Total Revenue from Paid Fines -->
        <div class="col-md-4 mb-3">
            <div class="card metric-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Revenue from Paid Fines</h6>
                        <h3 id="totalRevenue">KES <?php echo $total_revenue_kes; ?></h3>
                    </div>
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 1️⃣ Frequently Borrowed Books -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card p-4">
                <h5>Most Frequently Borrowed Books</h5>
                <div class="table-responsive">
                    <table id="frequentBooksTable" class="display nowrap" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Times Borrowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_freq = "
                                SELECT b.book_id, b.title, COUNT(br.borrowing_id) AS times_borrowed
                                FROM borrowing_records br
                                JOIN books b ON br.book_id = b.book_id
                                GROUP BY b.book_id, b.title
                                ORDER BY times_borrowed DESC
                                LIMIT 10;
                            ";
                            $result_freq = $connection->query($sql_freq);
                            if ($result_freq && $result_freq->num_rows > 0) {
                                while($row = $result_freq->fetch_assoc()) {
                                    echo '<tr>
                                        <td>'.htmlspecialchars($row['book_id']).'</td>
                                        <td>'.htmlspecialchars($row['title']).'</td>
                                        <td>'.htmlspecialchars($row['times_borrowed']).'</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3" style="text-align:center;">No borrowing data found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2️⃣ Total Revenue from Fines -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card p-4">
                <h5>Total Revenue from Paid Fines</h5>
                <div class="table-responsive">
                    <table id="revenueTable" class="display nowrap" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>Fine ID</th>
                                <th>User</th>
                                <th>Book Title</th>
                                <th>Fine Amount (KES)</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_revenue = "
                                SELECT 
                                    f.fine_id,
                                    CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                                    b.title,
                                    f.fine_amount,
                                    f.created_at AS payment_date
                                FROM fines f
                                JOIN users u ON f.user_id = u.user_id
                                JOIN borrowing_records br ON f.borrowing_id = br.borrowing_id
                                JOIN books b ON br.book_id = b.book_id
                                WHERE f.payment_status = 'paid'
                                ORDER BY f.created_at DESC;
                            ";
                            $result_revenue = $connection->query($sql_revenue);
                            if ($result_revenue && $result_revenue->num_rows > 0) {
                                while($row = $result_revenue->fetch_assoc()) {
                                    echo '<tr>
                                        <td>'.htmlspecialchars($row['fine_id']).'</td>
                                        <td>'.htmlspecialchars($row['user_name']).'</td>
                                        <td>'.htmlspecialchars($row['title']).'</td>
                                        <td>'.number_format((float)$row['fine_amount'], 2).'</td>
                                        <td>'.htmlspecialchars($row['payment_date']).'</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" style="text-align:center;">No paid fines found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

 <!-- ✅ Make sure jQuery loads FIRST -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- ✅ DataTables core -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.min.css">
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.min.js"></script>

<!-- ✅ DataTables Buttons dependencies -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.1/js/buttons.print.min.js"></script>

<!-- ✅ Initialize DataTables for all tables -->
<script>
$(document).ready(function() {
    // Table 2: Frequently Borrowed Books
    $('#frequentBooksTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5', title: 'Frequently Borrowed Books' },
            { extend: 'csvHtml5', title: 'Frequently Borrowed Books' },
            { extend: 'excelHtml5', title: 'Frequently Borrowed Books' },
            { extend: 'pdfHtml5', title: 'Frequently Borrowed Books' },
            { extend: 'print', title: 'Frequently Borrowed Books' }
        ],
        pageLength: 5,
        ordering: true,
        language: { emptyTable: 'No borrowing data available.' }
    });

    // Table 3: Total Revenue from Fines
    $('#revenueTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5', title: 'Revenue from Fines' },
            { extend: 'csvHtml5', title: 'Revenue from Fines' },
            { extend: 'excelHtml5', title: 'Revenue from Fines' },
            { extend: 'pdfHtml5', title: 'Revenue from Fines' },
            { extend: 'print', title: 'Revenue from Fines' }
        ],
        pageLength: 5,
        ordering: true,
        language: { emptyTable: 'No revenue records available.' }
    });
});
</script>
</script>

</body>
</html>


<?php
// Close the database connection
$connection->close();

$template->footer($config);
?>
