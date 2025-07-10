<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$board_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$board_id) die("Board ID missing");

// Ensure user is owner
$stmt = $pdo->prepare("SELECT * FROM boards WHERE id = ? AND owner_id = ?");
$stmt->execute([$board_id, $user_id]);
$board = $stmt->fetch();

if (!$board) die("Unauthorized or board not found");

// Archive it
$stmt = $pdo->prepare("UPDATE boards SET is_archived = 1 WHERE id = ?");
$stmt->execute([$board_id]);

header("Location: ../dashboard/index.php?archived=1");
exit;
