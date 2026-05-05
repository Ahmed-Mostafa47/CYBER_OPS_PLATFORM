<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
$ids = [12, 18, 19, 20, 21];
$r = $conn->query("SELECT lab_id FROM labs WHERE lab_id IN (" . implode(',', $ids) . ")");
$found = [];
while($row = $r->fetch_assoc()) $found[] = $row['lab_id'];
echo "Found in DB: " . implode(', ', $found) . "\n";
echo "Missing in DB: " . implode(', ', array_diff($ids, $found)) . "\n";
