<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
header('Content-Type: application/json; charset=utf-8');

$logger = new WebmasterLogger($db);
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
echo json_encode(['status' => 'success', 'data' => $logger->getLogsSince($lastId)]);