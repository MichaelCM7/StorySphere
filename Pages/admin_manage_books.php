<?php
require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php";


// Check connection and initialize data variable
$table_rows_html = '';
$COLSPAN_COUNT = 6; // Reset total column count to 6

// SQL Query
$sql = "SELECT 
                b.book_id, 
                b.title, 
                a.author_name AS author, 
                c.category_name AS category, 
                b.isbn
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.author_id
            LEFT JOIN categories c ON b.category_id = c.category_id
            ORDER BY b.book_id DESC";

$result = $connection->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Generate the HTML for the action buttons
        $actions = '
            <button class="btn btn-warning" onclick="editBook(' . htmlspecialchars($row['book_id']) . ')">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn btn-danger" onclick="deleteBook(' . htmlspecialchars($row['book_id']) . ')">
                <i class="bi bi-trash"></i> Delete
            </button>
        ';
        
        // Build the table row with 10 columns
        $table_rows_html .= '
            <tr>
                <td>' . htmlspecialchars($row['book_id']) . '</td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td>' . htmlspecialchars($row['author'] ?: 'N/A') . '</td>
                <td>' . htmlspecialchars($row['category'] ?: 'N/A') . '</td>
                <td>' . htmlspecialchars($row['isbn']) . '</td>
                <td>' . $actions . '</td>
            </tr>
        ';
    }
} else {
    // Fallback for no data found. colspan is 10 now.
    $table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center;">No books found in the database.</td></tr>';
}

$connection->close();

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Manage Books');
$template->hero('Manage Books');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management Dashboard</title>
    
    <!-- Bootstrap Icons (used for the action buttons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables Dependencies -->
    <!-- 1. jQuery (must be loaded first for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- 2. DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <!-- 3. DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    
    <style>
        /* General Styles - No CSS Framework */
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
        }
        .card h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
            border: none;
            cursor: pointer;
            margin: 2px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background-color: #d97706;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }

        /* Table Styles */
        #all-books-table {
            width: 100% !important; /* DataTables sets width inline, this ensures it respects the container */
            border-collapse: collapse;
            margin-top: 10px;
        }
        #all-books-table thead th {
            background-color: #1f2937;
            color: white;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #374151;
        }
        #all-books-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        #all-books-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        #all-books-table tbody tr:hover {
            background-color: #eff6ff;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper {
            padding: 1rem 0;
        }
        .dataTables_filter input, .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 10px;
            outline: none;
            transition: border-color 0.15s ease-in-out;
        }
        .dataTables_filter input:focus, .dataTables_length select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            transition: background-color 0.15s ease-in-out;
        }
        .dataTables_paginate .paginate_button.current, 
        .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background-color: #3b82f6;
            color: white !important;
            border-color: #3b82f6;
        }
        .dataTables_paginate .paginate_button.disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Responsive Layout (for smaller screens) */
        @media screen and (max-width: 768px) {
            .card {
                padding: 15px;
            }
            /* Force table to scroll horizontally on small screens if necessary */
            .table-responsive {
                overflow-x: auto;
                width: 100%;
            }
        }
    </style>

</head>
<body>

<div class="container">
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-modern p-4">
            <h5>Add New Book</h5>
            <form method="post" action="../Config/admin_manage_books_submit.php">
                <div class="mb-2">
                    <input id="title" name="title" type="text" class="form-control" placeholder="Title" required>
                </div>
                <div class="mb-2">
                    <input id="author" name="author" type="text" class="form-control" placeholder="Author" required>
                </div>
                <div class="mb-2">
                    <input id="isbn" name="isbn" type="text" class="form-control" placeholder="ISBN" required>
                </div>
                <div class="mb-2">
                    <select id="category" name="category" style="color: #212529;font-size:1rem;" class="form-control" required>
                        <option value="" disabled selected>Select A Book Category</option>
                        <option value="1">Science Fiction</option>
                        <option value="2">Classic Literature</option>
                        <option value="3">Fantasy</option>
                        <option value="4">Mystery</option>
                        <option value="5">Non-Fiction</option>
                        <option value="6">Thriller</option>
                        <option value="7">Historical Fiction</option>
                        <option value="8">Poetry</option>
                    </select>
                </div>
                <div class="mb-2">
                    <input id="number" name="number" type="text" class="form-control" placeholder="Number of Copies" required>
                </div>
                <button type="submit" class="btn btn-dark btn-modern">Add Book</button>
            </form>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5>All Books</h5>
                
                <div class="table-responsive">
                    <table id="all-books-table" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>ISBN</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data from database will be inserted here -->
                             <?php echo $table_rows_html; ?>
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
        // Target the table by its ID and initialize DataTables
        $('#all-books-table').DataTable({
            "paging": true,        
            "searching": true,     
            "ordering": true,      
            "info": true,          
            "responsive": true,
            // Configuration for future AJAX loading of database data
            // "processing": true,
            // "serverSide": true,
            // "ajax": "api/fetch_books.php", // Placeholder for actual database endpoint

            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable sorting on 'Actions' column
            ]
        });
    });
</script>

</body>
</html>
