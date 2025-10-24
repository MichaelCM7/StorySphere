<?php
session_start();

require_once 'dbconnection.php';

function redirect_to(string $path): void {
    header("Location: " . $path);
    exit;
}

if (!isset($_POST['email'], $_POST['password'])) {
    $_SESSION['login_error'] = "Email and password are required.";
    redirect_to("../Pages/signIn.php");
}

$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
$password = $_POST["password"]; 

if (empty($email) || empty($password) || !isset($connection)) {
    $_SESSION['login_error'] = "Missing email or password.";
    redirect_to("../Pages/signIn.php");
}

$sql = "SELECT password_hash, role_id FROM users WHERE email = ? LIMIT 1;";
$stmt = $connection->prepare($sql);

if (!$stmt) {
    $_SESSION['login_error'] = "A server error occurred during authentication.";
    redirect_to("../Pages/signIn.php");
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    $_SESSION['login_error'] = "A database error occurred. Please try again.";
    redirect_to("../Pages/signIn.php");
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $stored_hash = $user["password_hash"];
    $storedRole = (int)$user["role_id"];

    // Verify the submitted password against the stored hash
    if (password_verify($password, $stored_hash)) {

        // Prevent session fixation attacks
        session_regenerate_id(true);

        // Store session details
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['role_id'] = $storedRole;

        switch ($storedRole) {
            case 1:
                redirect_to("../Pages/admin_dashboard.php");
                break;
            case 2:
                redirect_to("../Pages/librarian_dashboard.php");
                break;
            case 3:
                redirect_to("../Pages/user_index_dashboard.php");
                break;
            default:
                session_unset();
                $_SESSION['login_error'] = "Login failed. Unknown user role.";
                redirect_to("../Pages/signIn.php");
                break;
        }

    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
        redirect_to("../Pages/signIn.php");
    }

} else {
    $_SESSION['login_error'] = "Invalid email or password.";
    redirect_to("../Pages/signIn.php");
}

$stmt->close();
$connection->close();

?>
