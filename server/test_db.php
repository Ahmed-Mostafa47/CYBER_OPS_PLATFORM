<?php
// Test database connection and check users
require_once __DIR__ . '/utils/db_connect.php';

header('Content-Type: application/json');

// Test connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get current database name
$result = $conn->query("SELECT DATABASE() as db_name");
$db_info = $result->fetch_assoc();

// Check if users table exists
$tables_result = $conn->query("SHOW TABLES LIKE 'users'");
$users_table_exists = $tables_result->num_rows > 0;

// Get users count
$users_count = 0;
$users_list = [];
if ($users_table_exists) {
    $users_result = $conn->query("SELECT user_id, username, email, is_active FROM users LIMIT 10");
    if ($users_result) {
        $users_list = $users_result->fetch_all(MYSQLI_ASSOC);
        $users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    }
}

echo json_encode([
    'success' => true,
    'database_name' => $db_info['db_name'] ?? 'unknown',
    'users_table_exists' => $users_table_exists,
    'users_count' => $users_count,
    'users' => $users_list,
    'connection_status' => 'Connected successfully'
], JSON_PRETTY_PRINT);

$conn->close();
?>

