<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
require_once __DIR__ . '/server/core/config/labs_config.php';

echo "White-box SQL Lab ID: " . HACKME_WHITEBOX_SQL_LAB_ID . "\n";

$wb_ids = [11, 12, 18, 19, 20, 21];
foreach ($wb_ids as $id) {
    echo "Checking Lab ID: $id\n";
    $res = $conn->query("SELECT * FROM labs WHERE lab_id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "  Lab found: " . $row['title'] . " (Published: " . $row['is_published'] . ")\n";
    } else {
        echo "  Lab NOT found in 'labs' table.\n";
    }

    $res = $conn->query("SELECT * FROM challenges WHERE lab_id = $id AND is_active = 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "  Challenge found: " . $row['title'] . "\n";
        echo "  Whitebox Files Ref length: " . strlen((string)$row['whitebox_files_ref']) . "\n";
        if (strlen((string)$row['whitebox_files_ref']) > 0) {
            echo "  Whitebox Files Ref: " . substr($row['whitebox_files_ref'], 0, 100) . "...\n";
        }
    } else {
        echo "  Active challenge NOT found for this lab.\n";
    }
    echo "-------------------\n";
}
