<?php
// TEMP TEST SCRIPT - DELETE AFTER DEBUGGING
require_once __DIR__ . '/core/utils/load_env.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

echo "<pre>";
echo "MAIL_HOST:      " . ($_ENV['MAIL_HOST'] ?? 'NOT SET') . "\n";
echo "MAIL_PORT:      " . ($_ENV['MAIL_PORT'] ?? 'NOT SET') . "\n";
echo "MAIL_USER:      " . ($_ENV['MAIL_USER'] ?? 'NOT SET') . "\n";
echo "MAIL_PASS:      " . (empty($_ENV['MAIL_PASS']) ? 'NOT SET' : str_repeat('*', strlen($_ENV['MAIL_PASS']))) . "\n";
echo "MAIL_FROM_NAME: " . ($_ENV['MAIL_FROM_NAME'] ?? 'NOT SET') . "\n";
echo "\n";

try {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // Full SMTP debug output
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USER'] ?? '';
    $mail->Password   = $_ENV['MAIL_PASS'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);

    $mail->setFrom($_ENV['MAIL_USER'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'Test');
    $mail->addAddress($_ENV['MAIL_USER'] ?? ''); // Send to self as test
    $mail->Subject = 'HACK_ME Test Email';
    $mail->Body    = 'This is a test email from HACK_ME platform.';

    $mail->send();
    echo "\n✅ Email sent successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Mailer Error: " . $e->getMessage() . "\n";
    echo "ErrorInfo: " . $mail->ErrorInfo . "\n";
}
echo "</pre>";
?>
