<?php

function sendEmail($toEmail, $emailFrom, $emailMessage, $subject) {
    require 'PHPMailerAutoload.php';

    $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output
    $headerValues = explode("$$", $emailFrom);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $headerValues[0];  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $headerValues[1];                 // SMTP username
    $mail->Password = $headerValues[2];                           // SMTP password
    $mail->Port = $headerValues[3];                                    // TCP port to connect to 587
    $mail->SMTPSecure = $headerValues[4];                            // Enable TLS encryption, `ssl` also accepted

    $mail->setFrom($headerValues[1], $headerValues[5]);
    $mail->addAddress($toEmail);
//$mail->addAddress($toEmail, 'Joe User');     // Add a recipient
//$mail->addAddress('j.patel@we-are-mea.com');               // Name is optional
//$mail->addReplyTo('info@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML
    if (empty($subject) || $subject == "") {
        $subject = $headerValues[6];
    }
    $mail->Subject = $subject;
    //$mail->Subject = 'Coupon code';
    $mail->Body = $emailMessage;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if (!$mail->send()) {
        //echo 'Email could not be sent.';
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
        return FALSE;
    } else {
        //echo 'Message has been sent';
        return TRUE;
    }
}
?>