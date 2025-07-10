<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$column_id = $_GET['id'] ?? null;
$board_id = $_GET['board_id'] ?? null;

if (!$column_id || !$board_id) {
    die("Missing parameters.");
}

// Set is_archived = 0
$stmt = $pdo->prepare("UPDATE columns SET is_archived = 0 WHERE id = ?");
$stmt->execute([$column_id]);

header("Location: manage_archived.php?board_id=$board_id");
exit;
