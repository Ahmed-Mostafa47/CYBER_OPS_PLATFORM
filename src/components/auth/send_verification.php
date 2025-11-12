<?php
require 'db_connect.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';

if (!$username || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing username or email']);
    exit;
}

// Generate 6-digit numeric code
$code = str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);

// Delete previous verifications
$stmt = $conn->prepare('DELETE FROM email_verifications WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->close();

// Insert new verification
$stmt = $conn->prepare('INSERT INTO email_verifications (email, username, verification_code, is_verified, expires_at) VALUES (?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
$stmt->bind_param('sss', $email, $username, $code);
$stmt->execute();
$stmt->close();

// Send email via PHPMailer
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'deboabdo1234@gmail.com'; // Gmail بتاعك
    $mail->Password = 'gjlwqkofrqlyozop';      // App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('deboabdo1234@gmail.com', 'CTF Platform');
    $mail->addAddress($email, $username);
    $mail->isHTML(false);
    $mail->Subject = 'CTF Platform - Verification Code';
    $mail->Body = "Hello $username,\n\nYour verification code is: $code\nIt expires in 10 minutes.";

    $mail->send();
    echo json_encode(['success'=>true, 'message'=>'Verification code sent successfully']);
} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>'Mailer error: '.$mail->ErrorInfo]);
}
?>
