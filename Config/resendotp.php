<?php
session_start();

// Ensure we have the user's email
$email = $_SESSION['email'] ?? '';
$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';

if (empty($email)) {
    header("Location: ../Pages/signUp.php?error=noemail");
    exit;
}

// Replace only OTP and timestamp
require_once __DIR__ . '/../Utils/otp.php';
unset($_SESSION['otp'], $_SESSION['otp_created']);
session_regenerate_id(true); // optional but recommended for security
$_SESSION['otp'] = otpGenerator();
$_SESSION['otp_created'] = time();

// prepare client (use session values)
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

// send mail
require 'mail.php';
// $mailer = new Mail();$mailer->sendMail($config, $client);
// Redirect back to verification page

if (!$mailer->sendMail($config, $client)) {
    // Optionally, redirect with an error or show a message
    header("Location: ../Pages/mailVerify.php?error=mailfail");
    exit;
}

header("Location: ../Pages/mailVerify.php?sent=1");
exit;
?>