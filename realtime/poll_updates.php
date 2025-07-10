<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$boardId = $_GET['board_id'] ?? null;
$lastFetch = $_GET['last_fetch'] ?? 0;

if (!$boardId) {
    echo json_encode(['updated' => false, 'timestamp' => time()]);
    exit;
}

// Get latest update time for tasks in this board
$stmt = $pdo->prepare("
    SELECT MAX(updated_at) as last_updated
    FROM tasks t
    JOIN columns c ON t.column_id = c.id
    WHERE c.board_id = ?
");
$stmt->execute([$boardId]);
$row = $stmt->fetch();

$latest = strtotime($row['last_updated'] ?? '1970-01-01');

echo json_encode([
    'updated' => $latest > $lastFetch,
    'timestamp' => time()
]);
