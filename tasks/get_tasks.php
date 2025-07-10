<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

$board_id = intval($_GET['board_id']);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT t.*, c.name AS column_name
    FROM tasks t
    JOIN columns c ON t.column_id = c.id
    WHERE c.board_id = ?
    ORDER BY t.due_date ASC
");
$stmt->execute([$board_id]);
$tasks = $stmt->fetchAll();

json_response($tasks);
