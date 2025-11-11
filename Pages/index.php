<?php 


class WelcomePage
{

    private string $title ="StorySphere";

    public function RenderPage():void{
        $this->Head();
        echo "<body style='margin: 0; width: 100%; padding: 0; font-family: \"Segoe UI\", Roboto, sans-serif; background-color: #ffffff; color: #333333;'>\n";
        // Use the minimal public navbar for the index page - moved outside container for full width
        include __DIR__ . '/../Components/public_navbar.php';
        echo "    <div style='max-width: 95%; margin: 0 auto; padding: 0 20px;'>\n";
        $this->Header();
        echo "        <main style='padding: 40px 0;'>\n";
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
<head style="width:100%">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->title}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2563eb;
            padding: 15px 30px;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
            max-width: 100vw;
        }
        
        .navbar .logo h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: bold;
        }
        
        .navbar .logo p {
            margin: 5px 0 0 0;
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .navbar .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 20px;
        }
        
        .navbar .nav-links li a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }
        
        .navbar .nav-links li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
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
            <section style="display: flex; align-items: center; gap: 50px; margin-bottom: 80px; padding: 50px; background-color: #f8f9fa; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <h2 style="font-size: 2.5em; margin: 0 0 25px 0; color: #3266be; font-weight: 700;">Discover. Create. Share.</h2>
                    <p style="font-size: 1.1em; line-height: 1.7; color: #333333; margin: 0;">Dive into a world where stories come alive. Explore our vast collection of narratives, create your own tales, and share them with a community of like-minded enthusiasts.</p>
                </div>
                <div style="flex: 1; min-width: 300px; text-align: center;">
                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=500&h=400&fit=crop" alt="Open books in library" style="max-width: 100%; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
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

        echo "            <section style=\"background: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1200&h=400&fit=crop') center/cover; padding: 70px 40px; border-radius: 15px; margin-bottom: 80px; position: relative; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);\">\n";
        echo "                <div style=\"position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.9); border-radius: 15px;\"></div>\n";
        echo "                <div style=\"position: relative; z-index: 1;\">\n";
        echo "                    <h2 style=\"font-size: 2.5em; margin: 0 0 50px 0; text-align: center; color: #3266be; font-weight: 700;\">Powerful Features</h2>\n";
        echo "                    <ul style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; list-style: none; padding: 0; margin: 0;\">\n";
        foreach ($features as $feature) {
            $safe = htmlspecialchars($feature, ENT_QUOTES, 'UTF-8');
            echo "                        <li style=\"background-color: #f8f9fa; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; color: #333333; font-size: 1em; font-weight: 500; transition: transform 0.3s ease;\" onmouseover=\"this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'\" onmouseout=\"this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.05)'\"><span style=\"display: inline-block; margin-right: 10px; color: #3266be; font-weight: bold; font-size: 1.2em;\">✓</span>{$safe}</li>\n";
        }
        echo "                    </ul>\n";
        echo "                </div>\n";
        echo "            </section>\n";
    }

   private function Search():void{
          echo <<<HTML
            <section style="background-color: #f8f9fa; padding: 50px 40px; border-radius: 15px; margin-bottom: 60px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div style="max-width: 600px; margin: 0 auto;">
                    <h2 style="font-size: 2.2em; margin: 0 0 30px 0; text-align: center; color: #3266be; font-weight: 600;">Search Our Collection</h2>
                    <form action="search.php" method="get" style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">
                        <input type="text" name="query" placeholder="Search for books, authors, genres..." required style="flex: 1; min-width: 250px; padding: 15px 20px; border: 2px solid #ddd; border-radius: 25px; font-size: 1em; font-family: 'Segoe UI', Roboto, sans-serif; background: white; color: #333333;" onfocus="this.style.borderColor='#3266be'" onblur="this.style.borderColor='#ddd'">
                        <button type="submit" style="padding: 15px 30px; background-color: #3266be; color: white; border: none; border-radius: 25px; cursor: pointer; font-size: 1em; font-weight: 600; transition: box-shadow 0.3s ease;" onmouseover="this.style.boxShadow='0 4px 15px rgba(50, 102, 190, 0.3)'" onmouseout="this.style.boxShadow='none'">Search</button>
                    </form>
                </div>
            </section>
HTML;
    }

    private function GetStarted():void{
          echo <<<HTML
            <section style="text-align: center; padding: 70px 40px; background-color: #3266be; border-radius: 15px; color: white; margin-bottom: 60px; box-shadow: 0 4px 20px rgba(50, 102, 190, 0.3);">
                <h2 style="font-size: 2.5em; margin: 0 0 25px 0; font-weight: 700;">Get Started Today</h2>
                <p style="font-size: 1.15em; max-width: 600px; margin: 0 auto 40px; line-height: 1.7;">Create an account to start your journey with StorySphere. Enjoy exclusive content and features tailored just for you.</p>
                <a href="signUp.php" style="background-color: white; color: #3266be; padding: 16px 40px; text-decoration: none; border-radius: 25px; font-weight: 600; display: inline-block; transition: box-shadow 0.3s ease; font-size: 1.05em;" onmouseover="this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.boxShadow='none'">Sign Up Now</a>
            </section>
HTML;
    }

    private function WhyStorySphere():void{
        echo <<<HTML
            <section style="margin-bottom: 80px;">
                <h2 style="font-size: 2.5em; margin: 0 0 50px 0; text-align: center; color: #3266be; font-weight: 700;">Why StorySphere?</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <div style="background-color: #f8f9fa; padding: 35px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.05)'">
                        <h3 style="color: #3266be; margin: 0 0 20px 0; font-size: 1.4em; font-weight: 600;">📚 Efficient Management</h3>
                        <p style="color: #333333; line-height: 1.7; margin: 0; font-size: 1em;">Keep track of all your library resources with our intuitive cataloging system. Easily manage loans, returns, and reservations in one place.</p>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 35px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.05)'">
                        <h3 style="color: #3266be; margin: 0 0 20px 0; font-size: 1.4em; font-weight: 600;">🔍 Smart Organization</h3>
                        <p style="color: #333333; line-height: 1.7; margin: 0; font-size: 1em;">Organize books by genre, author, or custom categories. Our advanced search helps you locate any book in your collection instantly.</p>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 35px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.05)'">
                        <h3 style="color: #3266be; margin: 0 0 20px 0; font-size: 1.4em; font-weight: 600;">👥 Member Management</h3>
                        <p style="color: #333333; line-height: 1.7; margin: 0; font-size: 1em;">Maintain detailed member profiles, track borrowing history, and manage library cards efficiently. Set custom loan periods and handle renewals seamlessly.</p>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 35px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border: 1px solid #e9ecef; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.05)'">
                        <h3 style="color: #3266be; margin: 0 0 20px 0; font-size: 1.4em; font-weight: 600;">📊 Resource Tracking</h3>
                        <p style="color: #333333; line-height: 1.7; margin: 0; font-size: 1em;">Monitor your library's inventory, get notifications for overdue books, and generate detailed reports on library usage and popular titles.</p>
                    </div>
                </div>
            </section>
HTML;
    }


    private function Footer():void{
        $year = date("Y");
        echo <<<HTML
            <footer style="text-align: center; padding: 30px 20px; background-color: #f8f9fa; border-radius: 10px; margin-top: 40px; font-size: 0.95em; color: #666666; border: 1px solid #e9ecef;">
                <p style="margin: 0;">&copy; {$year} <span style="color: #3266be; font-weight: 600;">StorySphere</span>. All rights reserved.</p>
            </footer>
HTML;

}
}
$page =new WelcomePage();
$page->RenderPage();
   
?>