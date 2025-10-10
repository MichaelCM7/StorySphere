<?php
// In dbconnect.php
require __DIR__ . '/constants.php';

$connection = new mysqli(
    $config['DB_Host'], 
    $config['DB_User'], 
    $config['DB_Password'], 
    $config['DB_Name'],
    $config['DB_Port']
);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
// echo "Connected successfully";
?>