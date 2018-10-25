<?php
    require 'PHPMailer.php';
    require 'SMTP.php';
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(); // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->Host = "smtpout.secureserver.net";
    $mail->Port = 465; // or 587
    $mail->IsHTML(true);
    $mail->Username = "soporte@storylabscr.com";
    $mail->Password = "Story2018+";
    $mail->SetFrom("soporte@storylabscr.com");
    $mail->Subject = "Test";
    $mail->Body = "hello";
    $mail->AddAddress("j-rojas-18@hotmail.com");

    if(!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Message has been sent";
    }
 ?>