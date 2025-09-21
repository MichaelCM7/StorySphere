<?php /// Welcome page for Story Sphere


class WelcomePage
{

    private string $title ="StorySphere";

    public function RenderPage():void{
     $this->Head();
        echo "<body>\n";
        echo "    <div class=\"container\">\n";
        $this->Header();
        echo "        <main>\n";
        $this->Intro();
        $this->Features();
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>
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

    private function GetStarted():void{
          echo <<<HTML
            <section class="get-started">
                <h2>Get Started</h2>
                <p>Create an account to start your journey with Story Sphere. Enjoy exclusive content and features tailored just for you.</p>
                <a href="Signup.php" class="btn">Sign Up Now</a>
            </section>
HTML;
    }

    private function Footer():void{
        $year = date("Y");
        echo <<<HTML
        <footer>
            <p>&copy; {$year} Story Sphere. All rights reserved.</p>
        </footer>
HTML;
    }
}
$page =new WelcomePage();
$page->RenderPage();
   
?>
