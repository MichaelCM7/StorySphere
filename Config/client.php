<?php
  require 'constants.php';
  //include '../Config/signup_submit.php';
  //require_once 'otpcall.php';
  session_start();

  $client = [
    'Name_From' => $config['Website_Name'],
    'Email_From' => $config['Website_Email'],
    'Name_To' => "{$_SESSION['firstname']} {$_SESSION['lastname']}",
    'Email_To' => $_SESSION['email'],
    'Email_Subject' => 'Welcome To '.$config['Website_Name'].' ! Account Verification',
    'Email_Body' => "
        Hello ".$_SESSION['firstname']." ".$_SESSION['lastname'].",<br><br>
        You requested to create an account on {$config['Website_Name']}.<br>
        If you did not register for this website you can ignore this message.<br>
        Enter the following code to complete the registration process.<br><br>
        <h1>".$_SESSION['otp']."</h1>
  
        <p>This code is valid for 2 minutes.</p>
        <br><br>

        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
  ];
?>