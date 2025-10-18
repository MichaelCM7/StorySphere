<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/IAP/styles/tables.css">
<title>Sign In Submit</title>
</head>
<body>
<?php
// Start the session at the very beginning
session_start();

// Ensure this file exists and handles your database connection setup.
require 'dbconnection.php';

// Define constants for better readability and maintainability
define('ROLE_ADMIN', 1);
define('ROLE_LIBRARIAN', 2);
define('ROLE_USER', 3);

// Function for secure redirection
function redirect_to(string $path): void {
    header("Location: " . $path);
    exit;
}

// Get and sanitize input
$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
$password = $_POST["password"];

// Check if email and password were provided and connection is valid
if (empty($email) || empty($password) || !isset($connection)) {
    $_SESSION['login_error'] = "Missing email or password.";
    redirect_to("../Pages/login_page.php");
}

// Prepare the statement to fetch the hashed password AND role_id (merged)
$sql = "SELECT password_hash, role_id FROM users WHERE email = ? LIMIT 1;";
$stmt = $connection->prepare($sql);

if (!$stmt) {
    // Handle prepare error
    $connection->close();
    $_SESSION['login_error'] = "A server error occurred during authentication.";
    redirect_to("../Pages/login_page.php");
}

$stmt->bind_param("s", $email); 
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if exactly one user was found
if ($user) {
    $stored_hash = $user["password_hash"];
    $storedRole = (int)$user["role_id"];

    // Verify the submitted password against the stored hash
    if (password_verify($password, $stored_hash)) {
        
        session_regenerate_id(true); // Prevents Session Fixation
        
        // Session setup
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['role_id'] = $storedRole;

        // role navigation using constants
        switch ($storedRole) {
            case ROLE_ADMIN:
                redirect_to("../Pages/admin_dashboard.php");
                break;
            case ROLE_LIBRARIAN:
                redirect_to("../Pages/librarian_dashboard.php");
                break;
            case ROLE_USER:
                redirect_to("../Pages/user_index_dashboard.php");
                break;
            default:
                // Unknown role, fail safe
                session_unset();
                session_destroy();
                $_SESSION['login_error'] = "Login failed. Unknown user role.";
                redirect_to("../Pages/login_page.php");
                break;
        }

    } else {
        // Generic failure message for security
        $_SESSION['login_error'] = "Invalid email or password.";
        redirect_to("../Pages/login_page.php");
    } 
} else {
    // Generic failure message for security
    $_SESSION['login_error'] = "Invalid email or password.";
    redirect_to("../Pages/login_page.php");
}

$stmt->close();
$connection->close();

// This line will only be reached on execution failure before redirect.
exit;
?>
</body>
</html>