<?php

require 'class.phpmailer.php';

//WEBSITE
//$mail = new PHPMailer();
////$mail->SMTPDebug = 3;
//$mail->IsSMTP();
//$mail->Mailer = 'smtp';
//$mail->Host = "localhost";
//$mail->IsHTML(true);
//
////Sender Info
//$mail->From = "info@myfamilyrecipes.online";
//$mail->FromName = "User Authentication";




/////LOCALHOST
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Mailer = 'smtp';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->Host = "smtp.gmail.com";
$mail->IsHTML(true);


$mail->SMTPAuth = true;
$mail->Username = "info.family.recipes@gmail.com";
$mail->Password = "Zxcv@0987";

//Sender Info
$mail->From = "no-reply@ictdesignhub.com";
$mail->FromName = "User Authentication";
