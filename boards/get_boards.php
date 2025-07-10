<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Fetch owned or shared boards
$stmt = $db->prepare("
    SELECT b.*
    FROM boards b
    LEFT JOIN board_users bu ON b.id = bu.board_id
    WHERE b.owner_id = ? OR bu.user_id = ?
    GROUP BY b.id
");
$stmt->execute([$user_id, $user_id]);
$boards = $stmt->fetchAll();

json_response($boards);
