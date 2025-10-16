<?php
// Includes based on the user's provided context
require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php"; // Assumes this file initializes $connection and handles connection details

// Template object instantiation and header creation
$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Manage Users'); // Updated Title
$template->hero('Manage Users'); // Updated Hero Title


/* ==============================================
 * 1. DATABASE CONNECTION AND DATA RETRIEVAL (PHP)
 * Fetches users and their total unpaid fines.
 * ==============================================
 */
// Initialize data variable and column count
$table_rows_html = '';
$COLSPAN_COUNT = 6; // User ID, Name, Email, Role, Fines, Actions

// Check if $connection is available and connected (as established in dbconnection.php)
if (!isset($connection) || $connection->connect_error) {
    // Fallback if the connection failed
    error_log("Database Connection Failed in user management.");
    $table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center; color: red;">Database connection failed. Please check your dbconnection.php file.</td></tr>';
} else {
    // SQL Query: Select users and calculate the sum of unpaid fines.
    // NOTE: This query assumes the following columns exist in the database:
    // - users table: user_id, name, email, role (or role_name)
    // - fines table: user_id, amount, is_paid (1 for paid, 0 for unpaid)
    $sql = "SELECT 
                u.user_id, 
                u.combined_username AS name, -- Alias combined_username to 'name' for compatibility
                u.email, 
                r.role_name AS role, -- Alias the joined role name to 'role' for compatibility
                COALESCE(SUM(CASE WHEN f.payment_status = 'unpaid' THEN f.fine_amount ELSE 0 END), 0) AS total_fine
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id -- Updated foreign key name from roleid to role_id based on schema
            LEFT JOIN fines f ON u.user_id = f.user_id
            GROUP BY u.user_id, u.combined_username, u.email, r.role_name
            ORDER BY u.user_id DESC";

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $userId = htmlspecialchars($row['user_id']);
            $role = htmlspecialchars($row['role']);
            $fine_amount = $row['total_fine'];
            
            // Logic to display fines: blank for Admin/Librarian, amount for Reader
            if (in_array($role, ['Admin', 'Librarian'])) {
                $fine_display = ''; // Blank field
            } else {
                // Display the fine amount, formatted as currency
                $fine_display = ($fine_amount > 0) ? '$' . number_format($fine_amount, 2) : 'No Fines';
            }

            // Generate the HTML for the action buttons (updated to user-related actions)
            $actions = '
                <button class="btn btn-warning" onclick="editUser(' . $userId . ')">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-danger" onclick="deleteUser(' . $userId . ')">
                    <i class="bi bi-trash"></i> Delete
                </button>
            ';
            
            // Build the table row with 6 columns
            $table_rows_html .= '
                <tr>
                    <td>' . $userId . '</td>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['email']) . '</td>
                    <td>' . $role . '</td>
                    <td>' . $fine_display . '</td>
                    <td>' . $actions . '</td>
                </tr>
            ';
        }
    } else {
        // Fallback for no data found.
        $table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center;">No users found in the database.</td></tr>';
    }

    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Dashboard</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    
    <style>
        /* General Styles - Retained from previous version */
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
        .btn-dark {
            background-color: #1f2937;
            color: white;
        }
        .btn-dark:hover {
            background-color: #374151;
        }

        /* Form Controls */
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box; 
        }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .p-4 { padding: 1.5rem; }

        /* Table Styles */
        #all-users-table { /* Updated table ID */
            width: 100% !important; 
            border-collapse: collapse;
            margin-top: 10px;
        }
        #all-users-table thead th {
            background-color: #1f2937;
            color: white;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #374151;
        }
        #all-users-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        #all-users-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        #all-users-table tbody tr:hover {
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
            <h5>Add New User</h5> <!-- Updated Form Title -->
            <form>
                <div class="mb-2">
                    <input type="text" class="form-control" placeholder="Full Name">
                </div>
                <div class="mb-2">
                    <input type="email" class="form-control" placeholder="Email Address">
                </div>
                <div class="mb-2">
                    <select class="form-control">
                        <option value="">Select Role</option>
                        <option value="Reader">Reader</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <button class="btn btn-dark btn-modern">Add User</button> <!-- Updated Button Text -->
            </form>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <h5>All Users</h5> <!-- Updated Table Title -->
                
                <div class="table-responsive">
                    <table id="all-users-table" class="display"> <!-- Updated Table ID -->
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Fines</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- PHP DATA INJECTION: User Data is rendered here -->
                            <?php echo $table_rows_html; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Updated Placeholder functions for managing users
    function editUser(userId) {
        console.log('Action: Edit User ID:', userId);
        // Implement modal or redirection to edit user details
    }

    function deleteUser(userId) {
        console.log('Action: Delete User ID:', userId);
        // Implement confirmation and backend request to delete user
    }

    // DataTables Initialization Script (Updated to target the new table ID)
    $(document).ready(function() {
        $('#all-users-table').DataTable({
            "paging": true,        
            "searching": true,     
            "ordering": true,      
            "info": true,          
            "responsive": true,
            
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable sorting on 'Actions' column
            ]
        });
    });
</script>

</body>
</html>
<?php
// Note: Document end template calls should typically go here
$template->footer($config);
?>
