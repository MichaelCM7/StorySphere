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

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

  // echo "<pre>";
  // print_r($_POST);
  // echo "</pre>";

  // Collect form data
  $_SESSION['firstname'] = $_POST["firstname"];
  $_SESSION['lastname'] = $_POST["lastname"];
  $_SESSION['userrole'] = $_POST["user-role"];
  $_SESSION['phonenumber'] = $_POST["phonenumber"];
  $_SESSION['email'] = $_POST["email"];
  $password = $_POST["password"] ?? '';
  $cpassword = $_POST["Cpassword"] ?? '';

  // Validate passwords match
if ($_SESSION['password'] !== $_SESSION['cpassword']) {
    echo "Error: Passwords do not match.";
    exit();
}


  if (!filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
    echo "Error: The email address is not valid.";
    exit(); 
  }

  $password = $_POST["password"];
  $hashed_password = password_hash($_SESSION['password'], PASSWORD_BCRYPT);

  $role_id = 3; // Initialize default role as 'Reader' (ID 3, based on your schema)

  if ($_SESSION['userrole'] === 'admin') {
    $role_id = 1;
  } elseif ($_SESSION['userrole'] === 'librarian') {
    $role_id = 2;
  } elseif ($_SESSION['userrole'] === 'reader') {
    $role_id = 3;
  } else {
    error_log("Invalid user role selected: " . $_SESSION['userrole']);
  }

  $check_stmt = $connection->prepare("SELECT user_id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $_SESSION['email']);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    echo "<script>alert('Error: An account with this email already exists.'); window.history.back();</script>";
    $check_stmt->close();
    exit();
}
$check_stmt->close();

  //Insert data into database
  $stmt = $connection->prepare("INSERT INTO users (first_name, last_name, phone_number, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssi", $_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['phonenumber'], $_SESSION['email'], $hashed_password, $role_id);
  if ($stmt->execute()) {
      //echo "Data inserted successfully!";
  } else {
     echo "<script>alert('Error inserting data: " . addslashes($stmt->error) . "'); window.history.back();</script>";
  }
  $stmt->close();

  require 'client.php';
  require 'mail.php';

  
  // Generate OTP
  // $otp=otpGenerator();
  
  // Send the email
  $Mail = new Mail();
  $result = $Mail->sendMail($config, $client);
  // $result = $Mail->sendMail($config, $client,$otp);

  if($result){
    //echo "Signup successful. Please check your email for verification.";
    header("Location: ../Pages/mailVerify.php");
    exit;
  } else {
    //echo "Sign Up Failed";
    header("Location: ../Pages/error.php");
    exit();
  }

  // Debug
  // echo "OTP set in SESSION: " . $_SESSION['otp'];
?>
</body>
</html>
