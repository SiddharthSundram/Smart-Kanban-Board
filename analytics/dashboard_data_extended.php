<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';
require_once '../utils/helpers.php';

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$board_id = $_GET['board_id'] ?? null;

// Helper to fetch JSON
function fetchAll($db, $sql, $params=[]) {
    $sth = $db->prepare($sql);
    $sth->execute($params);
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

// 1. Tasks per column
$tasksPerColumn = fetchAll($db, "
  SELECT c.name AS column, COUNT(t.id) AS count
  FROM columns c
  LEFT JOIN tasks t ON t.column_id = c.id
  WHERE c.board_id = ?
  GROUP BY c.id
", [$board_id]);

// 2. Task completion rate by board
$counts = fetchAll($db, "
  SELECT
    SUM(CASE WHEN priority='completed' THEN 1 ELSE 0 END) AS completed,
    COUNT(*) AS total
  FROM tasks t
  JOIN columns c ON t.column_id = c.id
  WHERE c.board_id = ?
", [$board_id])[0];

// 3. Burndown data: daily remaining tasks from project start to today
$start = date('Y-m-d', strtotime('-14 days'));
$dates = fetchAll($db, "SELECT DISTINCT DATE(created_at) AS dt FROM tasks WHERE created_at >= ?", [$start]);
$burndown = [];
$remaining = intval($counts['total']);
foreach ($dates as $row) {
  $date = $row['dt'];
  $doneThatDay = fetchAll($db, "
    SELECT COUNT(*) AS c FROM tasks t
    JOIN columns c2 ON t.column_id=c2.id
    WHERE c2.board_id = ? AND DATE(t.created_at)=? AND t.priority='completed'
  ", [$board_id, $date])[0]['c'];
  $remaining -= $doneThatDay;
  $burndown[] = ['date'=>$date, 'remaining'=>$remaining];
}

// 4. Timeline view: each task start–due
$timeline = fetchAll($db, "
  SELECT t.title, COALESCE(t.start_date, t.created_at) AS start, t.due_date AS end
  FROM tasks t
  JOIN columns c ON t.column_id=c.id
  WHERE c.board_id = ?
", [$board_id]);

json_response(compact('tasksPerColumn','counts','burndown','timeline'));
