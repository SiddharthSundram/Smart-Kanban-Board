<?php
require_once '../config/config.php';
require_once 'auth_middleware.php';

// Usage: include this in any board-related page with ?id= in URL
$board_id = $_GET['id'] ?? $_POST['board_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$board_id) {
  die("Board ID is missing");
}

// Get board details
$stmt = $pdo->prepare("SELECT * FROM boards WHERE id = ?");
$stmt->execute([$board_id]);
$board = $stmt->fetch();

if (!$board) {
  die("Board not found");
}

// Check if user is allowed
$is_owner = $board['owner_id'] == $user_id;
$is_collaborator = false;

$stmt = $pdo->prepare("SELECT role FROM board_users WHERE board_id = ? AND user_id = ?");
$stmt->execute([$board_id, $user_id]);
$collab = $stmt->fetch();
if ($collab) $is_collaborator = true;

$visibility = $board['visibility'];

$access_granted = false;

if ($visibility === 'public') {
  $access_granted = true;
} elseif ($visibility === 'team' && $is_collaborator) {
  $access_granted = true;
} elseif ($visibility === 'private' && ($is_owner || $is_collaborator)) {
  $access_granted = true;
}

if (!$access_granted) {
  die("Access denied to this board.");
}

// Optional: store role for later use in page
$board_role = $collab['role'] ?? ($is_owner ? 'Owner' : null);
?>
