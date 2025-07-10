<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/database.php';

$board_id = intval($_GET['board_id']);
$db = Database::getInstance()->getConnection();

// Example: Get number of remaining tasks per day for past 14 days
$stmt = $db->prepare("
    SELECT DATE(created_at) AS day, COUNT(*) AS tasks_left
    FROM tasks
    WHERE column_id IN (
        SELECT id FROM columns WHERE board_id = ?
    )
    AND priority != 'completed'
    GROUP BY day
    ORDER BY day ASC
    LIMIT 14
");
$stmt->execute([$board_id]);
$data = $stmt->fetchAll();

json_response($data);
