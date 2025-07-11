<?php


require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$order = $data['order'] ?? [];

if (!is_array($order)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

foreach ($order as $position => $board_id) {
    $stmt = $pdo->prepare("UPDATE boards SET position = ? WHERE id = ? AND owner_id = ?");
    $stmt->execute([$position, $board_id, $_SESSION['user_id']]);
}

echo json_encode(['status' => 'success']);
