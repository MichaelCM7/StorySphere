<?php
// CRITICAL: Start the session to access login data and set messages
session_start();

// Ensure this is a POST request and the user is logged in
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION['user_email'])) {
    header("Location: ../Pages/signIn.php");
    exit();
}

require "dbconnection.php";

// Function for secure redirection
function redirect_to_profile(string $message, string $type = 'success'): void {
    if ($type === 'success') {
        $_SESSION['success_message'] = $message;
    } else {
        $_SESSION['error_message'] = $message;
    }
    header("Location: ../Pages/admin_profile.php");
    exit();
}

// 1. Get and Sanitize Input
$user_email = $_SESSION['user_email']; // The identifier for the WHERE clause
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation for required fields
if (empty($first_name) || empty($last_name)) {
    redirect_to_profile("First and Last Name are required.", 'error');
}

// Check database connection
if (!isset($connection) || $connection->connect_error) {
    error_log("Database connection error during profile update.");
    redirect_to_profile("A critical server error occurred.", 'error');
}

// 2. Prepare the Query and Parameters
$fields_to_update = [];
$bind_params = [];
$bind_types = "";

// Always include name and phone fields
$fields_to_update[] = "first_name = ?";
$bind_types .= "s";
$bind_params[] = &$first_name;

$fields_to_update[] = "last_name = ?";
$bind_types .= "s";
$bind_params[] = &$last_name;

$fields_to_update[] = "phone_number = ?";
$bind_types .= "s";
$bind_params[] = &$phone_number;


// 3. Handle Password Update (Optional)
if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        redirect_to_profile("New password and confirmation password do not match.", 'error');
    }
    
    // Passwords must meet a minimum security standard (e.g., 8 characters)
    if (strlen($new_password) < 8) {
        redirect_to_profile("Password must be at least 8 characters long.", 'error');
    }

    // Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $fields_to_update[] = "password_hash = ?";
    $bind_types .= "s";
    $bind_params[] = &$hashed_password;
}


// 4. Construct and Execute the UPDATE Query
$set_clause = implode(", ", $fields_to_update);
$sql = "UPDATE users SET {$set_clause} WHERE email = ?";

// Add the user_email to the end of parameters for the WHERE clause
$bind_types .= "s";
$bind_params[] = &$user_email;

$stmt = $connection->prepare($sql);

if (!$stmt) {
    error_log("Prepare statement failed (Update Profile): " . $connection->error);
    $connection->close();
    redirect_to_profile("Failed to prepare update statement. Please try again.", 'error');
}

// Bind parameters dynamically
if (!call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params))) {
    error_log("Bind param failed: " . $stmt->error);
    $stmt->close();
    $connection->close();
    redirect_to_profile("Failed to bind parameters for update.", 'error');
}

if ($stmt->execute()) {
    $rows_affected = $stmt->affected_rows;
    if ($rows_affected > 0) {
        redirect_to_profile("Profile updated successfully!");
    } else {
        // This is safe: either the data was the same, or something went wrong.
        redirect_to_profile("No changes were made to your profile. If you intended to change your password, please check the requirements.", 'error');
    }
} else {
    error_log("Execute statement failed (Update Profile): " . $stmt->error);
    redirect_to_profile("An error occurred while updating the profile. Please try again.", 'error');
}

$stmt->close();
$connection->close();
?>
