<?php
class Template {

    // Navigation bar
    public function navArea($config) {
        ?>
        <nav class="navbar navbar-expand-lg header-nav" style="background-color:#0d6efd;">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#"><?php echo $config['Website_Name']; ?></a>
            <!-- hamburger menu button for small screens -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link text-white" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="admin_manage_books.php">Manage Books</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="admin_reports.php">Reports</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="admin_profile.php">Profile</a></li>
                <li class="nav-item">
                    <a class="btn btn-danger btn-modern ms-2" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            </div>
        </div>
        </nav>
        <?php
    }

    // Start main document
    public function documentStart($config, $pageTitle = '') {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>
                <?php   
                echo $pageTitle ? $pageTitle . " – " . $config['Website_Name'] : $config['Website_Name']; 
                ?>
            </title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
            <style>
                body { 
                    background-color: #f8f9fa; 
                    min-height: 100vh; 
                    display: flex; 
                    flex-direction: column; 
                }
                .content { 
                    padding: 20px; 
                    flex: 1; 
                }
                .footer { 
                    background-color: #fff; 
                    padding: 15px 20px; 
                    border-top: 1px solid #ddd; 
                    text-align: center; 
                    margin-top: auto; 
                }
                .card-modern {
                    border-radius: 12px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    transition: transform 0.2s ease;
                }
                .card-modern:hover {
                    transform: translateY(-5px);
                }
                .btn-modern {
                    border-radius: 8px;
                    padding: 10px 20px;
                    font-weight: 500;
                }
            </style>
        </head>
        <body>
        <div class="container content mt-4">
        <?php
    }

    // Page hero / heading
    public function hero($title) {
        ?>
        <div class="mb-4">
            <h2><?php echo $title; ?></h2>
        </div>
        <?php
    }

    // Footer
    public function footer($config) {
        ?>
        </div> 
        <div class="footer mt-4">
            &copy; <?php echo date('Y'); ?> <?php echo $config['Website_Name']; ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}
?>
