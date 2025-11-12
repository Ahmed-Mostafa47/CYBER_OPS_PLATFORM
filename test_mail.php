<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'deboabdo1234@gmail.com'; 
    $mail->Password = 'gjlwqkofrqlyozop'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('deboabdo1234@gmail.com', 'Test Mail');
    $mail->addAddress('ahmedmo1286631@gmail.com', 'Abdo');

    $mail->isHTML(true);
    $mail->Subject = 'Test PHPMailer';
    $mail->Body = 'This is a test email sent using PHPMailer and Gmail SMTP.';

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>
