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


// --- 3. Fetch Detailed Report Table Data ---

// Query 3: Detailed Report on Active/Overdue Loans and Outstanding Fines
$sql_detailed_report = "
    SELECT
        br.book_id,
        b.title,
        CONCAT(u.first_name, ' ', u.last_name) AS borrower_name,
        bs.status_name AS borrowing_status,
        COALESCE(SUM(f.fine_amount), 0) AS outstanding_fine
    FROM borrowing_records br
    JOIN books b ON br.book_id = b.book_id
    JOIN users u ON br.user_id = u.user_id
    JOIN book_statuses bs ON br.book_status_id = bs.book_status_id
    LEFT JOIN fines f ON br.borrowing_id = f.borrowing_id AND f.payment_status = 'unpaid'
    WHERE br.book_status_id IN (1, 3) -- 1: Currently Borrowed, 3: Overdue
    GROUP BY br.borrowing_id, br.book_id, b.title, borrower_name, borrowing_status
    ORDER BY br.due_date ASC
";

$result_report = $connection->query($sql_detailed_report);

if ($result_report && $result_report->num_rows > 0) {
    while($row = $result_report->fetch_assoc()) {
        $fine_display = number_format((float)$row['outstanding_fine'], 2, '.', ',');
        
        // Determine status styling
        $status_class = '';
        if ($row['borrowing_status'] === 'Overdue') {
            $status_class = 'text-danger fw-bold';
        } elseif ($row['borrowing_status'] === 'Currently Borrowed') {
            $status_class = 'text-success';
        }
        
        // Build the table row
        $report_table_rows_html .= '
            <tr>
                <td>' . htmlspecialchars($row['book_id']) . '</td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td>' . htmlspecialchars($row['borrower_name']) . '</td>
                <td>' . htmlspecialchars($fine_display) . '</td>
                <td class="' . $status_class . '">' . htmlspecialchars($row['borrowing_status']) . '</td>
            </tr>
        ';
    }
} else {
    // Fallback for no data found.
    $report_table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center;">No active or overdue borrowing records found.</td></tr>';
}

// Close the database connection
$connection->close();

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
    
    <!-- Metric Cards -->
    <div class="row mb-4">
        
        <!-- Total Books Card -->
        <div class="col-md-6 mb-3">
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
        
        <!-- Total Penalty Card -->
        <div class="col-md-6 mb-3">
            <div class="card metric-card p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6>Total Outstanding Fine (KES)</h6>
                        <h3 id="totalPenalty">KES <?php echo $total_penalty_kes; ?></h3>
                    </div>
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-modern p-4">
                <h5>Detailed Borrowing Reports (Active & Overdue)</h5>
                
                <div class="table-responsive">
                    <table id="detailed-reports-table" class="display">
                        <thead class="table-dark">
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Borrowed By</th>
                                <th>Fine (KES)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data populated from PHP Query 3 -->
                            <?php echo $report_table_rows_html; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // DataTables Initialization Script
    $(document).ready(function() {
        // Initialize DataTables for the detailed report table
        $('#detailed-reports-table').DataTable({
            "paging": true,        
            "searching": true,     
            "ordering": true,      
            "info": true,          
            "responsive": true,
            "columnDefs": [
                // Set default order: Status (Overdue first) then Fine
                { "orderData": [ 4, 3 ], "targets": 0 } 
            ],
            // Custom message if table is empty
            "language": {
                "emptyTable": "No active or overdue borrowing records found in the system."
            }
        });
    });
</script>

</body>
</html>

<?php
$template->footer($config);
?>
