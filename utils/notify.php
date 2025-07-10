<?php
require_once '../config/database.php';

function createNotification($userId, $type, $data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, data, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userId, $type, json_encode($data)]);
}

function markNotificationAsRead($notif_id, $user_id) {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
}

