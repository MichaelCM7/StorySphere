<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/otp.php';

function storeOtp($otp)
{
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_created'] = time();
}
function otpVerify($user_otp)
{
    if(!isset($_SESSION['otp']) || !isset($_SESSION['otp_created']))
    {
      return "Otp not found.Please request a new one";
    }  
    if(time() > $_SESSION['otp_created'] + 120)
      {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_created']);
        return "OTP expired";
      }
    if($user_otp === $_SESSION['otp'])
      {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_created']);
        return true; // OTP is valid
      }
    }
    return "Invalid OTP"; // OTP is invalid or not set

?>