<?php
class OverduePage {
    private $db;

    public function __construct($database = null)
    {
        $this->db = $database;
    }

    public function renderPage()
    {
        // Establish database connection
        if (!$this->db) {
            $this->db = new PDO('mysql:host=localhost;dbname=storysphere', 'root', 'pilos3245@2005');
        }

        // Query to retrieve overdue books
        $query = "SELECT b.title, b.author, b.isbn, b.borrowed_at, b.due_at, b.returned_at
                  FROM books b
                  WHERE b.returned_at IS NULL AND b.due_at < NOW()";
        $statement = $this->db->prepare($query);
        $statement->execute();

        // Fetch the results
        $overdueBooks = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Display the overdue books on the page
        if (count($overdueBooks) > 0) {
            echo '<h2>Overdue Books</h2>';
            echo '<ul>';
            foreach ($overdueBooks as $book) {
                echo '<li>';
                echo '<h3>' . htmlspecialchars($book['title']) . '</h3>';
                echo '<p>Author: ' . htmlspecialchars($book['author']) . '</p>';
                echo '<p>ISBN: ' . htmlspecialchars($book['isbn']) . '</p>';
                echo '<p>Borrowed At: ' . htmlspecialchars($book['borrowed_at']) . '</p>';
                echo '<p>Due At: ' . htmlspecialchars($book['due_at']) . '</p>';
                echo '<p>Status: Overdue</p>';
                echo '</li>';
            }
            echo '</ul>';            
        } else {
            echo '<p>No overdue books at the moment.</p>';                      
        }
    }
}       
?>
<?php