<?php
require_once '../auth/auth_middleware.php';
require_once '../utils/helpers.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Total tasks assigned to user
$stmt = $db->prepare("SELECT COUNT(*) FROM task_assignees WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_tasks = $stmt->fetchColumn();

// Overdue tasks
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM tasks t 
    JOIN task_assignees ta ON t.id = ta.task_id 
    WHERE ta.user_id = ? AND due_date < CURDATE() AND priority != 'completed'
");
$stmt->execute([$user_id]);
$overdue = $stmt->fetchColumn();

// Completed tasks
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM tasks t 
    JOIN task_assignees ta ON t.id = ta.task_id 
    WHERE ta.user_id = ? AND priority = 'completed'
");
$stmt->execute([$user_id]);
$completed = $stmt->fetchColumn();

// Timeline view (tasks created per day)
$stmt = $db->prepare("
    SELECT DATE(t.created_at) as date, COUNT(*) as count 
    FROM tasks t 
    JOIN task_assignees ta ON t.id = ta.task_id 
    WHERE ta.user_id = ? 
    GROUP BY DATE(t.created_at) 
    ORDER BY date ASC
");
$stmt->execute([$user_id]);
$timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tasks per column
$stmt = $db->prepare("
    SELECT c.name, COUNT(*) as count 
    FROM tasks t 
    JOIN columns c ON t.column_id = c.id 
    JOIN task_assignees ta ON t.id = ta.task_id 
    WHERE ta.user_id = ? 
    GROUP BY c.name
");
$stmt->execute([$user_id]);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Burndown chart (simulate ideal and actual)
$burndown = [];
$days = 7;
$daily_task = $total_tasks / $days;

for ($i = 0; $i < $days; $i++) {
    $date = date('Y-m-d', strtotime("-" . (6 - $i) . " days"));

    // Ideal burn
    $ideal = round($total_tasks - $daily_task * $i);

    // Actual: count of pending tasks as of this date
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM tasks t 
        JOIN task_assignees ta ON t.id = ta.task_id 
        WHERE ta.user_id = ? 
        AND DATE(t.created_at) <= ? 
        AND priority != 'completed'
    ");
    $stmt->execute([$user_id, $date]);
    $actual = $stmt->fetchColumn();

    $burndown[] = [
        'date' => $date,
        'ideal' => $ideal,
        'actual' => (int) $actual
    ];
}

// ✅ Final JSON response
json_response([
    'total_tasks' => (int)$total_tasks,
    'overdue_tasks' => (int)$overdue,
    'completed_tasks' => (int)$completed,
    'timeline' => $timeline,
    'columns' => $columns,
    'burndown' => $burndown
]);
