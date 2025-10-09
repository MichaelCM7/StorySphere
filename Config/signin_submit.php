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
  require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';

  echo "<pre>";
  print_r($_POST);
  echo "</pre>";


  $email = $_POST["email"];
  $password = $_POST["password"];
   
  $prepStatemnt = $connection->prepare("SELECT password FROM table_name WHERE email = ?;");
  $prepStatement->bind_param("s", $email); 
  $prepStatement->execute();
  $result = $prepStatement->get_result();

  
?>
</body>
</html>
