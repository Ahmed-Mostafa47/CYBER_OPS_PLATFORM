<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../../core/db/db_connect.php';
    require_once __DIR__ . '/../../../helpers/security/audit_log.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Load error']);
    exit;
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$userId = (int) ($input['user_id'] ?? 0);
$labId = (int) ($input['lab_id'] ?? 0);
$clientLocalIp = trim((string) ($input['client_local_ip'] ?? ''));
if ($userId < 1 || $labId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing user_id or lab_id']);
    exit;
}

$actorUsername = '';
$actorRes = $conn->query("SELECT username FROM users WHERE user_id = $userId LIMIT 1");
if ($actorRes && $actorRes->num_rows > 0) {
    $actorRow = $actorRes->fetch_assoc();
    $actorUsername = (string) ($actorRow['username'] ?? '');
}

$rolesRes = $conn->query("
  SELECT LOWER(r.name) as role_name
  FROM user_roles ur
  INNER JOIN roles r ON r.role_id = ur.role_id
  WHERE ur.user_id = $userId
");
$isAdmin = false;
$isInstructor = false;
if ($rolesRes && $rolesRes->num_rows > 0) {
    while ($rRow = $rolesRes->fetch_assoc()) {
        if (in_array($rRow['role_name'], ['admin', 'superadmin'])) {
            $isAdmin = true;
        }
        if ($rRow['role_name'] === 'instructor') {
            $isInstructor = true;
        }
    }
}

if (!$isAdmin && !$isInstructor) {
    hackme_write_audit_log($conn, [
        'actor_user_id' => $userId,
        'actor_username' => $actorUsername,
        'action' => 'lab_delete',
        'status' => 'failed',
        'details' => json_encode([
            'message' => 'Delete lab failed: insufficient permissions',
            'lab_id' => $labId,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'ip_address' => hackme_client_ip(),
        'client_local_ip' => $clientLocalIp,
        'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ]);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}

if (!$isAdmin && $isInstructor) {
    // Only instructor: send deletion request instead of deleting
    $stmt = $conn->prepare("INSERT INTO lab_deletion_requests (lab_id, instructor_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param('ii', $labId, $userId);
    if ($stmt->execute()) {
        hackme_write_audit_log($conn, [
            'actor_user_id' => $userId,
            'actor_username' => $actorUsername,
            'action' => 'lab_delete_request',
            'status' => 'success',
            'details' => json_encode([
                'message' => 'Requested lab deletion',
                'lab_id' => $labId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_address' => hackme_client_ip(),
            'client_local_ip' => $clientLocalIp,
            'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Deletion request sent to administrators',
            'data' => [
                'lab_id' => $labId,
                'status' => 'pending'
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to submit deletion request']);
    }
    exit;
}

$labRes = $conn->query("SELECT lab_id, title FROM labs WHERE lab_id = $labId LIMIT 1");
if (!$labRes || $labRes->num_rows === 0) {
    hackme_write_audit_log($conn, [
        'actor_user_id' => $userId,
        'actor_username' => $actorUsername,
        'action' => 'lab_delete',
        'status' => 'failed',
        'details' => json_encode([
            'message' => 'Delete lab failed: lab not found',
            'lab_id' => $labId,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'ip_address' => hackme_client_ip(),
        'client_local_ip' => $clientLocalIp,
        'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ]);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Lab not found']);
    exit;
}
$labRow = $labRes->fetch_assoc();
$labTitle = (string) ($labRow['title'] ?? '');

$conn->begin_transaction();
try {
    // Delete hints related to this lab's challenges
    $conn->query("DELETE FROM hints WHERE challenge_id IN (SELECT challenge_id FROM challenges WHERE lab_id = $labId)");
    
    // Delete challenges for this lab
    $conn->query("DELETE FROM challenges WHERE lab_id = $labId");

    // Delete lab completions for this lab
    $conn->query("DELETE FROM lab_completions WHERE lab_id = $labId");

    // Finally delete the lab itself
    $conn->query("DELETE FROM labs WHERE lab_id = $labId LIMIT 1");
    if ((int) $conn->affected_rows < 1) {
        throw new RuntimeException("Lab was not deleted");
    }
    $conn->commit();
    $ok = true;
} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable) {}
    $ok = false;
    $deleteError = $e->getMessage();
}

if (!$ok) {
    hackme_write_audit_log($conn, [
        'actor_user_id' => $userId,
        'actor_username' => $actorUsername,
        'action' => 'lab_delete',
        'status' => 'failed',
        'details' => json_encode([
            'message' => 'Delete lab failed',
            'lab_id' => $labId,
            'lab_title' => $labTitle,
            'error' => $deleteError,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'ip_address' => hackme_client_ip(),
        'client_local_ip' => $clientLocalIp,
        'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete lab']);
    exit;
}

hackme_write_audit_log($conn, [
    'actor_user_id' => $userId,
    'actor_username' => $actorUsername,
    'action' => 'lab_delete',
    'status' => 'success',
    'details' => json_encode([
        'message' => 'Deleted lab successfully',
        'lab_id' => $labId,
        'lab_title' => $labTitle,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'ip_address' => hackme_client_ip(),
    'client_local_ip' => $clientLocalIp,
    'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
]);

echo json_encode([
    'success' => true,
    'message' => 'Lab deleted',
    'data' => [
        'lab_id' => $labId,
        'lab_title' => $labTitle,
    ],
]);
