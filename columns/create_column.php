<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_id = $_POST['board_id'];
    $name = sanitize($_POST['name']);
    $color = $_POST['color'] ?? '#ffffff'; 
    $wip_limit = isset($_POST['wip_limit']) && is_numeric($_POST['wip_limit']) ? intval($_POST['wip_limit']) : null;

    // Get next position
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) + 1 AS new_pos FROM columns WHERE board_id = ?");
    $stmt->execute([$board_id]);
    $position = $stmt->fetchColumn();

    // ✅ Insert with color
    $stmt = $pdo->prepare("INSERT INTO columns (board_id, name, position, wip_limit, color) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$board_id, $name, $position, $wip_limit, $color]);

    redirect('../dashboard/boards/view.php?id=' . $board_id);
} else {
    die("Invalid request");
}
