<?php
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
