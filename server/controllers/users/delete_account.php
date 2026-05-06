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

require_once __DIR__ . '/../../core/db/db_connect.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$data = file_get_contents('php://input');
$input = json_decode($data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

if (!isset($conn) || !$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$password = isset($input['password']) ? $input['password'] : '';

// PROTECTED USER: Cannot delete user with ID 9
if ($user_id === 9) {
    echo json_encode(['success' => false, 'message' => 'This account is protected and cannot be deleted.']);
    exit;
}

// Validation
if (!$user_id || !$password) {
    echo json_encode(['success' => false, 'message' => 'User ID and password are required']);
    exit;
}

// Fetch user and verify password
$user_stmt = $conn->prepare('SELECT user_id, password_hash FROM users WHERE user_id = ? AND is_active = 1 LIMIT 1');
if (!$user_stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
    exit;
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Password is incorrect']);
    exit;
}

// Start transaction for cascading deletes
$conn->begin_transaction();

try {
    // Delete user roles
    $delete_roles_stmt = $conn->prepare('DELETE FROM user_roles WHERE user_id = ?');
    if ($delete_roles_stmt) {
        $delete_roles_stmt->bind_param('i', $user_id);
        $delete_roles_stmt->execute();
        $delete_roles_stmt->close();
    }
    
    // Delete role requests
    $delete_role_requests_stmt = $conn->prepare('DELETE FROM role_requests WHERE user_id = ?');
    if ($delete_role_requests_stmt) {
        $delete_role_requests_stmt->bind_param('i', $user_id);
        $delete_role_requests_stmt->execute();
        $delete_role_requests_stmt->close();
    }
    
    // Delete password resets
    $delete_resets_stmt = $conn->prepare('DELETE FROM password_resets WHERE user_id = ?');
    if ($delete_resets_stmt) {
        $delete_resets_stmt->bind_param('i', $user_id);
        $delete_resets_stmt->execute();
        $delete_resets_stmt->close();
    }
    
    // Delete comments
    $delete_comments_stmt = $conn->prepare('DELETE FROM comments WHERE user_id = ?');
    if ($delete_comments_stmt) {
        $delete_comments_stmt->bind_param('i', $user_id);
        $delete_comments_stmt->execute();
        $delete_comments_stmt->close();
    }
    
    // Delete notifications
    $delete_notifications_stmt = $conn->prepare('DELETE FROM notifications WHERE user_id = ?');
    if ($delete_notifications_stmt) {
        $delete_notifications_stmt->bind_param('i', $user_id);
        $delete_notifications_stmt->execute();
        $delete_notifications_stmt->close();
    }

    // Delete findings (where user is the reviewer)
    $delete_findings_stmt = $conn->prepare('DELETE FROM findings WHERE reviewer_id = ?');
    if ($delete_findings_stmt) {
        $delete_findings_stmt->bind_param('i', $user_id);
        $delete_findings_stmt->execute();
        $delete_findings_stmt->close();
    }

    // Delete submission files and findings related to user's submissions
    $sub_query = $conn->prepare('SELECT submission_id FROM submissions WHERE user_id = ?');
    if ($sub_query) {
        $sub_query->bind_param('i', $user_id);
        $sub_query->execute();
        $sub_result = $sub_query->get_result();
        while ($row = $sub_result->fetch_assoc()) {
            $sid = $row['submission_id'];
            
            // Delete findings for this submission
            $del_f = $conn->prepare('DELETE FROM findings WHERE submission_id = ?');
            $del_f->bind_param('i', $sid);
            $del_f->execute();
            $del_f->close();

            // Delete submission files for this submission
            $del_sf = $conn->prepare('DELETE FROM submission_files WHERE submission_id = ?');
            $del_sf->bind_param('i', $sid);
            $del_sf->execute();
            $del_sf->close();

            // Delete challenge comments for this submission
            $del_cc = $conn->prepare('DELETE FROM Challenges_comments WHERE submission_id = ?');
            $del_cc->bind_param('i', $sid);
            $del_cc->execute();
            $del_cc->close();
        }
        $sub_query->close();
    }

    // Delete file resources owned by user
    $delete_files_stmt = $conn->prepare('DELETE FROM file_resources WHERE owner_id = ?');
    if ($delete_files_stmt) {
        $delete_files_stmt->bind_param('i', $user_id);
        $delete_files_stmt->execute();
        $delete_files_stmt->close();
    }

    // Delete audit logs
    $delete_audit_stmt = $conn->prepare('DELETE FROM audit_logs WHERE user_id = ?');
    if ($delete_audit_stmt) {
        $delete_audit_stmt->bind_param('i', $user_id);
        $delete_audit_stmt->execute();
        $delete_audit_stmt->close();
    }

    // Delete attempt logs
    $delete_attempts_stmt = $conn->prepare('DELETE FROM attempt_logs WHERE user_id = ?');
    if ($delete_attempts_stmt) {
        $delete_attempts_stmt->bind_param('i', $user_id);
        $delete_attempts_stmt->execute();
        $delete_attempts_stmt->close();
    }

    // Delete blocks
    $delete_blocks_stmt = $conn->prepare('DELETE FROM blocks WHERE user_id = ?');
    if ($delete_blocks_stmt) {
        $delete_blocks_stmt->bind_param('i', $user_id);
        $delete_blocks_stmt->execute();
        $delete_blocks_stmt->close();
    }

    // Delete leaderboard entry
    $delete_leaderboard_stmt = $conn->prepare('DELETE FROM leaderboard WHERE user_id = ?');
    if ($delete_leaderboard_stmt) {
        $delete_leaderboard_stmt->bind_param('i', $user_id);
        $delete_leaderboard_stmt->execute();
        $delete_leaderboard_stmt->close();
    }

    // Delete challenge comments (where user_id matches)
    $delete_chal_comments_stmt = $conn->prepare('DELETE FROM Challenges_comments WHERE user_id = ?');
    if ($delete_chal_comments_stmt) {
        $delete_chal_comments_stmt->bind_param('i', $user_id);
        $delete_chal_comments_stmt->execute();
        $delete_chal_comments_stmt->close();
    }

    // Delete submissions
    $delete_submissions_stmt = $conn->prepare('DELETE FROM submissions WHERE user_id = ? OR reviewer_id = ?');
    if ($delete_submissions_stmt) {
        $delete_submissions_stmt->bind_param('ii', $user_id, $user_id);
        $delete_submissions_stmt->execute();
        $delete_submissions_stmt->close();
    }

    // Delete lab instances
    $delete_instances_stmt = $conn->prepare('DELETE FROM lab_instances WHERE user_id = ?');
    if ($delete_instances_stmt) {
        $delete_instances_stmt->bind_param('i', $user_id);
        $delete_instances_stmt->execute();
        $delete_instances_stmt->close();
    }
    
    // Finally, delete the user
    $delete_user_stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
    if (!$delete_user_stmt) {
        throw new Exception('Failed to prepare delete user statement: ' . $conn->error);
    }
    
    $delete_user_stmt->bind_param('i', $user_id);
    
    if (!$delete_user_stmt->execute()) {
        throw new Exception('Failed to delete user: ' . $delete_user_stmt->error);
    }
    
    $delete_user_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Account deleted successfully'
    ]);
    
} catch (Throwable $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    error_log("Delete Account Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete account: ' . $e->getMessage()
    ]);
}
$conn->close();
?>


