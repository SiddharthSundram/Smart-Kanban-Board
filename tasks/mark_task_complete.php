<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';
require_once '../utils/notify.php';

if (!isset($_GET['id'])) {
    die("Task ID missing.");
}

$task_id = $_GET['id'];
$current_user = $_SESSION['user_id'];

// Get task details and associated board
$stmt = $pdo->prepare("
    SELECT t.title, t.column_id, c.board_id 
    FROM tasks t 
    JOIN columns c ON t.column_id = c.id 
    WHERE t.id = ?
");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    die("Task not found.");
}

$task_title = $task['title'];
$board_id = $task['board_id'];

// Find 'Done' column
$stmt = $pdo->prepare("SELECT id FROM columns WHERE board_id = ? AND LOWER(name) = 'done' LIMIT 1");
$stmt->execute([$board_id]);
$done_column_id = $stmt->fetchColumn();

// Auto-create 'Done' column if it doesn't exist
if (!$done_column_id) {
    $stmt = $pdo->prepare("SELECT IFNULL(MAX(position), 0) + 1 FROM columns WHERE board_id = ?");
    $stmt->execute([$board_id]);
    $next_position = $stmt->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO columns (board_id, name, position) VALUES (?, 'Done', ?)");
    $stmt->execute([$board_id, $next_position]);

    $done_column_id = $pdo->lastInsertId();
}

// Move task to 'Done'
$stmt = $pdo->prepare("UPDATE tasks SET column_id = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$done_column_id, $task_id]);

// Notify collaborators
$stmt = $pdo->prepare("SELECT user_id FROM board_users WHERE board_id = ? AND user_id != ?");
$stmt->execute([$board_id, $current_user]);
$collaborators = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($collaborators as $user_id) {
    createNotification($user_id, 'task_moved', [
        'task_id' => $task_id,
        'board_id' => $board_id,
        'title' => $task_title,
        'column_id' => $done_column_id,
        'new_stage' => 'Done'
    ]);
}

// Redirect to board
redirect("../dashboard/boards/view.php?id=$board_id");
