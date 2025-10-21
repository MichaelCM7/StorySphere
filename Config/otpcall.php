<?php
  //session_start(); // Start session
  //require_once '../Utils/otp.php';
  if (session_status() === PHP_SESSION_NONE) session_start();

  if (!isset($_SESSION['otp'])) {
      $_SESSION['otp'] = otpGenerator();
      $_SESSION['otp_created'] = time(); // Store creation time
  }
?>