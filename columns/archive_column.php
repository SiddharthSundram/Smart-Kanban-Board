<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';
require_once '../utils/helpers.php';

$column_id = $_GET['id'] ?? null;
$board_id = $_GET['board_id'] ?? null;

if (!$column_id || !$board_id) {
    die("Invalid request");
}

// Archive the column (set a flag)
$stmt = $pdo->prepare("UPDATE columns SET is_archived = 1 WHERE id = ?");
$stmt->execute([$column_id]);

redirect("../dashboard/boards/view.php?id=" . $board_id);
