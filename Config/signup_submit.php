<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/IAP/styles/tables.css">
  <title>Sign Up Submit</title>
</head>
<body>
  <?php
  require 'dbconnection.php';
  require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';

  echo "<pre>";
  print_r($_POST);
  echo "</pre>";

  $username = $_POST["username"];
  $email = $_POST["email"];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: The email address is not valid.";
    exit(); 
  }
  $password = $_POST["password"];
  $hashed_password = password_hash($password, PASSWORD_BCRYPT);

  //Insert data into database
  $sql = "INSERT INTO USERS () VALUES (,col2); ";
  mysqli_query($connection,$sql)

  require 'client.php';
  require 'mail.php';

  // Send the email
  $Mail = new Mail();
  $result = $Mail->sendMail($config, $client);

  if($result){
    echo "Signup successful. Please check your email for verification.";
    return true;
  } else {
    echo "Sign Up Failed";
    return false;
  }

  
?>
</body>
</html>
