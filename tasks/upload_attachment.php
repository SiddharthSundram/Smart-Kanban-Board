<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

if (!isset($_FILES['file']) || !isset($_POST['task_id'])) {
    json_response(['error' => 'Missing file or task ID'], 400);
}

$task_id = intval($_POST['task_id']);
$upload_dir = '../assets/uploads/';
$file_name = basename($_FILES['file']['name']);
$target_path = $upload_dir . time() . '_' . $file_name;

if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO attachments (task_id, file_name, file_path) VALUES (?, ?, ?)");
    $stmt->execute([$task_id, $file_name, $target_path]);

    json_response(['message' => 'File uploaded', 'file' => $file_name]);
} else {
    json_response(['error' => 'File upload failed'], 500);
}
