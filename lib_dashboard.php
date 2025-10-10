<?php
class LibraryDashboard
{
    private string $title ="StorySphere";

    public function RenderPage():void{
     $this->Head();
        echo "<body>\n";
        echo "    <div class=\"container\">\n";
        $this->Navbar();

    }
     public function BookInventory():void {
        echo <<<HTML
        <section class="book-inventory">
            <h2>Your Book Inventory</h2>
            <p>Manage the libraries collection of books.</p>
            <a href="Inventory.php" class="btn">Manage Inventory</a>
        </section>   
     }
  public function Borrowings():void {
        echo <<<HTML
        <section class="borrowings">
            <h2>Current Borrowings</h2>
            <p>View and manage current borrowings.</p>
            <a href="Borrowings.php" class="btn">View Borrowings</a>
        </section>   
     }
}