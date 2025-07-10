<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $column_id = intval($_POST['column_id']);
    $wip_limit = intval($_POST['wip_limit']);

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE columns SET wip_limit = ? WHERE id = ?");
    $stmt->execute([$wip_limit, $column_id]);

    json_response(['message' => 'WIP limit updated']);
}
