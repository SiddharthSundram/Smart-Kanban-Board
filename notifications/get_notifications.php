<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY timestamp DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifications);
