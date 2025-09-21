<?php

class Book {
    public $title;
    public $author;
    public $rating;
    public $status;

    public function __construct($title, $author, $rating, $status){
        $this->title = $title;
        $this->author = $author;
        $this->rating = $rating;
        $this->status = $status;
    }

    public function showBookCard(){
        $statusBadge = $this->status === "available"
        ? '<span class="badge available">Available</span>'
        : '<span class="badge borrowed">Borrowed</span>';
        
        $button = $this->status === "available"
        ? '<button class="btn borrow">Borrow</button>'
        : '<button class="btn unavailable" disabled>Unavailable</button>';

        echo '
        
        <div class="book-card">
            <h3>' . htmlspecialchars($this->title) . '</h3>
            <p> by ' .htmlspecialchars($this->author) . '</p>' . $statusBadge . '
            <p class="rating">⭐ ' . $this->rating . '</p>' . $button . '
            </div>
        ';
    }
}

?>