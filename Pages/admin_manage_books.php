<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../Config/constants.php";
require "../Components/Template.php";
require "../Config/dbconnection.php";

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Manage Books');
$template->hero('Manage Books');

/* ==========================================
 * DATABASE CONNECTION AND DATA RETRIEVAL (PHP)
 * ========================================== */
$table_rows_html = '';
$COLSPAN_COUNT = 6; // ID, Title, Author, Category, ISBN, Actions

if (!isset($connection) || $connection->connect_error) {
    $table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center; color: red;">Database connection failed.</td></tr>';
} else {
    $sql = "SELECT 
                b.book_id, 
                b.title, 
                a.author_name AS author, 
                c.category_name AS category, 
                b.isbn,
                b.is_deleted
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.author_id
            LEFT JOIN categories c ON b.category_id = c.category_id
            ORDER BY b.book_id DESC";

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $bookId = htmlspecialchars($row['book_id']);
            $isDeleted = (int)$row['is_deleted'];

            // Delete / Revert button
            if ($isDeleted === 1) {
                $deleteButton = '<button class="btn btn-success" onclick="toggleBookDelete(' . $bookId . ', 0, this)">
                                    <i class="bi bi-arrow-counterclockwise"></i> Revert
                                 </button>';
            } else {
                $deleteButton = '<button class="btn btn-danger" onclick="toggleBookDelete(' . $bookId . ', 1, this)">
                                    <i class="bi bi-trash"></i> Delete
                                 </button>';
            }

            $actions = '<button class="btn btn-warning" onclick="editBook(' . $bookId . ')">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        ' . $deleteButton;

            $table_rows_html .= '<tr>
                                    <td>' . $bookId . '</td>
                                    <td>' . htmlspecialchars($row['title']) . '</td>
                                    <td>' . htmlspecialchars($row['author'] ?: 'N/A') . '</td>
                                    <td>' . htmlspecialchars($row['category'] ?: 'N/A') . '</td>
                                    <td>' . htmlspecialchars($row['isbn']) . '</td>
                                    <td>' . $actions . '</td>
                                 </tr>';
        }
    } else {
        $table_rows_html = '<tr><td colspan="' . $COLSPAN_COUNT . '" style="text-align: center;">No books found in the database.</td></tr>';
    }

    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management Dashboard</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <style>
        /* Same styles as user page */
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; padding: 0px; }
        .container { max-width: 1200px; margin: 0; }
        .card { background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; padding: 24px; margin-top: 20px; }
        .card h5 { font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer; margin: 2px; }
        .btn-dark { background-color: #1f2937; color: white; }
        .btn-dark:hover { background-color: #374151; }
        .btn-warning { background-color: #f59e0b; color: white; }
        .btn-warning:hover { background-color: #d97706; }
        .btn-danger { background-color: #ef4444; color: white; }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-success { background-color: #22c55e; color: white; }
        .btn-success:hover { background-color: #16a34a; }
        .form-control { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db; }
        .mb-2 { margin-bottom: 0.5rem; }
        #all-books-table { width: 100% !important; border-collapse: collapse; margin-top: 10px; }
        #all-books-table thead th { background-color: #1f2937; color: white; font-weight: 600; padding: 12px; border-bottom: 2px solid #374151; }
        #all-books-table tbody td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        #all-books-table tbody tr:nth-child(even) { background-color: #f9fafb; }
        #all-books-table tbody tr:hover { background-color: #eff6ff; }
        @media screen and (max-width: 768px) { .card { padding: 15px; } .table-responsive { overflow-x: auto; } }
    </style>
</head>
<body>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editBookForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editBookModalLabel">Edit Book</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_book_id">
          <div class="mb-3"><label class="form-label">Title</label><input type="text" id="edit_title" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Author</label><input type="text" id="edit_author" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Category</label>
              <select id="edit_category" class="form-select" required>
                  <option value="">Select Category</option>
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
          <div class="mb-3"><label class="form-label">ISBN</label><input type="text" id="edit_isbn" class="form-control" required></div>
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
      <div class="card p-4">
        <h5>Add New Book</h5>
        <form id="addBookForm">
          <div class="mb-2"><input type="text" id="add_title" class="form-control" placeholder="Title" required></div>
          <div class="mb-2"><input type="text" id="add_author" class="form-control" placeholder="Author" required></div>
          <div class="mb-2"><input type="text" id="add_isbn" class="form-control" placeholder="ISBN" required></div>
          <div class="mb-2">
            <select id="add_category" class="form-control" required>
              <option value="">Select Category</option>
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
          <button type="submit" class="btn btn-dark">Add Book</button>
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
              <?php echo $table_rows_html; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let editModal = new bootstrap.Modal(document.getElementById('editBookModal'));

function editBook(bookId) {
    let row = document.querySelector(`#all-books-table button[onclick="editBook(${bookId})"]`).closest("tr");
    $('#edit_book_id').val(bookId);
    $('#edit_title').val(row.children[1].textContent.trim());
    $('#edit_author').val(row.children[2].textContent.trim());
    $('#edit_category').val(row.children[3].textContent.trim());
    $('#edit_isbn').val(row.children[4].textContent.trim());
    editModal.show();
}

$('#editBookForm').on('submit', function(e){
    e.preventDefault();
    let bookId = $('#edit_book_id').val();
    let title = $('#edit_title').val();
    let author = $('#edit_author').val();
    let category = $('#edit_category').val();
    let isbn = $('#edit_isbn').val();

    fetch('update_book.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({book_id: bookId, title, author, category, isbn})
    }).then(r => r.json()).then(res => {
        if(res.success){ alert("Book updated!"); location.reload(); }
        else alert(res.message);
    });
});

$('#addBookForm').on('submit', function(e){
    e.preventDefault();
    let title = $('#add_title').val();
    let author = $('#add_author').val();
    let category = $('#add_category').val();
    let isbn = $('#add_isbn').val();

    if(!title || !author || !category || !isbn){ alert("Fill all fields."); return; }

    fetch('add_book.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({title, author, category, isbn})
    }).then(r => r.json()).then(res=>{
        if(res.success){ alert("Book added!"); location.reload(); }
        else alert(res.message);
    });
});

function toggleBookDelete(bookId, newStatus, button){
    if(!confirm(newStatus===1?"Delete this book?":"Restore this book?")) return;
    fetch('toggle_book_delete.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({book_id:bookId,is_deleted:newStatus})
    }).then(r=>r.json()).then(res=>{
        if(res.success){
            if(newStatus===1){
                button.classList.replace('btn-danger','btn-success');
                button.innerHTML='<i class="bi bi-arrow-counterclockwise"></i> Revert';
                button.setAttribute('onclick',`toggleBookDelete(${bookId},0,this)`);
            }else{
                button.classList.replace('btn-success','btn-danger');
                button.innerHTML='<i class="bi bi-trash"></i> Delete';
                button.setAttribute('onclick',`toggleBookDelete(${bookId},1,this)`);
            }
        }else alert(res.message);
    });
};

$(document).ready(function() { $('#all-books-table').DataTable({"columnDefs":[{"orderable":false,"targets":5}]}); });
</script>

<?php $template->footer($config); ?>
</body>
</html>
