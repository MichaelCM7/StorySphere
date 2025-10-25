<?php
session_start();
ini_set('display_errors', '1');
error_reporting(E_ALL);

include '../Components/auth_guard.php'; // This should start the session and set $user
require "../Config/dbconnection.php"; 

$user_email = $_SESSION['user_email'] ?? ($user['email'] ?? null); 
$profile = [
    'first_name' => '', 
    'last_name' => '',  
    'email' => '',      
    'phone_number' => ''
];

// Check for and handle any messages from the update script
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']); 


if ($user_email && isset($connection) && $connection->connect_error === null) {
    $sql = "SELECT first_name, last_name, phone_number, email FROM users WHERE email = ? AND is_deleted = 0 LIMIT 1";
    $stmt = $connection->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $db_data = $result->fetch_assoc();
            
            // Sanitize and assign data using correct keys
            $profile['first_name'] = htmlspecialchars($db_data['first_name'] ?? '');
            $profile['last_name'] = htmlspecialchars($db_data['last_name'] ?? '');
            $profile['phone_number'] = htmlspecialchars($db_data['phone_number'] ?? '');
            $profile['email'] = htmlspecialchars($db_data['email'] ?? '');
        }
        $stmt->close();
    }
    $connection->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | StorySphere</title>
    <link rel="stylesheet" href="../user_style.css?v=<?= filemtime(__DIR__.'/../user_style.css') ?>">
    <!-- Add simple styles for clarity and aesthetics -->
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../Components/user_navbar.php'; ?>
        <main>
            <?php include '../Components/user_header.php'; ?>
            <h1>Your Profile</h1>
            <p class="subtitle">Manage your account details and security settings</p>

            <!-- Display Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert"><?= $success_message ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error" role="alert"><?= $error_message ?></div>
            <?php endif; ?>


            <div class="profile-container">
                <!-- FORM 1: Personal Information Update -->
                <!-- The action attribute ensures submission goes to the update handler -->
                <form class="profile-form" action="../Config/user_profile_update.php" method="POST">
                    <h2>Personal Information</h2>
                    <!-- This hidden field is crucial for routing in the handler script -->
                    <input type="hidden" name="form_type" value="personal">

                    <!-- Value attributes display the fetched data from $profile array -->
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($profile['first_name']); ?>" required>

                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($profile['last_name']); ?>" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($profile['email']); ?>" readonly>

                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($profile['phone_number']); ?>">

                    <button type="submit" class="save-btn">Save Changes</button>
                </form>

                <!-- FORM 2: Password Update -->
                <!-- The action attribute ensures submission goes to the update handler -->
                <form class="profile-form" action="../Config/user_profile_update.php" method="POST">
                    <h2>Change Password</h2>
                    <!-- This hidden field is crucial for routing in the handler script -->
                    <input type="hidden" name="form_type" value="password">
                    
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>

                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>

                    <button type="submit" class="save-btn">Update Password</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
