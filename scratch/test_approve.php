<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
require_once __DIR__ . '/../server/helpers/security/permissions.php';

// Try to approve request for User 17 to Admin
$userId = 17;
$role = 'admin';

echo "Attempting to assign role '$role' to User ID $userId...\n";

// Manual steps from request_role.php
$existingRoles = getUserRoles($conn, $userId);
foreach ($existingRoles as $er) {
    echo "Removing existing role: $er\n";
    removeRole($conn, $userId, $er);
}

$success = assignRole($conn, $userId, $role, 4); // Use 4 as admin ID (Ahmed)

if ($success) {
    echo "✅ Success! Role assigned.\n";
} else {
    echo "❌ Failed! Check error logs or table constraints.\n";
    echo "DB Error: " . $conn->error . "\n";
}
