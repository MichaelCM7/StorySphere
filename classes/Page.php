<?php
require_once __DIR__ . '/Header.php';
require_once __DIR__ . '/Footer.php';
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Content.php';

class Page {
    public $header;
    public $footer;
    public $content;

    public function __construct($books){
        $this->header = new Header();
        $this->footer = new Footer();
        $this->content = new Content($books);
    }

    public function showPage(){
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Story Sphere</title>
            <link rel="stylesheet" href="assets/css/style.css">
        </head>
        <body>';
        
        $this->header->showHeader();
        $this->content->showContent();
        $this->footer->showFooter();

        echo '</body></html>';
    }

}

?>