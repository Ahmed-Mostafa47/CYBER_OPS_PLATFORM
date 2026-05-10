<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
$conn->query("UPDATE challenges SET whitebox_files_ref = NULL WHERE lab_id IN (11, 12, 18, 19, 20, 21)");
echo "Affected rows: " . $conn->affected_rows . "\n";
?>
