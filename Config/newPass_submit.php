<?php
session_start();
require_once '../Config/dbconnection.php';

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    echo "<script>alert('Invalid session. Please start the password reset process again.'); window.location.href='../Pages/forgotPass.php';</script>";
    exit();
}

try {
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['Cpassword']);
    $email = $_SESSION['reset_email'];

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
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password in database
    $query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if ($stmt->execute()) {
        // Clear all session data
        unset($_SESSION['otp_verified'], $_SESSION['reset_email']);
        
        echo "<script>alert('Password reset successful! Please login with your new password.'); window.location.href='../Pages/signIn.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update password. Please try again.'); window.history.back();</script>";
        exit();
    }

    $stmt->close();
    $connection->close();

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo "<script>alert('An error occurred. Please try again later.'); window.history.back();</script>";
    exit();
}
?>