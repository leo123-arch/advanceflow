<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    $mail->Host = 'smtp.gmail.com';
    $mail->Username = 'yourgmail@gmail.com';
    $mail->Password = 'YOUR_APP_PASSWORD'; 
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';

    $mail->setFrom('yourgmail@gmail.com');
    $mail->addAddress('yourgmail@gmail.com');

    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'Testing SMTP connection';

    $mail->send();
    echo "Success!";
} catch (Exception $e) {
    echo "ERROR: {$mail->ErrorInfo}";
}
?>
