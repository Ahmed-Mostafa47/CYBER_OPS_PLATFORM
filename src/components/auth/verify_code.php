<?php
// debug mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// DB connection
require 'db_connect.php';
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Read raw input
$rawInput = file_get_contents('php://input');
if (!$rawInput) {
    echo json_encode(['success' => false, 'message' => 'No input received']);
    exit;
}

// Decode JSON
$input = json_decode($rawInput, true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . $rawInput]);
    exit;
}

// Extract email & code
$email = isset($input['email']) ? trim($input['email']) : '';
$code = isset($input['code']) ? trim($input['code']) : '';

// Validate input
if (!$email || !$code) {
    echo json_encode(['success' => false, 'message' => 'Missing email or code']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code format']);
    exit;
}

// Fetch latest verification record
$stmt = $conn->prepare('
    SELECT id, verification_code, is_verified, expires_at 
    FROM email_verifications 
    WHERE email = ? 
    ORDER BY created_at DESC 
    LIMIT 1
');

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('s', $email);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    exit;
}

$res = $stmt->get_result();
if (!$res) {
    echo json_encode(['success' => false, 'message' => 'Get result failed: ' . $stmt->error]);
    exit;
}

$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No verification request found for ' . $email]);
    exit;
}

if ($row['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'This email is already verified']);
    exit;
}

if (strtotime($row['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Verification code has expired']);
    exit;
}

if ($row['verification_code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Incorrect verification code']);
    exit;
}

// Mark email as verified
$upd = $conn->prepare('UPDATE email_verifications SET is_verified = 1 WHERE id = ?');
if (!$upd) {
    echo json_encode(['success' => false, 'message' => 'Prepare update failed: ' . $conn->error]);
    exit;
}

$upd->bind_param('i', $row['id']);
if (!$upd->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execute update failed: ' . $upd->error]);
    exit;
}

$upd->close();

echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
?>
