<?php
  require "../Secure/secureInfo.php";
  
  // Website Timezone
  $config['Timezone'] = 'AFRICA/NAIROBI';

  // Website Information
  $config['Website_Name'] = 'StorySphere'; 
  $config['Website_URL'] = 'http://localhost/StorySphere/';
  $config['Website_Email'] = 'info@storysphere.com';

  // Website language
  $config['Website_Language'] = 'en';

  // Database Constants
  $config['DB_Type'] = 'mysqli';
  $config['DB_Host'] = 'localhost';
  $config['DB_User'] = $dbUser;
  $config['DB_Password'] = $dbPassword;
  $config['DB_Name'] = $dbName;
  $config['DB_Port'] = $dbPort;

  //  Email Protocol Configiguration
  $config['Mail_Type'] = 'smtp';
  $config['SMTP_Host'] = 'smtp.gmail.com';
  $config['SMTP_User'] =  $personalEmail;
  $config['SMTP_Password'] =  $appPassword;
  $config['SMTP_Port'] = 465; 
  $config['SMTP_Security'] = 'ssl';
?>