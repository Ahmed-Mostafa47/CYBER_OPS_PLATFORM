<?php
declare(strict_types=1);

// Start output buffering
ob_start();

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    ob_end_flush();
    exit;
}

require_once __DIR__ . '/../utils/db_connect.php';
require_once __DIR__ . '/../utils/permissions.php';

$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handle_get_request($conn);
            break;
        case 'POST':
            handle_post_request($conn);
            break;
        case 'PUT':
            handle_put_request($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method Not Allowed'
            ]);
            break;
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    error_log('Manage Users API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}

/**
 * GET Request Handler
 * - Get all users with their roles and permissions
 * - Get single user details
 */
function handle_get_request(mysqli $conn)
{
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    $currentUserId = isset($_GET['current_user_id']) ? (int)$_GET['current_user_id'] : null;
    
    // Check if current user is superadmin or admin
    if ($currentUserId && !isSuperAdmin($conn, $currentUserId) && !isAdmin($conn, $currentUserId)) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Admin or SuperAdmin privileges required.'
        ]);
        ob_end_flush();
        return;
    }
    
    if ($userId) {
        // Get single user with roles and permissions
        $user = getUserDetails($conn, $userId);
        
        if (!$user) {
            ob_clean();
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
            ob_end_flush();
            return;
        }
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
        ob_end_flush();
    } else {
        // Get all users with their roles
        $users = getAllUsersWithRoles($conn);
        
        // Get all available roles
        $roles = getAllRoles($conn);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'users' => $users,
            'available_roles' => $roles
        ]);
        ob_end_flush();
    }
}

/**
 * POST Request Handler
 * - Assign role to user
 */
function handle_post_request(mysqli $conn)
{
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['current_user_id']) || !isset($data['target_user_id']) || !isset($data['role_name'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: current_user_id, target_user_id, role_name'
        ]);
        ob_end_flush();
        return;
    }
    
    $currentUserId = (int)$data['current_user_id'];
    $targetUserId = (int)$data['target_user_id'];
    $roleName = strtolower(trim($data['role_name']));
    
    // Check if current user is superadmin (only superadmin can assign roles)
    if (!isSuperAdmin($conn, $currentUserId)) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. SuperAdmin privileges required to assign roles.'
        ]);
        ob_end_flush();
        return;
    }
    
    // Prevent removing superadmin role from the last superadmin
    if ($roleName !== 'superadmin') {
        $currentUserRoles = getUserRoles($conn, $currentUserId);
        if (in_array('superadmin', $currentUserRoles) && $currentUserId === $targetUserId) {
            // Check if there are other superadmins
            $superAdminCount = countSuperAdmins($conn, $currentUserId);
            if ($superAdminCount === 0) {
                ob_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot remove superadmin role. You are the last superadmin.'
                ]);
                ob_end_flush();
                return;
            }
        }
    }
    
    // Remove all existing roles first (single role system)
    $existingRoles = getUserRoles($conn, $targetUserId);
    foreach ($existingRoles as $existingRole) {
        // Don't remove superadmin if it's the last one
        if ($existingRole === 'superadmin') {
            $superAdminCount = countSuperAdmins($conn, $targetUserId);
            if ($superAdminCount <= 1 && $roleName !== 'superadmin') {
                ob_clean();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot remove superadmin role. This is the last superadmin.'
                ]);
                ob_end_flush();
                return;
            }
        }
        removeRole($conn, $targetUserId, $existingRole);
    }
    
    // Assign new role
    $success = assignRole($conn, $targetUserId, $roleName, $currentUserId);
    
    if ($success) {
        // Update profile_meta for backward compatibility
        $roleNameUpper = strtoupper($roleName);
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET profile_meta = JSON_SET(
                COALESCE(profile_meta, '{}'),
                '$.rank', ?
            )
            WHERE user_id = ?
        ");
        if ($updateStmt) {
            $updateStmt->bind_param('si', $roleNameUpper, $targetUserId);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        $user = getUserDetails($conn, $targetUserId);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Role assigned successfully (previous roles removed)',
            'user' => $user
        ]);
        ob_end_flush();
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to assign role'
        ]);
        ob_end_flush();
    }
}

