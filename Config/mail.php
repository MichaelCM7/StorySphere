<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require_once __DIR__ . '/../ExternalLibraries/PHPMailer/vendor/autoload.php';
//require 'client.php';

class Mail{
  public function sendMail($config,$mailClient) {
  $mail = new PHPMailer(true);

  try {
      //Server settings
      $mail->isSMTP();
      $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      $mail->Host       = $config['SMTP_Host'];
      $mail->SMTPAuth   = true;
      $mail->Username   = $config['SMTP_User'];
      $mail->Password   = $config['SMTP_Password'];
      $mail->SMTPSecure = $config['SMTP_Security'];
      $mail->Port       = 465;

      //Recipients
      $mail->setFrom($mailClient['Email_From'], $mailClient['Name_From']);
      $mail->addAddress($mailClient['Email_To'], $mailClient['Name_To']);
      // $mail->addAddress('ellen@example.com');
      // $mail->addReplyTo('info@example.com', 'Information');
      // $mail->addCC('cc@example.com');
      // $mail->addBCC('bcc@example.com');

      //Attachments
      // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
      // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

      //Content
      $mail->isHTML(true);
      $mail->Subject = $mailClient['Email_Subject'];
      $mail->Body    = $mailClient['Email_Body'];
      // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

      $mail->send();
      echo 'Message has been sent';
      return true;
  } catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      return false;
  }
  }
}

