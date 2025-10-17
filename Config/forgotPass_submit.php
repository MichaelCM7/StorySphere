<?php
session_start();

// Include required files
require_once '../Utils/otp.php';
require_once '../Config/mail.php';
require_once __DIR__ .'/../Secure/secureInfo.php';
require_once '../Config/dbconnection.php'; 

try {
    // Get email 
    $email = trim($_POST['email']);

    // Check if user exists
    $query = "SELECT email FROM users WHERE email = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    //  If user found, generate OTP
    if ($result->num_rows > 0) {
        // Generate and store OTP
        $otp = otpGenerator();
        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email; 

        // Use non-personalized client config
        require_once '../Config/client_reset.php';

        $mail = new Mail();
        if ($mail->sendMail($config, $client)) {
            header ("Location: ../Pages/mailVerify.php");
            exit();

        } else {
            echo "Failed to send OTP. Please try again later.";
        }

    } else {
        echo "No account found with that email.";
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
