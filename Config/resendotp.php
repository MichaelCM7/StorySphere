<?php
session_start();

// Remove old OTP and timestamp
unset($_SESSION['otp'], $_SESSION['otp_created']);

// Generate new OTP and timestamp
require_once '../Utils/otp.php';
$_SESSION['otp'] = otpGenerator();
$_SESSION['otp_created'] = time();


// STore user info in session
//require_once 'signup_submit.php';
$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
$email = $_SESSION['email'] ?? '';

// If not, you must prompt the user to sign up again or store these in session during signup.

if (empty($email)) {
    header("Location: ../Pages/signUp.php?error=noemail");
    exit;
}

// Prepare the email client array
require 'constants.php';
$client = [
    'Name_From' => $config['Website_Name'],
    'Email_From' => $config['Website_Email'],
    'Name_To' => "$firstname $lastname",
    'Email_To' => $email,
    'Email_Subject' => 'Your New OTP for '.$config['Website_Name'],
    'Email_Body' => "
        Hello $firstname $lastname,<br><br>
        Here is your new OTP code for verifying your account on {$config['Website_Name']}.<br>
        <h1>{$_SESSION['otp']}</h1>
        <p>This code is valid for 2 minutes.</p>
        <br><br>
        Regards,<br>
        Support Team.<br>
        {$config['Website_Name']}.
    "
];

// Send the email
require 'mail.php';
$mailer = new Mail();
if (!$mailer->sendMail($config, $client)) {
    // Optionally, redirect with an error or show a message
    header("Location: ../Pages/mailVerify.php?error=mailfail");
    exit;
}

header("Location: ../Pages/mailVerify.php");
exit;
?>