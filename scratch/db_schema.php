<?php
require_once __DIR__ . '/../server/core/db/db_connect.php';
$res = $conn->query("SHOW CREATE TABLE hints");
if ($res) print_r($res->fetch_assoc());

$res2 = $conn->query("SHOW CREATE TABLE challenges");
if ($res2) print_r($res2->fetch_assoc());

$res3 = $conn->query("SHOW CREATE TABLE labs");
if ($res3) print_r($res3->fetch_assoc());
