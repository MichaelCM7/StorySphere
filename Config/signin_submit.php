<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/IAP/styles/tables.css">
<title>Sign In Submit</title>
</head>
<body>
<?php
  // Ensure this file exists and handles your database connection setup.
  require 'dbconnection.php';

  // The following lines are removed as they are not needed for a basic login check:
  // require_once __DIR__ . '/../Utils/otp.php';
  // require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';
  // require 'client.php';
  // require 'mail.php';


  // Get and sanitize input
  $email = $_POST["email"];
  $password = $_POST["password"];

  // Prepare the statement to fetch the hashed password for the given email
  $prepStatement = $connection->prepare("SELECT password FROM table_name WHERE email = ?;");
  $prepStatement->bind_param("s", $email); 
  $prepStatement->execute();
  $result = $prepStatement->get_result();

  // Check if exactly one user was found
  if ($result->num_rows === 1) {
  $row = $result->fetch_assoc();
  $stored_hash = $row["password"];

    // Verify the submitted password against the stored hash
    if (password_verify($password, $stored_hash)) {
      echo "Login Successful";
      // *** ACTION AFTER SUCCESS ***
      // You can add session start and redirection here if needed:
      // session_start();
      // $_SESSION['user_email'] = $email;
      // header("location: ../Pages/dashboard.php");
      // exit;
    } else {
      echo "Login Failed. Incorrect Password.";
    } 
  } else {
    echo "Login Failed. Email not found!";
  }

  $prepStatement->close();
  $connection->close();
?>
</body>
</html>
