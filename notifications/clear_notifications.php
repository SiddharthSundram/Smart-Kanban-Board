<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

echo json_encode(['success' => true]);
