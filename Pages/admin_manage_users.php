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
                u.combined_username AS name,
                u.email, 
                u.phone_number,
                r.role_name AS role,
                COALESCE(SUM(CASE WHEN f.payment_status = 'unpaid' THEN f.fine_amount ELSE 0 END), 0) AS total_fine
            FROM users u
            INNER JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN fines f ON u.user_id = f.user_id
            GROUP BY u.user_id, u.combined_username, u.email, u.phone_number, r.role_name
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
            $isDeleted = isset($row['is_deleted']) ? (int)$row['is_deleted'] : 0;

            if ($isDeleted === 1) {
                $deleteButton = '
                    <button class="btn btn-success" onclick="toggleUserDelete(' . $userId . ', 0, this)">
                        <i class="bi bi-arrow-counterclockwise"></i> Revert
                    </button>';
            } else {
                $deleteButton = '
                    <button class="btn btn-danger" onclick="toggleUserDelete(' . $userId . ', 1, this)">
                        <i class="bi bi-trash"></i> Delete
                    </button>';
            }

            $actions = '
                <button class="btn btn-warning" onclick="editUser(' . $userId . ')">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                ' . $deleteButton . '
            ';
            
            // Build the table row with 6 columns
            $table_rows_html .= '
                <tr>
                    <td>' . $userId . '</td>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['email']) . '</td>
                    <td>' . htmlspecialchars($row['phone_number'] ?? '') . '</td>
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
    <!-- In <head> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

<!-- Edit User Modal -->
<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editUserForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" id="edit_user_id">
          <div class="mb-3">
            <label for="edit_name" class="form-label">Full Name</label>
            <input type="text" id="edit_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_email" class="form-label">Email</label>
            <input type="email" id="edit_email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_phone" class="form-label">Phone Number</label>
            <input type="text" id="edit_phone" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_role" class="form-label">Role</label>
            <select id="edit_role" class="form-select" required>
                <option value="Administrator">Administrator</option>
                <option value="Librarian">Librarian</option>
                <option value="Member">Member</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-dark">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="container">
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card card-modern p-4">
        <h5>Add New User</h5>
        <form id="addUserForm">
          <div class="mb-2">
            <input type="text" id="add_first_name" class="form-control" placeholder="First Name" required>
          </div>
          <div class="mb-2">
            <input type="text" id="add_last_name" class="form-control" placeholder="Last Name" required>
          </div>
          <div class="mb-2">
            <input type="email" id="add_email" class="form-control" placeholder="Email Address" required>
          </div>
          <div class="mb-2">
            <input type="text" id="add_phone" class="form-control" placeholder="Phone Number" required>
          </div>
                <div class="mb-2">
            <select id="add_role" class="form-control" required>
              <option value="">Select Role</option>
              <option value="Administrator">Administrator</option>
              <option value="Librarian">Librarian</option>
              <option value="Member">Member</option>
            </select>
          </div>
          <button type="submit" class="btn btn-dark btn-modern">Add User</button>
        </form>
      </div>
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
                                <th>Phone</th>
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

<!-- Bootstrap JS must be before your own JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let editModal = new bootstrap.Modal(document.getElementById('editUserModal'));

function editUser(userId) {
    let row = document.querySelector(`#all-users-table button[onclick="editUser(${userId})"]`).closest("tr");
    let name = row.children[1].textContent.trim();
    let email = row.children[2].textContent.trim();
    let phone = row.children[3].textContent.trim();
    let role = row.children[4].textContent.trim();

    $('#edit_user_id').val(userId);
    $('#edit_name').val(name);
    $('#edit_email').val(email);
    $('#edit_phone').val(phone);
    $('#edit_role').val(role);

    editModal.show();
}

$('#editUserForm').on('submit', function(e) {
    e.preventDefault();

    let userId = $('#edit_user_id').val();
    let name = $('#edit_name').val();
    let email = $('#edit_email').val();
    let role = $('#edit_role').val();
    let phone_number = $('#edit_phone').val();

    console.log("Sending:", { user_id: userId, name: name, email: email, role: role });

    fetch('update_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          user_id: userId,
          name: name,
          email: email,
          phone_number: phone_number,
          role: role
        })
    })
    .then(response => response.json())
    .then(res => {
        console.log("Response:", res);
        if (res.success) {
            alert("User updated successfully!");
            location.reload();
        } else {
            alert("Update failed: " + res.message);
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Unexpected error while updating user.");
    });
});

// Handle Add User form submission
$('#addUserForm').on('submit', function(e) {
  e.preventDefault();

  let first_name = $('#add_first_name').val();
  let last_name = $('#add_last_name').val();
  let email = $('#add_email').val();
  let role = $('#add_role').val();
  let phone_number = $('#add_phone').val();

  if (!first_name || !last_name || !email || !role || !phone_number) {
    alert("Please fill all fields.");
    return;
  }

  fetch('add_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ first_name, last_name, email, role, phone_number })
  })
  .then(response => response.json())
  .then(res => {
    if (res.success) {
      alert("User added successfully!");
      location.reload();
    } else {
      alert("Failed to add user: " + res.message);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    alert("Unexpected error while adding user.");
  });
});

function toggleUserDelete(userId, newStatus, button) {
  const confirmMsg = newStatus === 1 
    ? "Are you sure you want to delete this user?" 
    : "Do you want to restore this user?";
  
  if (!confirm(confirmMsg)) return;

  fetch('toggle_user_delete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, is_deleted: newStatus })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (newStatus === 1) {
        // Change to Revert (Green)
        button.classList.remove('btn-danger');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Revert';
        button.setAttribute('onclick', `toggleUserDelete(${userId}, 0, this)`);
      } else {
        // Change back to Delete (Red)
        button.classList.remove('btn-success');
        button.classList.add('btn-danger');
        button.innerHTML = '<i class="bi bi-trash"></i> Delete';
        button.setAttribute('onclick', `toggleUserDelete(${userId}, 1, this)`);
      }
    } else {
      alert("❌ Error: " + data.message);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    alert("Unexpected error while toggling user delete state.");
  });
}

// Initialize DataTables on the user table
$(document).ready(function() {
    $('#all-users-table').DataTable({
        "columnDefs": [
            { "orderable": false, "targets": 5 } // Actions column should not be sortable
        ],
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ users",
            "infoEmpty": "No users available",
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });
});

</script>

<?php $template->footer($config); ?>
</body>
</html>
