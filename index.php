<?php /// Welcome page for Story Sphere


class WelcomePage
{

    private string $title ="StorySphere";

    public function RenderPage():void{
     $this->Head();
        echo "<body>\n";
        echo "    <div class=\"container\">\n";
        $this->Navbar();
        $this->Header();
        echo "        <main>\n";
        $this->Search();
        $this->Intro();
        $this->Features();
        $this->WhyStorySphere();
        $this->GetStarted();
        echo "        </main>\n";
        $this->Footer();
        echo "    </div>\n";
        echo "</body>\n</html>\n";
    }

    private function Head():void{
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->title}</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/css/search.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>
HTML;
    }
    
    private function Navbar():void{
        echo <<<HTML
        <nav class="navbar">
            <a class="brand" href="Welcome.php">{$this->title}</a>
            <div class="links">
                <a href="Login.php" class="nav-link">Log in</a>
                <a href="Signup.php" class="btn">Sign up</a>
            </div>
        </nav>
HTML;
    }
    
   private function Header():void{
        echo <<<HTML
        <header>
            <h1>Welcome to {$this->title}</h1>
            <p>Your gateway to immersive storytelling experiences. This is your personal library brought even closer to you. Be it research or leisure,we've got you covered</p>
        </header>
HTML;
    }

    private function Intro():void{
        echo <<<HTML
            <section class="intro">
                <h2>Discover. Create. Share.</h2>
                <p>Dive into a world where stories come alive. Explore our vast collection of narratives, create your own tales, and share them with a community of like-minded enthusiasts.</p>
            </section>
HTML;
    }

    private function Features():void{
              $features = [
            'Personalized Recommendations',
            'Offline Reading Mode',
            'Community Reviews and Ratings',
            'Author Spotlights and Interviews',
            'Both physical and digital book options',
        ];

        echo "            <section class=\"features\">\n";
        echo "                <h2>Features</h2>\n";
        echo "                <ul>\n";
        foreach ($features as $feature) {
            $safe = htmlspecialchars($feature, ENT_QUOTES, 'UTF-8');
            echo "                    <li>{$safe}</li>\n";
        }
        echo "                </ul>\n";
        echo "            </section>\n";
    }

   private function Search():void{
          echo <<<HTML
            <section class="search">
                <div class="search-container">
                    <h2>Search Our Collection</h2>
                    <form action="search.php" method="get">
                        <input type="text" name="query" placeholder="Search for books, authors, genres..." required>
                        <button type="submit">Search</button>
                    </form>
                </div>
            </section>
HTML;
    }

    private function GetStarted():void{
          echo <<<HTML
            <section class="get-started">
                <h2>Get Started</h2>
                <p>Create an account to start your journey with Story Sphere. Enjoy exclusive content and features tailored just for you.</p>
                <a href="Signup.php" class="btn">Sign Up Now</a>
            </section>
HTML;
    }

    private function WhyStorySphere():void{
        echo <<<HTML
            <section class="why-storysphere">
                <h2>Why StorySphere?</h2>
                <div class="benefits-grid">
                    <div class="benefit">
                        <h3>Efficient Management</h3>
                        <p>Keep track of all your library resources with our intuitive cataloging system. Easily manage loans, returns, and reservations in one place.</p>
                    </div>
                    <div class="benefit">
                        <h3>Smart Organization</h3>
                        <p>Organize books by genre, author, or custom categories. Our advanced search helps you locate any book in your collection instantly.</p>
                    </div>
                    <div class="benefit">
                        <h3>Member Management</h3>
                        <p>Maintain detailed member profiles, track borrowing history, and manage library cards efficiently. Set custom loan periods and handle renewals seamlessly.</p>
                    </div>
                    <div class="benefit">
                        <h3>Resource Tracking</h3>
                        <p>Monitor your library's inventory, get notifications for overdue books, and generate detailed reports on library usage and popular titles.</p>
                    </div>
                </div>
            </section>
HTML;
    }


    private function Footer():void{
        $year = date("Y");
        echo <<<HTML
            <footer>
                <p>&copy; {$year} StorySphere. All rights reserved.</p>
            </footer>
HTML;

}
}
$page =new WelcomePage();
$page->RenderPage();
   
?>
