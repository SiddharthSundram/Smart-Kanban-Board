<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    die("Task ID is missing");
}

// ✅ First get the board_id from the task's column
$stmt = $pdo->prepare("SELECT c.board_id FROM tasks t JOIN columns c ON t.column_id = c.id WHERE t.id = ?");
$stmt->execute([$task_id]);
$board = $stmt->fetch();

if (!$board) {
    die("Task or associated board not found");
}

$board_id = $board['board_id'];

// ✅ Now delete the task
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
if ($stmt->execute([$task_id])) {
    header("Location: ../dashboard/boards/view.php?id=" . $board_id); // ✅ Redirect to correct board
    exit;
} else {
    die("Failed to delete task");
}
