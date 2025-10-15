<?php
session_start();

$enteredCode = $_POST['otp'];
$timeout = 180; // 3 minutes

if (isset($_SESSION['otp'], $_SESSION['otp_created']) &&
    (time() - $_SESSION['otp_created'] <= $timeout)) {
      if (trim((string)$enteredCode) === trim((string)$_SESSION['otp'])) {
          echo "Your email has been verified";
          header("Location: ../Pages/admin_dashboard.php");
          unset($_SESSION['otp'], $_SESSION['otp_created']);
      } else {
          echo "Wrong entry";
      }
} else {
    echo "OTP expired. Please request a new one.";
    unset($_SESSION['otp'], $_SESSION['otp_created']);
}
?>