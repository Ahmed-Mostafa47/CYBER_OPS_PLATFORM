<?php
declare(strict_types=1);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ctf_platform';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error,
    ]);
    exit;
}

$conn->set_charset('utf8mb4');

