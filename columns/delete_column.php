<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$column_id = $_GET['id'] ?? null;
$board_id = $_GET['board_id'] ?? null;

if (!$column_id) {
    die('Column ID missing');
}

// Delete tasks in column (optional, or cascade delete in DB)
$stmt = $pdo->prepare("DELETE FROM tasks WHERE column_id = ?");
$stmt->execute([$column_id]);

// Delete the column
$stmt = $pdo->prepare("DELETE FROM columns WHERE id = ?");
$stmt->execute([$column_id]);

header("Location: ../dashboard/boards/view.php?id=" . ($board_id ?? ''));
exit;
