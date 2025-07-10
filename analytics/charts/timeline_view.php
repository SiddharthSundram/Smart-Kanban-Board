
<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/database.php';

$board_id = intval($_GET['board_id']);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT id, title, start_date, due_date
    FROM tasks
    WHERE column_id IN (
        SELECT id FROM columns WHERE board_id = ?
    )
    ORDER BY start_date ASC
");
$stmt->execute([$board_id]);
$data = $stmt->fetchAll();

json_response($data);
