<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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

function isAdminOrSuperadmin($conn, $userId) {
    $rolesRes = $conn->query("
        SELECT 1
        FROM user_roles ur
        INNER JOIN roles r ON r.role_id = ur.role_id
        WHERE ur.user_id = " . (int)$userId . "
        AND LOWER(r.name) IN ('admin','superadmin')
        LIMIT 1
    ");
    return ($rolesRes && $rolesRes->num_rows > 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = (int)($_GET['user_id'] ?? 0);
    if ($userId < 1 || !isAdminOrSuperadmin($conn, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }

    $query = "
        SELECT 
            ldr.id,
            ldr.lab_id,
            l.title as lab_title,
            ldr.instructor_id,
            u.username as instructor_username,
            u.full_name as instructor_full_name,
            ldr.status,
            ldr.created_at
        FROM lab_deletion_requests ldr
        JOIN labs l ON l.lab_id = ldr.lab_id
        JOIN users u ON u.user_id = ldr.instructor_id
        WHERE ldr.status = 'pending'
        ORDER BY ldr.created_at DESC
    ";
    $result = $conn->query($query);
    $requests = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => ['requests' => $requests]]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }

    $userId = (int)($input['user_id'] ?? 0);
    $requestId = (int)($input['request_id'] ?? 0);
    $action = (string)($input['action'] ?? ''); // 'approve' or 'reject'
    $clientLocalIp = trim((string)($input['client_local_ip'] ?? ''));

    if ($userId < 1 || $requestId < 1 || !in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input parameters']);
        exit;
    }

    if (!isAdminOrSuperadmin($conn, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }

    $actorUsername = '';
    $actorRes = $conn->query("SELECT username FROM users WHERE user_id = $userId LIMIT 1");
    if ($actorRes && $actorRes->num_rows > 0) {
        $actorRow = $actorRes->fetch_assoc();
        $actorUsername = (string)($actorRow['username'] ?? '');
    }

    $reqRes = $conn->query("SELECT lab_id, status FROM lab_deletion_requests WHERE id = $requestId LIMIT 1");
    if (!$reqRes || $reqRes->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit;
    }
    $reqRow = $reqRes->fetch_assoc();
    if ($reqRow['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Request is already processed']);
        exit;
    }
    $labId = (int)$reqRow['lab_id'];

    if ($action === 'reject') {
        $conn->query("UPDATE lab_deletion_requests SET status = 'rejected' WHERE id = $requestId");
        hackme_write_audit_log($conn, [
            'actor_user_id' => $userId,
            'actor_username' => $actorUsername,
            'action' => 'lab_delete_reject',
            'status' => 'success',
            'details' => json_encode(['request_id' => $requestId, 'lab_id' => $labId], JSON_UNESCAPED_UNICODE),
            'ip_address' => hackme_client_ip(),
            'client_local_ip' => $clientLocalIp,
            'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
        ]);
        echo json_encode(['success' => true, 'message' => 'Deletion request rejected']);
        exit;
    }

    if ($action === 'approve') {
        // We need to delete the lab, then update request to 'approved'
        $labRes = $conn->query("SELECT title FROM labs WHERE lab_id = $labId LIMIT 1");
        $labTitle = $labRes && $labRes->num_rows > 0 ? $labRes->fetch_assoc()['title'] : "Unknown Lab";

        $conn->begin_transaction();
        try {
            // Update request status first
            $conn->query("UPDATE lab_deletion_requests SET status = 'approved' WHERE id = $requestId");

            // Delete lab dependencies
            $conn->query("DELETE FROM hints WHERE challenge_id IN (SELECT challenge_id FROM challenges WHERE lab_id = $labId)");
            $conn->query("DELETE FROM challenges WHERE lab_id = $labId");
            $conn->query("DELETE FROM lab_completions WHERE lab_id = $labId");
            // Delete lab
            $conn->query("DELETE FROM labs WHERE lab_id = $labId LIMIT 1");
            
            $conn->commit();
            
            hackme_write_audit_log($conn, [
                'actor_user_id' => $userId,
                'actor_username' => $actorUsername,
                'action' => 'lab_delete_approve',
                'status' => 'success',
                'details' => json_encode(['request_id' => $requestId, 'lab_id' => $labId, 'lab_title' => $labTitle], JSON_UNESCAPED_UNICODE),
                'ip_address' => hackme_client_ip(),
                'client_local_ip' => $clientLocalIp,
                'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Lab deletion approved and lab deleted']);
        } catch (Throwable $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete lab: ' . $e->getMessage()]);
        }
        exit;
    }
}
