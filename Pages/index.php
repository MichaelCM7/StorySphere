<?php 


class WelcomePage
{

    private string $title ="StorySphere";

    public function RenderPage():void{
        $this->Head();
        echo "<body>\n";
        echo "    <div class=\"container\">\n";
        // Use the minimal public navbar for the index page
        include __DIR__ . '/../Components/public_navbar.php';
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
    <link rel="stylesheet" href="../user_style.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <style>
        /* Enhanced styling for welcome page */
        header {
            position: relative;
           background: linear-gradient(rgba(10, 8, 8, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1200&h=400&fit=crop') center/cover;

            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        
        header h1 {
            font-size: 3em;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        
        header p {
            font-size: 1.2em;
            max-width: 800px;
            margin: 0 auto;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        
        .search {
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.1), rgba(37, 117, 252, 0.1));
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .intro {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 50px;
            padding: 30px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        
        .intro-content {
            flex: 1;
        }
        
        .intro-image {
            flex: 1;
            text-align: center;
        }
        
        .intro-image img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .features {
            background: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1200&h=400&fit=crop') center/cover;
            background-attachment: fixed;
            padding: 60px 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            position: relative;
        }
        
        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 15px;
        }
        
        .features h2,
        .features ul {
            position: relative;
            z-index: 1;
            color: white;
        }
        
        .features ul {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            list-style: none;
            padding: 0;
        }
        
        .features li {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        
        .features li:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .features li::before {
           
            display: inline-block;
            margin-right: 10px;
            color: #4CAF50;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .why-storysphere {
            margin-bottom: 50px;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .benefit {
            background: rgba(255,255,255,0.05);
            padding: 30px;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .benefit::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #6A11CB, #2575FC);
        }
        
        .benefit:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .benefit h3 {
            color: #2575FC;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .get-started {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, #3d3f4170);
            border-radius: 15px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .get-started::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=800&h=600&fit=crop') center/cover;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        
        .get-started h2,
        .get-started p {
            position: relative;
            z-index: 1;
        }
        
        .get-started .btn {
            background: white;
            color: #3266beff;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .get-started .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .search-container form {
            display: flex;
            gap: 10px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-container input {
            flex: 1;
            padding: 12px 20px;
            border-radius: 25px;
            border: 2px solid rgba(106, 17, 203, 0.3);
        }
        
        .search-container button {
            padding: 12px 25px;
            background: linear-gradient(  #3266beff);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.95em;
            transition: transform 0.3s ease;
        }
        
        .search-container button:hover {
            transform: scale(1.05);
        }
        
        footer {
            text-align: center;
            padding: 5px;
            background: rgba(237, 240, 243, 0.3);
            border-radius: 10px;
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        @media (max-width: 768px) {
            .intro {
                flex-direction: column;
            }
            
            header h1 {
                font-size: 2em;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
HTML;
    }
    
    // Navbar is now included from user_navbar.php
    
   private function Header():void{
        echo <<<HTML
        <header>
            <h1>Welcome to {$this->title}</h1>
            <p>Your gateway to immersive storytelling experiences. This is your personal library brought even closer to you. Be it research or leisure, we've got you covered</p>
        </header>
HTML;
    }

    private function Intro():void{
        echo <<<HTML
            <section class="intro">
                <div class="intro-content">
                    <h2>Discover. Create. Share.</h2>
                    <p>Dive into a world where stories come alive. Explore our vast collection of narratives, create your own tales, and share them with a community of like-minded enthusiasts.</p>
                </div>
                <div class="intro-image">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=500&h=400&fit=crop" alt="Open books in library">
                </div>
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
                        <h3>📚 Efficient Management</h3>
                        <p>Keep track of all your library resources with our intuitive cataloging system. Easily manage loans, returns, and reservations in one place.</p>
                    </div>
                    <div class="benefit">
                        <h3>🔍 Smart Organization</h3>
                        <p>Organize books by genre, author, or custom categories. Our advanced search helps you locate any book in your collection instantly.</p>
                    </div>
                    <div class="benefit">
                        <h3>👥 Member Management</h3>
                        <p>Maintain detailed member profiles, track borrowing history, and manage library cards efficiently. Set custom loan periods and handle renewals seamlessly.</p>
                    </div>
                    <div class="benefit">
                        <h3>📊 Resource Tracking</h3>
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