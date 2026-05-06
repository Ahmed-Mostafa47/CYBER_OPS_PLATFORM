<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
$email = $argv[1] ?? '';
if (!$email) die("No email provided\n");

echo "--- Checking Users Table ---\n";
$stmt = $conn->prepare("SELECT user_id, username, email, is_active FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$stmt->close();

echo "\n--- Checking Email Verifications Table ---\n";
$stmt = $conn->prepare("SELECT id, email, username, is_verified, expires_at FROM email_verifications WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
    print_r($row);
}
$stmt->close();

$conn->close();
