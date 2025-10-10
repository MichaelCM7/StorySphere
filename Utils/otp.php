<?php

session_start();
  function otpGenerator(){
    $randomNum1 = rand(1,9);
    $randomNum2 = rand(1,9);
    $randomNum3 = rand(1,9);
    $randomNum4 = rand(1,9);
    $randomNum5 = rand(1,9);
    $randomNum6 = rand(1,9);
    $randomNumber = $randomNum1.$randomNum2.$randomNum3.$randomNum4.$randomNum5.$randomNum6;
    return $randomNumber;
  };
function storeOtp($otp)
{
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time()+120;
}
function otpVerify($user_otp)
{
    if(isset($_SESSION['otp']) || isset($_SESSION['otp_time']))
    {
      return "otp not found.Please request anew one";
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
