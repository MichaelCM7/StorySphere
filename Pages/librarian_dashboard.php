<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/Librarian/LibraryComponents.php';

// Session check placeholder (align with your existing auth if needed)
// session_start();
// if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'librarian') {
//     header('Location: ../Pages/signIn.php');
//     exit();
// }

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Librarian Dashboard');
$template->hero('Librarian Dashboard');

$sections = [
    new StatsTilesSection(),
    new BookInventorySection(),
    new BorrowingsSection(),
    new OverdueAlertsSection(),
    new PendingReservationsSection(),
    new FineManagementSection(),
];

foreach ($sections as $section) {
    echo '<div class="card card-modern mb-4">';
    echo '  <div class="card-body">';
    echo '    <h5 class="card-title mb-3">' . htmlspecialchars($section->getTitle()) . '</h5>';
    echo          $section->renderContent();
    echo '  </div>';
    echo '</div>';
}

$template->footer($config);
