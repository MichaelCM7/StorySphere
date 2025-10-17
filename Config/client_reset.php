<?php
require 'constants.php';

$client = [
    'Name_From' => $config['Website_Name'],
    'Email_From' => $config['Website_Email'],
    'Name_To' => '', // Not personalized
    'Email_To' => $email,
    'Email_Subject' => 'Password Reset OTP for ' . $config['Website_Name'],
    'Email_Body' => "
        Hello,<br><br>
        You requested to reset your password on {$config['Website_Name']}.<br>
        Enter the following code to complete the password reset process.<br><br>
        <h1>" . $_SESSION['otp'] . "</h1>
        <p>This code is valid for 2 minutes.</p>
        <br><br>
        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
];
?>
