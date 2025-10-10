<?php
  require 'constants.php';
  require '../Utils/otp.php';

  $otp = otpGenerator();

  $client = [
    'Name_From' => $config['Website_Name'],
    'Email_From' => $config['Website_Email'],
    'Name_To' => "$firstname $lastname",
    'Email_To' => $email,
    'Email_Subject' => 'Welcome To '.$config['Website_Name'].' ! Account Verification',
    'Email_Body' => "
        Hello $firstname $lastname,<br><br>
        You requested to create an account on {$config['Website_Name']}.<br>
        If you did not register for this website you can ignore this message.<br>
        Enter the following code to complete the registration process.<br><br>
        <h1>".$otp."</h1>
        <p>This code is valid for 2 minutes.</p>
        <br><br>

        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
  ];
?>