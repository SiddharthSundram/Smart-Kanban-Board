<?php
require_once '../config/config.php';

$column_id = $_GET['column_id'] ?? null;
if (!$column_id) {
    echo json_encode(['error' => 'Missing column ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT wip_limit FROM columns WHERE id = ?");
$stmt->execute([$column_id]);
$limit = $stmt->fetchColumn();

echo json_encode(['wip_limit' => $limit]);
