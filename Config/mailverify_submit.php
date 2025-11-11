<?php
session_start();

$enteredCode = $_POST['otp'];
$timeout = 180; // 3 minutes

if (isset($_SESSION['otp'], $_SESSION['otp_created']) &&
    (time() - $_SESSION['otp_created'] <= $timeout)) {
      if (trim((string)$enteredCode) === trim((string)$_SESSION['otp'])) {
          echo "Your email has been verified";
        //   Redirect to correct user
            if (isset($_SESSION['userrole'])) {
                if (isset($_SESSION['userrole'])) {
                if ($_SESSION['userrole'] === 'admin') {
                    header("Location: ../Pages/admin_dashboard.php");
                    exit();
                } elseif ($_SESSION['userrole'] === 'librarian') {
                    header("Location: ../Pages/librarian_dashboard.php");
                    exit();
                } elseif ($_SESSION['userrole'] === 'reader') {
                    header("Location: ../Pages/user_index_dashboard.php");
                    exit();
                } else {
                    error_log("Invalid user role selected: " . $_SESSION['userrole']);
                    header("Location: ../Pages/signIn.php");
                    exit();
                }
            } else {
                // fallback if role not set
                header("Location: ../Pages/signIn.php");
                exit();
            }
          unset($_SESSION['otp'], $_SESSION['otp_created']);
      } else {
          echo "Wrong entry";
          header("Location: ../Pages/mailVerify.php");
          exit();
      }}
} else {
    echo "OTP expired. Please request a new one.";
    unset($_SESSION['otp'], $_SESSION['otp_created']);
    header("Location: ../Pages/mailVerify.php");
    exit();
}
?>