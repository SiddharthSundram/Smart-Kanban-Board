<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';
require_once '../utils/helpers.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['positions'])) {
        json_response(['error' => 'Missing positions array'], 400);
    }

    $positions = $input['positions'];

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE columns SET position = ? WHERE id = ?");

    foreach ($positions as $item) {
        $stmt->execute([$item['position'], $item['column_id']]);
    }

    json_response(['message' => 'Column positions updated']);
}
