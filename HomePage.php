<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/classes/Page.php';
$books = require __DIR__ . '/data/books.php';

$page = new Page($books);
$page->showPage();

?>