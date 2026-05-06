<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
require_once __DIR__ . '/../server/helpers/security/permissions.php';

echo "Cleaning up users with no roles...\n";

$res = $conn->query("SELECT user_id, username FROM users");
while ($user = $res->fetch_assoc()) {
    $userId = (int)$user['user_id'];
    $roles = getUserRoles($conn, $userId);
    
    if (empty($roles)) {
        echo "Assigning 'user' role to: " . $user['username'] . " (ID: $userId)\n";
        assignRole($conn, $userId, 'user', null);
    }
}

echo "Cleanup complete!\n";
