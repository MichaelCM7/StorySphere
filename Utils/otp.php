<?php
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
?>
