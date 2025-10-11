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
  require_once __DIR__ . '/../Utils/otp.php';
  require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';
  require 'dbconnection.php';
  require 'client.php';
  require 'mail.php';


  echo "<pre>";
  print_r($_POST);
  echo "</pre>";


  $email = $_POST["email"];
  $password = $_POST["password"];
   
  $prepStatemnt = $connection->prepare("SELECT password FROM table_name WHERE email = ?;");
  $prepStatement->bind_param("s", $email); 
  $prepStatement->execute();
  $result = $prepStatement->get_result();

  if ($result->num_rows === 0) {
      echo "Error: No user found with this email.";
      exit(); 
  }
  $row = $result->fetch_assoc();
  $hashed_password = $row['password'];
  if (!password_verify($password, $hashed_password)) {
      echo "Error: Incorrect password.";
      exit(); 
  }
  // Generate OTP
  $otp=otpGenerator();
  

  // Send the email
  $Mail = new Mail();
  $result = $Mail->sendMail($config, $client,$otp);

  if($result){
    echo "Sign In successful. Please check your email for verification.";
    header("location: ../Pages/mailVerify.php");
    exit;
    return true;
  } else {
    echo "Sign In Failed";
    return false;
  }


?>
</body>
</html>
