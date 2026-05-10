<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
$res = $conn->query("SELECT NOW() as now");
echo "MySQL NOW(): " . $res->fetch_assoc()['now'] . "\n";
echo "PHP date(): " . date('Y-m-d H:i:s') . "\n";
$conn->close();
