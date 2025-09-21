<?php

require_once __DIR__ . '/classes/Page.php';
$books = require __DIR__ . '/data/books.php';

$page = new Page($books);
$page->showPage();

?>