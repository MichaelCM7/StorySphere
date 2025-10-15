<?php
require "../Config/constants.php";
require "../Components/Template.php";

$template = new Template();
$template->navArea($config);
$template->documentStart($config, 'Manage Books');
$template->hero('Manage Books');
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-modern p-4">
            <h5>Add New Book</h5>
            <form>
                <div class="mb-2">
                    <input type="text" class="form-control" placeholder="Title">
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control" placeholder="Author">
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control" placeholder="Category">
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control" placeholder="ISBN">
                </div>
                <button class="btn btn-dark btn-modern">Add Book</button>
            </form>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card card-modern p-4">
            <h5>All Books</h5>
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
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
                    <tr>
                        <td>1</td>
                        <td>Book 1</td>
                        <td>Author 1</td>
                        <td>Fiction</td>
                        <td>978-0-306-40615-7</td>
                        <td>
                            <button class="btn btn-warning btn-modern btn-sm"><i class="bi bi-pencil"></i> Edit</button>
                            <button class="btn btn-danger btn-modern btn-sm"><i class="bi bi-trash"></i> Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$template->footer($config);
?>
