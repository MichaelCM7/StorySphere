<?php
  require 'constants.php';


  $client = [
    'Name_From' => $config['Website_Name'],
    'Email_From' => $config['Website_Email'],
    'Name_To' => "$username",
    'Email_To' => $email,
    'Email_Subject' => 'Welcome To '.$config['Website_Name'].' ! Account Verification',
    'Email_Body' => "
        Hello $username,<br><br>
        You requested to create an account on {$config['Website_Name']}.<br>
        If you did not register for this you can ignore this message.<br>
        In order to use this account you need to <a href='{$config['Website_URL']}index.php'>Click Here</a> to complete the registration process.<br><br>
        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
  ];
?>