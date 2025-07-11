<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';
require_once '../utils/notify.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?? null;
    $column_id = $_POST['column_id'];
    $board_id = $_POST['board_id'];
    $created_by = $_SESSION['user_id'];

    // Check WIP limit
    $stmt = $pdo->prepare("SELECT wip_limit FROM columns WHERE id = ?");
    $stmt->execute([$column_id]);
    $wip_limit = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE column_id = ?");
    $stmt->execute([$column_id]);
    $current_task_count = $stmt->fetchColumn();

    if ($wip_limit && $current_task_count >= $wip_limit) {
        $_SESSION['error'] = "Cannot add more tasks. WIP limit ($wip_limit) reached.";
        redirect('../dashboard/boards/view.php?id=' . $board_id);
    }

    // ✅ Insert the task
    $stmt = $pdo->prepare("INSERT INTO tasks (column_id, title, description, priority, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$column_id, $title, $description, $priority, $due_date, $created_by]);

    $task_id = $pdo->lastInsertId();

    // ✅ Notify all collaborators except the creator
    $stmt = $pdo->prepare("SELECT user_id FROM board_users WHERE board_id = ? AND user_id != ?");
    $stmt->execute([$board_id, $created_by]);
    $collaborators = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($collaborators as $userId) {
        createNotification($userId, 'task_created', [
            'task_id' => $task_id,
            'title' => $title,
            'board_id' => $board_id,
            'column_id' => $column_id
        ]);
    }

    redirect('../dashboard/boards/view.php?id=' . $board_id);
} else {
    die("Invalid request");
}
