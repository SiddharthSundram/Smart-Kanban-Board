<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notif_id'])) {
    $notif_id = intval($_POST['notif_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $success = $stmt->execute([$notif_id, $user_id]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
        http_response_code(500);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
http_response_code(400);
