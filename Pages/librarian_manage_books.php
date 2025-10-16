<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/Librarian/LibraryComponents.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Manage Books');
$template->hero('Manage Books');

echo '<div class="mb-3 d-flex justify-content-end">';
echo '  <a href="librarian_book_create.php" class="btn btn-success btn-modern"><i class="bi bi-plus-circle me-1"></i> Add Book</a>';
echo '</div>';

// Reuse list page for more complete management
require __DIR__ . '/librarian_books_list.php';
