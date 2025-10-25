<?php
// CRITICAL: Start the session to access login data and set messages
session_start();

// Ensure this is a POST request and the user is logged in (session email must exist)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION['user_email'])) {
    // If the user is not logged in or method is wrong, redirect to login
    header("Location: ../Pages/login.php"); 
    exit();
}

require "dbconnection.php";

// Function for secure redirection and message handling
function redirect_to_profile(string $message, string $type = 'success'): void {
    if ($type === 'success') {
        $_SESSION['success_message'] = $message;
    } else {
        $_SESSION['error_message'] = $message;
    }
    header("Location: ../Pages/user_profile.php");
    exit();
}

// Global identifier for the user based on the session
$user_email = $_SESSION['user_email'];

// Check database connection
if (!isset($connection) || $connection->connect_error) {
    error_log("Database connection error during user profile update.");
    redirect_to_profile("A critical server error occurred.", 'error');
}

// Get the form type to route the request
$form_type = $_POST['form_type'] ?? '';

// --- Handle Personal Information Update (Matches form_type=personal) ---
if ($form_type === 'personal') {
    // Retrieving separate first_name, last_name, and phone_number as per DB schema
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // Validation
    if (empty($first_name) || empty($last_name)) {
        redirect_to_profile("First Name and Last Name are required.", 'error');
    }

    $fields_to_update = [];
    $bind_params = [];
    $bind_types = "";

    // Dynamically building the UPDATE query using correct column names
    
    $fields_to_update[] = "first_name = ?";
    $bind_types .= "s";
    $bind_params[] = &$first_name;

    $fields_to_update[] = "last_name = ?";
    $bind_types .= "s";
    $bind_params[] = &$last_name;

    $fields_to_update[] = "phone_number = ?";
    $bind_types .= "s";
    $bind_params[] = &$phone_number;

    $set_clause = implode(", ", $fields_to_update);
    $sql = "UPDATE users SET {$set_clause} WHERE email = ?";

    // Add the user_email to the end of parameters for the WHERE clause
    $bind_types .= "s";
    $bind_params[] = &$user_email;

    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        error_log("Prepare statement failed (Personal Update): " . $connection->error);
        $connection->close();
        redirect_to_profile("Failed to prepare update statement. Please try again.", 'error');
    }

    // Execute statement securely
    if (!call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params))) {
        error_log("Bind param failed: " . $stmt->error);
        $stmt->close();
        $connection->close();
        redirect_to_profile("Failed to bind parameters for update.", 'error');
    }

    if ($stmt->execute()) {
        $rows_affected = $stmt->affected_rows;
        if ($rows_affected > 0) {
            redirect_to_profile("Personal information updated successfully!");
        } else {
            redirect_to_profile("No changes were made to your personal information.", 'error');
        }
    } else {
        error_log("Execute statement failed (Personal Update): " . $stmt->error);
        redirect_to_profile("An error occurred while updating your profile.", 'error');
    }
    $stmt->close();

// --- Handle Password Update (Matches form_type=password) ---
} elseif ($form_type === 'password') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        redirect_to_profile("Both password fields are required for a password change.", 'error');
    }

    if ($new_password !== $confirm_password) {
        redirect_to_profile("New password and confirmation password do not match.", 'error');
    }
    
    // Password strength check
    if (strlen($new_password) < 8) {
        redirect_to_profile("Password must be at least 8 characters long.", 'error');
    }

    // Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update query targeting the 'password_hash' column
    $sql = "UPDATE users SET password_hash = ? WHERE email = ?";
    
    $stmt = $connection->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare statement failed (Password Update): " . $connection->error);
        $connection->close();
        redirect_to_profile("Failed to prepare update statement. Please try again.", 'error');
    }

    $stmt->bind_param("ss", $hashed_password, $user_email);

    if ($stmt->execute()) {
        $rows_affected = $stmt->affected_rows;
        if ($rows_affected > 0) {
            redirect_to_profile("Password updated successfully! You will need to log in again soon.");
        } else {
            redirect_to_profile("Password was not changed.", 'error');
        }
    } else {
        error_log("Execute statement failed (Password Update): " . $stmt->error);
        redirect_to_profile("An error occurred while updating your password.", 'error');
    }

    $stmt->close();
} else {
    // Neither form type was submitted or form_type is missing
    redirect_to_profile("Invalid form submission.", 'error');
}

$connection->close();
?>
