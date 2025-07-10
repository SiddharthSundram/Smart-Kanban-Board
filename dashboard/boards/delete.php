<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/database.php';

$board_id = $_GET['id'];
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("DELETE FROM boards WHERE id = ?");
$stmt->execute([$board_id]);

redirect('../index.php');
