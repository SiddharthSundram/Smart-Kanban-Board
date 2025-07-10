<?php
require_once '../config/config.php';

$board_id = $_GET['id'] ?? null;
if (!$board_id) exit("Board ID missing");

$stmt = $pdo->prepare("DELETE FROM boards WHERE id = ?");
$stmt->execute([$board_id]);

header("Location: ../dashboard/index.php");
exit;
