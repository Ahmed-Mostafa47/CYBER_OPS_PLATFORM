<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
$res = $conn->query("SELECT * FROM submissions WHERE type = 'whitebox' OR payload_text LIKE 'whitebox%' ORDER BY submission_id DESC LIMIT 10");
if ($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
