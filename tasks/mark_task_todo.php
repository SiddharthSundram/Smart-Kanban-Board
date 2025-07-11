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

// Fetch task's board and column
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

// Find 'To Do' column in the board
$stmt = $pdo->prepare("SELECT id FROM columns WHERE board_id = ? AND LOWER(name) = 'to do' LIMIT 1");
$stmt->execute([$board_id]);
$todo_column_id = $stmt->fetchColumn();

if (!$todo_column_id) {
    die("No 'To Do' column found in this board.");
}

//  Move task to 'To Do' column
$stmt = $pdo->prepare("UPDATE tasks SET column_id = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$todo_column_id, $task_id]);

// Notify collaborators (excluding self)
$stmt = $pdo->prepare("SELECT user_id FROM board_users WHERE board_id = ? AND user_id != ?");
$stmt->execute([$board_id, $current_user]);
$collaborators = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($collaborators as $user_id) {
    createNotification($user_id, 'task_moved', [
        'task_id' => $task_id,
        'board_id' => $board_id,
        'title' => $task_title,
        'column_id' => $todo_column_id,
        'new_stage' => 'To Do'
    ]);
}

//  Redirect to board view
redirect("../dashboard/boards/view.php?id=$board_id");
