<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $column_id = intval($_POST['column_id']);
    $name = sanitize($_POST['name']);
    $color = sanitize($_POST['color']);

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE columns SET name = ?, color = ? WHERE id = ?");
    $stmt->execute([$name, $color, $column_id]);

    json_response(['message' => 'Column updated']);
}

