<?php
require 'class.phpmailer.php';
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Port = 465;
$mail->Host = 'smtp.gmail.com';
$mail->IsHTML(true);
$mail->Mailer = 'smtp';
$mail->SMTPSecure = 'ssl';

$mail->SMTPAuth = true;
$mail->Username = "your_gmail_user_name@gmail.com";
$mail->Password = "your_gmail_password";

//Sender Info
$mail->From = "no-reply@ictdesignhub.com";
$mail->FromName = "User Authentication";

