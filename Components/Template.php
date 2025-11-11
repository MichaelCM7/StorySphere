<?php
class Template {

    // Navigation bar
    public function navArea($config) {
        ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .navbar-custom {
            background: #1a56db ;
            padding: 10px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0px;
            color: white;
        }

        .navbar-logo-top {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .navbar-logo-subtitle {
            color: white;
            font-size: 13px;
            font-weight: 400;
            margin-top: 0;
            margin-left: 0;
        }

        .navbar-links {
            display: flex;
            list-style: none;
            gap: 30px;
            margin: 0;
            padding: 0;
        }

        .navbar-links li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            transition: opacity 0.3s ease;
        }

        .navbar-links li a:hover {
            opacity: 0.8;
        }

        .navbar-links li a i {
            font-size: 18px;
        }
    </style>

    <div class="navbar-custom">
        <div class="navbar-logo">
            <div class="navbar-logo-top">
                <i class="fa-solid fa-book"></i>
                <?= htmlspecialchars($config['Website_Name'] ?? 'StorySphere') ?>
            </div>
            <div class="navbar-logo-subtitle">Library Management</div>
        </div>

        <ul class="navbar-links">
            <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
            <li><a href="admin_manage_books.php"><i class="fa-solid fa-book"></i> Books</a></li>
            <li><a href="admin_manage_users.php"><i class="fa-solid fa-book-reader"></i> Manage Users</a></li>
            <li><a href="admin_reports.php"><i class="fa-solid fa-chart-line"></i> Reports</a></li>
            <li><a href="admin_profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>
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
                echo $pageTitle ? $pageTitle . " - " . $config['Website_Name'] : $config['Website_Name']; 
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
        <div class="mb-4" style="margin-top: 30px;">
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
