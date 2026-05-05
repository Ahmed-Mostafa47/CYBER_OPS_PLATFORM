<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
$r = $conn->query("SELECT COUNT(*) as c FROM labs WHERE is_published = 1");
echo "Published: " . $r->fetch_assoc()['c'] . "\n";
$r = $conn->query("SELECT COUNT(*) as c FROM labs");
echo "Total: " . $r->fetch_assoc()['c'] . "\n";
$r = $conn->query("SELECT lab_id, title, is_published, visibility FROM labs WHERE is_published = 0");
echo "Unpublished Labs:\n";
while($row = $r->fetch_assoc()) {
    echo "- ID: {$row['lab_id']}, Title: {$row['title']}, Visibility: {$row['visibility']}\n";
}
