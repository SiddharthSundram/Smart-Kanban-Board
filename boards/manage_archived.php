<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM boards WHERE owner_id = ? AND is_archived = 1");
$stmt->execute([$user_id]);
$boards = $stmt->fetchAll();
?>

<h2 class="text-xl font-bold mb-4">🗃️ Archived Boards</h2>
<ul>
  <?php foreach ($boards as $board): ?>
    <li class="mb-2 flex justify-between">
      <span><?= htmlspecialchars($board['name']) ?></span>
      <a href="unarchive_board.php?id=<?= $board['id'] ?>" 
         class="text-green-600 hover:underline">♻️ Restore</a>
    </li>
  <?php endforeach; ?>
</ul>
<a href="../dashboard/index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
