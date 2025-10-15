<?php
require_once __DIR__ . '/otp.php';
// Start the session if it hasn't been started yet
function storeOtp($otp)
{
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time()+120;
}
function otpVerify($user_otp)
{
    if(isset($_SESSION['otp']) || !isset($_SESSION['otp_time']))
    {
      return "Otp not found.Please request a new one";
    }  
    if(time() > $_SESSION['otp_time'])
      {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_time']);
        return "OTP expired";
      }
    if($user_otp === $_SESSION['otp'])
      {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_time']);
        return true; // OTP is valid
      }
    }
    return "Invalid OTP"; // OTP is invalid or not set

?>