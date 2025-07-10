<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

// Get board id to clone
$board_id = $_GET['id'] ?? null;
if (!$board_id) {
    die("Board ID missing");
}

// Check ownership
$stmt = $pdo->prepare("SELECT * FROM boards WHERE id = ? AND owner_id = ?");
$stmt->execute([$board_id, $_SESSION['user_id']]);
$board = $stmt->fetch();
if (!$board) {
    die("Board not found or access denied");
}

try {
    $pdo->beginTransaction();

    // Clone board (add " - Copy" suffix)
    $newBoardName = $board['name'] . " - Copy";
    $stmt = $pdo->prepare("INSERT INTO boards (owner_id, name, position) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $newBoardName, $board['position'] + 1]);
    $newBoardId = $pdo->lastInsertId();

    // Clone columns
    $stmt = $pdo->prepare("SELECT * FROM columns WHERE board_id = ?");
    $stmt->execute([$board_id]);
    $columns = $stmt->fetchAll();

    foreach ($columns as $column) {
        $stmtInsertCol = $pdo->prepare("INSERT INTO columns (board_id, name, position) VALUES (?, ?, ?)");
        $stmtInsertCol->execute([$newBoardId, $column['name'], $column['position']]);
        $newColumnId = $pdo->lastInsertId();

        // Clone tasks in column
        $stmtTasks = $pdo->prepare("SELECT * FROM tasks WHERE column_id = ?");
        $stmtTasks->execute([$column['id']]);
        $tasks = $stmtTasks->fetchAll();

        foreach ($tasks as $task) {
            $stmtInsertTask = $pdo->prepare("INSERT INTO tasks (column_id, title, description, priority, due_date, start_date, created_by, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsertTask->execute([
                $newColumnId,
                $task['title'],
                $task['description'],
                $task['priority'],
                $task['due_date'],
                $task['start_date'],
                $_SESSION['user_id'],
                $task['cover_image']
            ]);
        }
    }

    $pdo->commit();

    header("Location: ../dashboard/index.php?msg=Board cloned successfully");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Failed to clone board: " . $e->getMessage());
}
