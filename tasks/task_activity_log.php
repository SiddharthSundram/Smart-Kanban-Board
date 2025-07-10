<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if (!$task_id) {
    echo json_encode(['error' => 'Task ID is required.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT a.*, u.full_name 
        FROM task_activity a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.task_id = ? 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$task_id]);
    $log = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($log);
} catch (Exception $e) {
    echo json_encode(['error' => 'Something went wrong.', 'details' => $e->getMessage()]);
}
