<?php
$allowOrigin = '*';
header("Access-Control-Allow-Origin: $allowOrigin");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ctf_platform';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['success'=>false,'error'=>'DB connection failed: '.$conn->connect_error]);
  exit;
}
?>
