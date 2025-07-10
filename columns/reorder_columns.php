<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $positions = $_POST['positions']; // Expects [{ "column_id": 1, "position": 0 }, ...]

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE columns SET position = ? WHERE id = ?");

    foreach ($positions as $item) {
        $stmt->execute([$item['position'], $item['column_id']]);
    }

    json_response(['message' => 'Column positions updated']);
}



