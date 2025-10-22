<?php
require_once __DIR__ . '/../Config/constants.php';
require_once __DIR__ . '/../Components/LibraryComponents.php';

$template = new LibrarianTemplate();
$template->navArea($config);
$template->documentStart($config, 'Borrowings');
$template->hero('Borrowings');

$section = new BorrowingsSection();
echo '<div class="card card-modern"><div class="card-body">';
echo $section->renderContent();
echo '</div></div>';

$template->footer($config);
