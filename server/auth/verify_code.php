<?php
// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 3600");
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../utils/db_connect.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');


$data = file_get_contents('php://input');
$input = json_decode($data, true);
$email = isset($input['email']) ? trim($input['email']) : '';
$code = isset($input['code']) ? trim($input['code']) : '';

if (!$email || !$code) {
    echo json_encode(['success' => false, 'message' => 'Missing email or code']);
    exit;
}

$stmt = $conn->prepare('SELECT id FROM email_verifications WHERE email = ? AND verification_code = ? AND expires_at > NOW() AND is_verified = 0 LIMIT 1');
$stmt->bind_param('ss', $email, $code);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Invalid, expired, or already used verification code']);
    exit;
}

$id = $row['id'];
$stmt->close();

$update_stmt = $conn->prepare('UPDATE email_verifications SET is_verified = 1 WHERE id = ?');
$update_stmt->bind_param('i', $id);

if ($update_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Email successfully verified']);
} else {

    echo json_encode(['success' => false, 'message' => 'Verification update failed: ' . $update_stmt->error]);
}

$update_stmt->close();
$conn->close();
?>
