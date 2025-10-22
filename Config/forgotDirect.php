<?php
session_start();

$enteredCode = $_POST['otp'];
$timeout = 180; // 3 minutes

if (isset($_SESSION['otp'], $_SESSION['otp_created']) &&
    (time() - $_SESSION['otp_created'] <= $timeout)) {

      if (trim((string)$enteredCode) === trim((string)$_SESSION['otp'])) {
          unset($_SESSION['otp'], $_SESSION['otp_created']);
          $_SESSION['otp_verified'] = true;
           //echo "<pre>";
          //print_r($_SESSION);
         // echo "</pre>";
        //exit();
          header("Location: ../Pages/newPass.php");

          exit();
         
      } else {
         echo "<script>alert('Invalid OTP. Please try again.'); window.history.back();</script>";
        exit();  
      }
}
    else {
    
    unset($_SESSION['otp'], $_SESSION['otp_created'] );
    echo "<script>alert('OTP expired or invalid. Please request a new one.');window.location.href='../Pages/forgotPass.php';</script>";
    exit();
    
}   
?>