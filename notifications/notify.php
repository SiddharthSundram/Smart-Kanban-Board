<?php
require_once '../config/config.php';

function createNotification($userId, $type, $dataArray) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, data) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $type, json_encode($dataArray)]);
}
