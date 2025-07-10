<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';
require_once '../utils/notify.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $assignee_id = $_POST['assignee_id'] ?? null;
    $board_id = $_POST['board_id'] ?? null;

    if (!$task_id || !$assignee_id) {
        die("Missing task or assignee");
    }

    // Update task assignment
    $stmt = $pdo->prepare("UPDATE tasks SET assigned_to = ? WHERE id = ?");
    $stmt->execute([$assignee_id, $task_id]);

    // Get task title for notification
    $stmt = $pdo->prepare("SELECT title FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();

    if ($task) {
        createNotification($assignee_id, 'task_assigned', [
            'title' => $task['title'],
            'task_id' => $task_id,
            'board_id' => $board_id
        ]);
    }

    redirect("../dashboard/boards/view.php?id=$board_id");
} else {
    die("Invalid request");
}
