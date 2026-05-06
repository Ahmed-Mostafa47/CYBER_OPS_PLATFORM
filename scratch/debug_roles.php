<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';

// Fix the table permanently
$conn->query("ALTER TABLE user_roles MODIFY assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
echo "Table user_roles modified to support default timestamp.\n";

echo "--- First 5 Users ---\n";
$res = $conn->query("SELECT user_id, username FROM users LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . " | Username: " . $row['username'] . "\n";
    }
}

echo "--- System Roles ---\n";
$res = $conn->query("SELECT * FROM roles");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['role_id'] . " | Name: " . $row['name'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n--- user_roles Structure ---\n";
$res = $conn->query("DESCRIBE user_roles");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
    }
}

echo "\n--- role_requests Structure ---\n";
$res = $conn->query("DESCRIBE role_requests");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
    }
}
