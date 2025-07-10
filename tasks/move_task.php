<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['task_id'], $data['new_column_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$taskId = (int)$data['task_id'];
$newColumnId = (int)$data['new_column_id'];

$stmt = $pdo->prepare("UPDATE tasks SET column_id = ? WHERE id = ?");
$stmt->execute([$newColumnId, $taskId]);

echo json_encode(["status" => "success"]);