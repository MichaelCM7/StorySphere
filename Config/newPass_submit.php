<?php
session_start();
require_once '../Config/dbconnection.php';

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    echo "<script>alert('Invalid session. Please start the password reset process again.'); window.location.href='../Pages/forgotPass.php';</script>";
    exit();
}

try {
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['Cpassword'] ?? '');
    $email = $_SESSION['reset_email'];

    // Debug output (remove in production)
    error_log("Password length: " . strlen($password));
    error_log("Email: " . $email);

    // Validate passwords match
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Validate password length
    if (strlen($password) < 8 || strlen($password) > 50) {
        echo "<script>alert('Password must be between 8 and 50 characters.'); window.history.back();</script>";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if connection exists
    if (!isset($connection) || $connection->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Update password in database
    $query = "UPDATE users SET password_hash = ? WHERE email = ?";  // Note: changed 'password' to 'password_hash' if that's your column name
    $stmt = $connection->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        // Clear all session data
        unset($_SESSION['otp_verified'], $_SESSION['reset_email']);
        
        echo "<script>alert('Password reset successful! Please login with your new password.'); window.location.href='../Pages/signIn.php';</script>";
        exit();
    } else {
        echo "<script>alert('No account found with this email.'); window.history.back();</script>";
        exit();
    }

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo "<script>alert('An error occurred: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($connection)) {
        $connection->close();
    }
}
?>