<?php
  require 'constants.php';
  //include '../Config/signup_submit.php';
  //require_once 'otpcall.php';

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
        <h1>".$_SESSION['otp']."</h1>
        <br><br>

        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
  ];
?>