<?php
declare(strict_types=1);

function sanitize_comment_text(string $text): string
{
    $clean = trim($text);
    $clean = strip_tags($clean);
    $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;
    $clean = mb_substr($clean, 0, 500, 'UTF-8');

    return htmlspecialchars($clean, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function parse_profile_meta(?string $meta): array
{
    if (!$meta) {
        return [];
    }

    $decoded = json_decode($meta, true);
    return is_array($decoded) ? $decoded : [];
}

function format_comment_row(array $row): array
{
    $profileMeta = [];
    if (isset($row['profile_meta'])) {
        $profileMeta = parse_profile_meta($row['profile_meta']);
    }

    return [
        'id' => (int)($row['id'] ?? 0),
        'user_id' => (int)($row['user_id'] ?? 0),
        'user' => $row['username'] ?? 'OPERATIVE',
        'avatar' => $profileMeta['avatar'] ?? '💀',
        'rank' => $profileMeta['rank'] ?? null,
        'text' => $row['content'] ?? '',
        'likes' => (int)($row['likes'] ?? 0),
        'liked_by_current_user' => isset($row['liked_by_current_user'])
            ? (bool)$row['liked_by_current_user']
            : false,
        'created_at' => $row['created_at'] ?? null,
        'updated_at' => $row['updated_at'] ?? null,
    ];
}

function fetch_comment_by_id(mysqli $conn, int $commentId, ?int $currentUserId = null): ?array
{
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
                (
                    SELECT COUNT(*) 
                    FROM comment_likes cl 
                    WHERE cl.comment_id = c.id
                ) AS likes,
                EXISTS(
                    SELECT 1 FROM comment_likes cl2 
                    WHERE cl2.comment_id = c.id 
                      AND cl2.user_id = ?
                ) AS liked_by_current_user
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            WHERE c.id = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $conn->error);
        }
        $stmt->bind_param('ii', $currentUserId, $commentId);
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
                (
                    SELECT COUNT(*) 
                    FROM comment_likes cl 
                    WHERE cl.comment_id = c.id
                ) AS likes,
                0 AS liked_by_current_user
            FROM comments c
            LEFT JOIN users u ON u.user_id = c.user_id
            WHERE c.id = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $conn->error);
        }
        $stmt->bind_param('i', $commentId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }

    return format_comment_row($row);
}

function user_is_admin(mysqli $conn, int $userId): bool
{
    $stmt = $conn->prepare("SELECT profile_meta FROM users WHERE user_id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare role query: ' . $conn->error);
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return false;
    }

    // Check profile_meta for rank
    $profileMeta = parse_profile_meta($user['profile_meta'] ?? null);
    $rank = $profileMeta['rank'] ?? '';

    return strtoupper((string)$rank) === 'ADMIN';
}


