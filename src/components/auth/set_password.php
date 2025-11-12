<?php
require 'db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$fullName = isset($input['fullName']) ? trim($input['fullName']) : '';
$username = isset($input['username']) ? trim($input['username']) : '';

if (!$email || !$password || !$username) {
    echo json_encode(['success'=>false,'message'=>'Missing fields']);
    exit;
}

// check verification
$stmt = $conn->prepare('SELECT id, is_verified, username FROM email_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || !$row['is_verified']) {
    echo json_encode(['success'=>false,'message'=>'Email not verified']);
    exit;
}

// check user/email not exists
$chk = $conn->prepare('SELECT user_id FROM users WHERE email = ? OR username = ? LIMIT 1');
$chk->bind_param('ss', $email, $username);
$chk->execute();
if ($chk->get_result()->fetch_assoc()) {
    echo json_encode(['success'=>false,'message'=>'User with this email or username already exists']);
    exit;
}
$chk->close();

// hash password and insert
$hash = password_hash($password, PASSWORD_BCRYPT);
$profile_meta = json_encode(['avatar'=>'🆕','rank'=>'RECRUIT','specialization'=>'TRAINING','join_date'=>date('c')]);

$ins = $conn->prepare('INSERT INTO users (username, email, password_hash, full_name, profile_meta) VALUES (?, ?, ?, ?, ?)');
$ins->bind_param('sssss', $username, $email, $hash, $fullName, $profile_meta);
if (!$ins->execute()) {
    echo json_encode(['success'=>false,'message'=>'Insert failed: ' . $ins->error]);
    exit;
}
$userid = $ins->insert_id;
$ins->close();

// optional: delete verification entries for this email
$del = $conn->prepare('DELETE FROM email_verifications WHERE email = ?');
$del->bind_param('s', $email);
$del->execute();
$del->close();

echo json_encode(['success'=>true,'message'=>'Account created','user_id'=>$userid]);
?>