/**
 * PUT Request Handler
 * - Remove role from user
 */
function handle_put_request(mysqli $conn)
{
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['current_user_id']) || !isset($data['target_user_id']) || !isset($data['role_name'])) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: current_user_id, target_user_id, role_name'
        ]);
        ob_end_flush();
        return;
    }
    
    $currentUserId = (int)$data['current_user_id'];
    $targetUserId = (int)$data['target_user_id'];
    $roleName = strtolower(trim($data['role_name']));
    
    // Check if current user is superadmin
    if (!isSuperAdmin($conn, $currentUserId)) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. SuperAdmin privileges required to remove roles.'
        ]);
        ob_end_flush();
        return;
    }
    
    // Prevent removing superadmin role from the last superadmin
    if ($roleName === 'superadmin') {
        $superAdminCount = countSuperAdmins($conn, $targetUserId);
        if ($superAdminCount <= 1) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Cannot remove superadmin role. This is the last superadmin.'
            ]);
            ob_end_flush();
            return;
        }
    }
    
    // Remove role
    $success = removeRole($conn, $targetUserId, $roleName);
    
    if ($success) {
        $user = getUserDetails($conn, $targetUserId);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Role removed successfully',
            'user' => $user
        ]);
        ob_end_flush();
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove role'
        ]);
        ob_end_flush();
    }
}

/**
 * Get all users with their roles
 */
function getAllUsersWithRoles(mysqli $conn): array
{
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.full_name,
            u.is_active,
            u.created_at,
            u.last_login,
            u.profile_meta
        FROM users u
        ORDER BY u.created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare query: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $userId = (int)$row['user_id'];
        $roles = getUserRoles($conn, $userId);
        $permissions = getUserPermissions($conn, $userId);
        
        $profileMeta = $row['profile_meta'];
        if (is_string($profileMeta)) {
            $profileMeta = json_decode($profileMeta, true);
        }
        
        $users[] = [
            'user_id' => $userId,
            'username' => $row['username'],
            'email' => $row['email'],
            'full_name' => $row['full_name'],
            'is_active' => (bool)$row['is_active'],
            'created_at' => $row['created_at'],
            'last_login' => $row['last_login'],
            'profile_meta' => $profileMeta,
            'roles' => $roles,
            'permissions' => $permissions
        ];
    }
    
    $stmt->close();
    return $users;
}

/**
 * Get user details with roles and permissions
 */
function getUserDetails(mysqli $conn, int $userId): ?array
{
    $stmt = $conn->prepare("
        SELECT 
            user_id,
            username,
            email,
            full_name,
            is_active,
            created_at,
            last_login,
            profile_meta
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare query: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        return null;
    }
    
    $roles = getUserRoles($conn, $userId);
    $permissions = getUserPermissions($conn, $userId);
    
    $profileMeta = $row['profile_meta'];
    if (is_string($profileMeta)) {
        $profileMeta = json_decode($profileMeta, true);
    }
    
    return [
        'user_id' => (int)$row['user_id'],
        'username' => $row['username'],
        'email' => $row['email'],
        'full_name' => $row['full_name'],
        'is_active' => (bool)$row['is_active'],
        'created_at' => $row['created_at'],
        'last_login' => $row['last_login'],
        'profile_meta' => $profileMeta,
        'roles' => $roles,
        'permissions' => $permissions
    ];
}

/**
 * Count superadmins excluding a specific user
 */
function countSuperAdmins(mysqli $conn, int $excludeUserId): int
{
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT ur.user_id) as count
        FROM user_roles ur
        INNER JOIN roles r ON ur.role_id = r.role_id
        WHERE LOWER(r.name) = 'superadmin' AND ur.user_id != ?
    ");
    
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param('i', $excludeUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)($row['count'] ?? 0);
}

