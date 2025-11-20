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

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Simple connectivity check: GET ?ping=1 → {"success":true,"message":"pong"}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ping'])) {
	echo json_encode(['success' => true, 'message' => 'pong']);
	exit;
}

require_once __DIR__ . '/../utils/db_connect.php';

// Read JSON body if provided
$data = file_get_contents('php://input');
$input = null;
if ($data !== false && strlen(trim($data)) > 0) {
	$input = json_decode($data, true);
}

// Fallback: support application/x-www-form-urlencoded or multipart/form-data
if ($input === null && !empty($_POST)) {
	$input = [
		'email' => isset($_POST['email']) ? $_POST['email'] : null,
		'password' => isset($_POST['password']) ? $_POST['password'] : null
	];
}

// Check if JSON decoding failed
if ($data && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit;
}

// Check if input is null or not an array
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Error handling for database connection
if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Check if user exists by email
$stmt = $conn->prepare('SELECT user_id, username, email, password_hash, full_name, profile_meta FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('s', $email);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Update last login timestamp
$update_stmt = $conn->prepare('UPDATE users SET last_login = NOW() WHERE user_id = ?');
if ($update_stmt) {
    $update_stmt->bind_param('i', $user['user_id']);
    $update_stmt->execute();
    $update_stmt->close();
}

// Parse profile_meta if it's a JSON string
$profile_meta = $user['profile_meta'];
if (is_string($profile_meta)) {
    $profile_meta = json_decode($profile_meta, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $profile_meta = ['avatar' => '🆕', 'rank' => 'OPERATIVE', 'specialization' => 'TRAINING'];
    }
}

// Return user data (without password_hash)
$userData = [
    'user_id' => (int)$user['user_id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'full_name' => $user['full_name'],
    'total_points' => (int)($user['total_points'] ?? 0),
    'profile_meta' => $profile_meta
];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => $userData
]);

$conn->close();
?>

