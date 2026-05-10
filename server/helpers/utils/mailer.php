<?php
declare(strict_types=1);

// Check if vendor autoload exists before requiring
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
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

require_once __DIR__ . '/../../core/utils/load_env.php';

// Mail settings from .env
if (!defined('CYBEROPS_MAIL_USERNAME'))  define('CYBEROPS_MAIL_USERNAME',  $_ENV['MAIL_USER']      ?? '');
if (!defined('CYBEROPS_MAIL_PASSWORD'))  define('CYBEROPS_MAIL_PASSWORD',  $_ENV['MAIL_PASS']      ?? '');
if (!defined('CYBEROPS_MAIL_FROM_NAME')) define('CYBEROPS_MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'CYBER_OPS Platform');
if (!defined('CYBEROPS_ADMIN_EMAIL'))    define('CYBEROPS_ADMIN_EMAIL',    $_ENV['ADMIN_EMAIL']    ?? CYBEROPS_MAIL_USERNAME);

function cyberops_mailer()
{
    global $mailerAvailable;
    if (!$mailerAvailable) {
        return null;
    }
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = CYBEROPS_MAIL_USERNAME;
    $mail->Password = CYBEROPS_MAIL_PASSWORD;
    $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
    $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
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

