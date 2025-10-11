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
  require_once __DIR__ . '/../Utils/verifyOtp.php';
  require 'dbconnection.php';
  require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';
  require_once 'otpcall.php';

  // echo "<pre>";
  // print_r($_POST);
  // echo "</pre>";

 // Collect form data
$firstname   = $_POST["firstname"];
$lastname    = $_POST["lastname"];
$phonenumber = $_POST["phonenumber"];
$email       = $_POST["email"];
$password    = $_POST["password"];
$cpassword   = $_POST["Cpassword"];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: The email address is not valid.";
    exit(); 
  }
  $password = $_POST["password"];
  $hashed_password = password_hash($password, PASSWORD_BCRYPT);

  //Insert data into database
  // Prepare the SQL statement
$stmt = $connection->prepare("
    INSERT INTO USERS (firstname, lastname, phonenumber, email, password)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $firstname, $lastname, $phonenumber, $email, $hashed_password);
if ($stmt->execute()) {
    echo "Data inserted successfully!";
} else {
    echo "Error inserting data: " . $stmt->error;
}
$stmt->close();
  
  // Generate OTP
  $otp=otpGenerator();
  
  // Send the email
  $Mail = new Mail();
  $result = $Mail->sendMail($config, $client,$otp);

  if($result){
    echo "Signup successful. Please check your email for verification.";
    header("location: ../Pages/mailVerify.php");
    exit;
    return true;
  } else {
    echo "Sign Up Failed";
    return false;
  }

  // Debug
  // echo "OTP set in session: " . $_SESSION['otp'];
?>
</body>
</html>
