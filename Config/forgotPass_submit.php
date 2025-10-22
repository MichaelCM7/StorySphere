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
        $_SESSION['otp_created'] = time();
        $_SESSION['reset_email'] = $email; 

        // Use non-personalized client config
        require_once '../Config/client_reset.php';

        $mail = new Mail();
        if ($mail->sendMail($config, $client)) {
            header ("Location: ../Pages/forgotOtp.php");
            exit();

        } else {
           echo "<script>alert(' Fail to send OTP .Please try again later '); window.history.back();</script>";
        exit();
        }

    } else {
        echo "<script>alert(' No account found with that email.'); window.history.back();</script>";
        exit();
    
    }

    $stmt->close();
    $connection->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
