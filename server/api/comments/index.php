<?php
declare(strict_types=1);

// Start output buffering to prevent warnings from corrupting JSON
ob_start();

// Suppress display of errors (we'll log them instead)
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    ob_end_flush();
    exit;
}

require_once __DIR__ . '/../../utils/db_connect.php';
require_once __DIR__ . '/comments_repository.php';

$method = $_SERVER['REQUEST_METHOD'];
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$segments = array_values(array_filter(explode('/', $pathInfo)));

try {
    switch ($method) {
        case 'GET':
            handle_get_comments($conn);
            break;
        case 'POST':
            handle_create_comment($conn);
            break;
        case 'DELETE':
            handle_delete_comment($conn, $segments);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method Not Allowed',
            ]);
            break;
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    error_log('Comments API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getFile() . ':' . $e->getLine(),
    ]);
    ob_end_flush();
}

function handle_get_comments(mysqli $conn): void
{
    $currentUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    if ($currentUserId && $currentUserId < 1) {
        $currentUserId = null;
    }

    if ($currentUserId) {
        $sql = "
            SELECT 
                c.id,
                c.user_id,
                c.content,
                c.created_at,
                c.updated_at,
                u.username,
                u.profile_meta,
                COALESCE(l.like_count, 0) AS likes,
                EXISTS(
                    SELECT 1 
                    FROM comment_likes cl 
                    WHERE cl.comment_id = c.id 
                      AND cl.user_id = ?
                ) AS liked_by_current_user
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            LEFT JOIN (
                SELECT comment_id, COUNT(*) AS like_count
                FROM comment_likes
                GROUP BY comment_id
            ) l ON l.comment_id = c.id
            ORDER BY c.created_at DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $conn->error);
        }
        $stmt->bind_param('i', $currentUserId);
    } else {
        $sql = "
            SELECT 
                c.id,
                c.user_id,
                c.content,
                c.created_at,
                c.updated_at,
                u.username,
                u.profile_meta,
                COALESCE(l.like_count, 0) AS likes,
                0 AS liked_by_current_user
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            LEFT JOIN (
                SELECT comment_id, COUNT(*) AS like_count
                FROM comment_likes
                GROUP BY comment_id
            ) l ON l.comment_id = c.id
            ORDER BY c.created_at DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $conn->error);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];

    while ($row = $result->fetch_assoc()) {
        $comments[] = format_comment_row($row);
    }

    $stmt->close();

    ob_clean();
    echo json_encode([
        'success' => true,
        'comments' => $comments,
    ]);
    ob_end_flush();
}

function handle_create_comment(mysqli $conn): void
{
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);

    if (!is_array($payload)) {
        throw new InvalidArgumentException('Invalid payload');
    }

    $userId = isset($payload['user_id']) ? (int)$payload['user_id'] : 0;
    $content = isset($payload['content']) ? (string)$payload['content'] : '';

    if ($userId < 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing or invalid user_id',
        ]);
        return;
    }

    if (trim($content) === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Comment content is required',
        ]);
        return;
    }

    $sanitizedContent = sanitize_comment_text($content);

    $stmt = $conn->prepare("INSERT INTO comments (user_id, content) VALUES (?, ?)");
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare insert statement: ' . $conn->error);
    }

    $stmt->bind_param('is', $userId, $sanitizedContent);

    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to create comment: ' . $stmt->error);
    }

    $commentId = (int)$conn->insert_id;
    $stmt->close();

    $comment = fetch_comment_by_id($conn, $commentId, $userId);

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Comment created',
        'comment' => $comment,
    ]);
    ob_end_flush();
}

function handle_delete_comment(mysqli $conn, array $segments): void
{
    $commentId = extract_comment_id($segments);

    $rawBody = file_get_contents('php://input');
    $body = $rawBody ? json_decode($rawBody, true) : [];

    if (!is_array($body)) {
        $body = [];
    }

    $userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;

    if ($commentId < 1) {
        ob_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid comment id',
        ]);
        ob_end_flush();
        return;
    }

    if ($userId < 1) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'User context required',
        ]);
        ob_end_flush();
        return;
    }

    if (!user_is_admin($conn, $userId)) {
        ob_clean();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin privileges required',
        ]);
        ob_end_flush();
        return;
    }

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare delete statement: ' . $conn->error);
    }

    $stmt->bind_param('i', $commentId);
    $stmt->execute();

    $deleted = $stmt->affected_rows > 0;
    $stmt->close();

    if (!$deleted) {
        ob_clean();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Comment not found',
        ]);
        ob_end_flush();
        return;
    }

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Comment deleted',
    ]);
    ob_end_flush();
}

function extract_comment_id(array $segments): int
{
    // First try to get from PATH_INFO segments (e.g., /comments/3)
    if (!empty($segments)) {
        $first = reset($segments);
        if (is_numeric($first)) {
            return (int)$first;
        }
    }

    // Then try query parameter (e.g., /comments.php?id=3)
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        return (int)$_GET['id'];
    }

    // Also check REQUEST_URI for numeric segments
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $uriParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));
    foreach ($uriParts as $part) {
        if (is_numeric($part)) {
            return (int)$part;
        }
    }

    return 0;
}


