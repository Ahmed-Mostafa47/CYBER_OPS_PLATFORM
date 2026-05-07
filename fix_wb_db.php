<?php
require_once __DIR__ . '/server/core/db/db_connect.php';
require_once __DIR__ . '/server/core/config/labs_config.php';
require_once __DIR__ . '/server/helpers/whitebox/whitebox_lab1_defaults.php';
require_once __DIR__ . '/server/helpers/whitebox/whitebox_lab12_defaults.php';
require_once __DIR__ . '/server/helpers/whitebox/whitebox_lab18_defaults.php';
require_once __DIR__ . '/server/helpers/whitebox/whitebox_lab19_defaults.php';
require_once __DIR__ . '/server/helpers/whitebox/whitebox_xss_defaults.php';

$wb_labs = [
    11 => hackme_whitebox_lab1_meta_json(),
    12 => hackme_whitebox_lab12_meta_json(),
    18 => hackme_whitebox_lab18_meta_json(),
    19 => hackme_whitebox_lab19_meta_json(),
    20 => hackme_whitebox_xss_meta_json_for_lab(20),
    21 => hackme_whitebox_xss_meta_json_for_lab(21),
];

foreach ($wb_labs as $id => $json) {
    echo "Updating Lab ID: $id\n";
    $jsonEsc = $conn->real_escape_string($json);
    
    // Ensure challenge exists
    $check = $conn->query("SELECT challenge_id FROM challenges WHERE lab_id = $id LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $cid = $row['challenge_id'];
        $conn->query("UPDATE challenges SET whitebox_files_ref = '$jsonEsc' WHERE challenge_id = $cid");
        echo "  Updated challenge $cid\n";
    } else {
        echo "  No challenge found for lab $id. Creating one...\n";
        $conn->query("INSERT INTO challenges (lab_id, created_by, title, statement, order_index, max_score, difficulty, is_active, whitebox_files_ref) 
                      VALUES ($id, 1, 'WHITEBOX_CHALLENGE', 'Fix the vulnerability', 1, 100, 'medium', 1, '$jsonEsc')");
    }
}
echo "Done.\n";
