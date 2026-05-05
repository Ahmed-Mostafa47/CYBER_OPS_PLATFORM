<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
$conn->query("UPDATE labs SET is_published = 1, visibility = 'public' WHERE lab_id IN (11, 46)");
echo "Updated: " . $conn->affected_rows . "\n";
