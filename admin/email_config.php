<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ONLY autoload — remove manual PHPMailer/src requires
require __DIR__ . '/../vendor/autoload.php';

function sendMailToFaculty($email, $subject, $message){
    $mail = new PHPMailer(true);

    try {
        // Server Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // Replace with your Gmail  
        $mail->Username   = 'yourgmail@gmail.com';  
        
        // Gmail app password (NOT your real password)
        $mail->Password   = 'hevufacuvqjgqnru';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom('yourgmail@gmail.com', 'Career System Admin');

        // Receiver
        $mail->addAddress($email);

        // Email Content
        $mail->isHTML(false); 
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo; // SHOW ERROR
        return false;
    }
}
?>
