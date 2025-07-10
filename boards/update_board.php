<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_id = $_POST['board_id'];
    $name = trim($_POST['name']);
    $visibility = $_POST['visibility'] ?? 'private';

    $stmt = $pdo->prepare("UPDATE boards SET name = ?, visibility = ? WHERE id = ?");
    $stmt->execute([$name, $visibility, $board_id]);

    header("Location: ../dashboard/index.php");
    exit;
}
