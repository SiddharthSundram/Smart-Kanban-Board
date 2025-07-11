<?php
require_once '../config/config.php';

$now = new DateTime();
$soon = (clone $now)->modify('+1 day')->format('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE due_date = ?");
$stmt->execute([$soon]);
$tasks = $stmt->fetchAll();

foreach ($tasks as $task) {
    $assignees = explode(',', $task['assignee_ids']);
    foreach ($assignees as $userId) {
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, data) VALUES (?, 'task_due_soon', ?)");
        $notifStmt->execute([
            $userId,
            json_encode([
                'task_id' => $task['id'],
                'board_id' => $task['column_id'],
                'title' => $task['title']
            ])
        ]);
    }
}
