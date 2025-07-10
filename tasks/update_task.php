<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    } else {
        die("Invalid request method.");
    }
    exit;
}

$task_id = $_POST['task_id'] ?? null;

// Fetch current task for comparison and board ID
$stmt = $pdo->prepare("
    SELECT t.*, c.board_id, c.name AS column_name
    FROM tasks t
    JOIN columns c ON t.column_id = c.id
    WHERE t.id = ?
");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    $msg = ['status' => 'error', 'message' => 'Task not found.'];
    echo $isAjax ? json_encode($msg) : die($msg['message']);
    exit;
}
$board_id = $task['board_id'];
$old_column_name = $task['column_name'];
$old_assignees = explode(',', $task['assignee_ids'] ?? '');

$title       = trim($_POST['title']);
$description = trim($_POST['description']);
$priority    = $_POST['priority'];
$start_date  = $_POST['start_date'] ?? null;
$due_date    = $_POST['due_date'] ?? null;
$label_color = $_POST['label_color'] ?? null;
$cover_type  = $_POST['cover_type'] ?? 'color';

$assignee_ids = isset($_POST['assignee_ids']) ? implode(',', $_POST['assignee_ids']) : null;
$follower_ids = isset($_POST['follower_ids']) ? implode(',', $_POST['follower_ids']) : null;

// Cover value
$cover_value = ($cover_type === 'color')
    ? ($_POST['cover_value_color'] ?? '#ffffff')
    : ($_POST['cover_value_image'] ?? null);

// File upload
$attachmentPath = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileTmp  = $_FILES['attachment']['tmp_name'];
    $fileName = basename($_FILES['attachment']['name']);
    $filePath = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($fileTmp, $filePath)) {
        $attachmentPath = $filePath;
    }
}

// Build update query
$sql = "UPDATE tasks SET 
    title = :title,
    description = :description,
    priority = :priority,
    start_date = :start_date,
    due_date = :due_date,
    assignee_ids = :assignee_ids,
    follower_ids = :follower_ids,
    label_color = :label_color,
    cover_type = :cover_type,
    cover_value = :cover_value";

if ($attachmentPath) {
    $sql .= ", cover_image = :cover_image";
}

$sql .= ", updated_at = NOW() WHERE id = :task_id";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':title', $title);
$stmt->bindValue(':description', $description);
$stmt->bindValue(':priority', $priority);
$stmt->bindValue(':start_date', $start_date ?: null);
$stmt->bindValue(':due_date', $due_date ?: null);
$stmt->bindValue(':assignee_ids', $assignee_ids);
$stmt->bindValue(':follower_ids', $follower_ids);
$stmt->bindValue(':label_color', $label_color);
$stmt->bindValue(':cover_type', $cover_type);
$stmt->bindValue(':cover_value', $cover_value);
$stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);

if ($attachmentPath) {
    $stmt->bindValue(':cover_image', $attachmentPath);
}

if ($stmt->execute()) {

    // 🔔 Notify newly assigned users
    $new_assignees = explode(',', $assignee_ids);
    $just_assigned = array_diff($new_assignees, $old_assignees);

    foreach ($just_assigned as $userId) {
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, data) VALUES (?, 'task_assigned', ?)");
        $notifStmt->execute([
            $userId,
            json_encode([
                'task_id' => $task_id,
                'board_id' => $board_id,
                'title' => $title
            ])
        ]);
    }

    // 🔄 Notify movement if column changed
    $stmtCol = $pdo->prepare("SELECT c.name FROM tasks t JOIN columns c ON t.column_id = c.id WHERE t.id = ?");
    $stmtCol->execute([$task_id]);
    $new_column = $stmtCol->fetchColumn();

    if ($new_column && $new_column !== $old_column_name) {
        foreach ($new_assignees as $userId) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, data) VALUES (?, 'task_moved', ?)");
            $notifStmt->execute([
                $userId,
                json_encode([
                    'task_id' => $task_id,
                    'board_id' => $board_id,
                    'title' => $title,
                    'to' => $new_column
                ])
            ]);
        }
    }

    if ($isAjax) {
        echo json_encode(['status' => 'success', 'message' => 'Task updated']);
    } else {
        header("Location: ../dashboard/boards/view.php?id=" . $board_id);
        exit;
    }

} else {
    $msg = ['status' => 'error', 'message' => 'Failed to update task.'];
    echo $isAjax ? json_encode($msg) : die($msg['message']);
}
