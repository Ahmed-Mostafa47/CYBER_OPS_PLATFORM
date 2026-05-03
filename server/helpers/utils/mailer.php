<?php
declare(strict_types=1);

// Check if vendor autoload exists before requiring
$vendorAutoload = __DIR__ . '/../auth/vendor/autoload.php';
$mailerAvailable = false;

if (file_exists($vendorAutoload)) {
    try {
        require_once $vendorAutoload;
        $mailerAvailable = class_exists('PHPMailer\PHPMailer\PHPMailer');
    } catch (Throwable $e) {
        error_log('Failed to load PHPMailer: ' . $e->getMessage());
        $mailerAvailable = false;
    }
} else {
    error_log('PHPMailer vendor autoload not found at: ' . $vendorAutoload);
    $mailerAvailable = false;
}

const CYBEROPS_MAIL_USERNAME = 'deboabdo1234@gmail.com';
const CYBEROPS_MAIL_PASSWORD = 'gjlwqkofrqlyozop';
const CYBEROPS_MAIL_FROM_NAME = 'CYBER_OPS Platform';
const CYBEROPS_ADMIN_EMAIL = 'deboabdo1234@gmail.com';

function cyberops_mailer()
{
    global $mailerAvailable;
    if (!$mailerAvailable) {
        return null;
    }
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = CYBEROPS_MAIL_USERNAME;
    $mail->Password = CYBEROPS_MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom(CYBEROPS_MAIL_USERNAME, CYBEROPS_MAIL_FROM_NAME);
    $mail->isHTML(true);
    return $mail;
}

function cyberops_send_admin_notification(string $subject, string $body): bool
{
    global $mailerAvailable;
    if (!$mailerAvailable) {
        error_log('Mailer not available - PHPMailer not loaded');
        return false;
    }
    
    try {
        $mail = cyberops_mailer();
        if (!$mail) {
            return false;
        }
        $mail->clearAddresses();
        $mail->addAddress(CYBEROPS_ADMIN_EMAIL);
        $mail->Subject = $subject;
        $mail->Body = nl2br($body);
        $mail->AltBody = strip_tags($body);
        return $mail->send();
    } catch (\Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}

