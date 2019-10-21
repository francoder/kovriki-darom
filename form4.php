<?php
/**
 * Created by PhpStorm.
 * User: klimenko
 * Date: 13.02.19
 * Time: 16:30
 */

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';



  $mail          = new PHPMailer(true);
  $mail->CharSet = 'UTF-8';
  //$mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Host      = 'localhost';

$mail->setFrom('zakaz@kovriki-darom.ru', 'Kovriki-Darom');

  $mail->addAddress("drlance@mail.ru");

  $mail->isHTML(true);
  $mail->Subject = 'ТЕМА';
  $mail->Body    = 'TEKCN';

  $mail->send();




?>


