<?php
// Welcome page for Story Sphere
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Sphere</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head> 
<body>
    <div class="container">
        <header>
            <h1>Welcome to Story Sphere</h1>
            <p>Your gateway to immersive storytelling experiences. This is your personal library brought even closer to you</p>
        </header>
        <main>
            <section class="intro">
                <h2>Discover New Worlds</h2>
                <p>Explore a vast collection of stories from various genres and authors. Dive into adventures, mysteries, romances, and more.</p>
            </section>
            <section class="features">
                <h2>Features</h2>
                <ul>
                    <li>Personalized Recommendations</li>
                    <li>Offline Reading Mode</li>
                    <li>Community Reviews and Ratings</li>
                    <li>Author Spotlights and Interviews</li>
                </ul>
            </section>
            <section class="get-started">
                <h2>Get Started</h2>
                <p>Create an account to start your journey with Story Sphere. Enjoy exclusive content and features tailored just for you.</p>
                <a href="signup.html" class="btn">Sign Up Now</a>
            </section>
        </main>
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Story Sphere. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
