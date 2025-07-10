<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $column_id = $_POST['column_id'];
    $board_id = $_POST['board_id'];
    $name = sanitize($_POST['name']);
    $wip_limit = isset($_POST['wip_limit']) && $_POST['wip_limit'] !== '' ? intval($_POST['wip_limit']) : null;
    $color = $_POST['color'] ?? '#ffffff';

    $stmt = $pdo->prepare("UPDATE columns SET name = ?, wip_limit = ?, color = ? WHERE id = ?");
    $stmt->execute([$name, $wip_limit, $color, $column_id]);

    redirect("../dashboard/boards/view.php?id=" . $board_id);
} else {
    die("Invalid request");
}
