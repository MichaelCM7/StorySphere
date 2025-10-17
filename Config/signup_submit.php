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
  $userrole    = $_POST["user-role"];
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

  $role_id = 3; // Initialize default role as 'Reader' (ID 3, based on your schema)

  if ($userrole === 'admin') {
    $role_id = 1;
  } elseif ($userrole === 'librarian') {
    $role_id = 2;
  } elseif ($userrole === 'reader') {
    $role_id = 3;
  } else {
    error_log("Invalid user role selected: " . $userrole);
  }

  //Insert data into database
  // $stmt = $connection->prepare("INSERT INTO users (first_name, last_name, phone_number, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?, ?)");
  // $stmt->bind_param("sssssi", $firstname, $lastname, $phonenumber, $email, $hashed_password,$role_id);
  // if ($stmt->execute()) {
  //     echo "Data inserted successfully!";
  // } else {
  //     echo "Error inserting data: " . $stmt->error;
  // }
  // $stmt->close();

  require 'client.php';
  require 'mail.php';

  
  // Generate OTP
  // $otp=otpGenerator();
  
  // Send the email
  $Mail = new Mail();
  $result = $Mail->sendMail($config, $client);
  // $result = $Mail->sendMail($config, $client,$otp);

  if($result){
    echo "Signup successful. Please check your email for verification.";
     header("Location: ../Pages/mailVerify.php");
    exit;
  } else {
    echo "Sign Up Failed";
    header("Location: ../Pages/error.php");
    exit();
  }

  // Debug
  // echo "OTP set in session: " . $_SESSION['otp'];
?>
</body>
</html>
