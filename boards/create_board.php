<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';
require_once '../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $visibility = $_POST['visibility'];
    $owner_id = $_SESSION['user_id'];

    $db = Database::getInstance()->getConnection();

    // 1. Create Board
    $stmt = $db->prepare("INSERT INTO boards (name, visibility, owner_id) VALUES (?, ?, ?)");
    $stmt->execute([$name, $visibility, $owner_id]);
    $board_id = $db->lastInsertId();

    // 2. Add default columns with color and is_archived set
    $defaultColumns = ['To Do', 'In Progress', 'Testing', 'Done'];
    $position = 1;
    $defaultColor = '#ffffff'; // white background
    $defaultArchived = 0; // not archived
    $defaultWip = null;

    $stmt = $db->prepare("INSERT INTO columns (board_id, name, position, color, is_archived, wip_limit) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($defaultColumns as $colName) {
        $stmt->execute([$board_id, $colName, $position++, $defaultColor, $defaultArchived, $defaultWip]);
    }

    json_response(['message' => 'Board created successfully with default columns', 'board_id' => $board_id]);
}
