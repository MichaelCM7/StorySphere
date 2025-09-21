<?php

class Content{
    private $books = [];

    public function __construct($books){
        $this->books = $books;
    }

    public function showContent(){
        echo '<main class="content">';
        echo '<h2>Available Books</h2>';
        echo '<div class="book-grid">';
        foreach ($this->books as $book){
            $book->showBookCard();
        }
        echo '</div>';
        echo '</main>';
    }
}

?>