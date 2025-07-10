<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$board_id = $_GET['id'] ?? null;
if (!$board_id) die("Board ID missing");

// Get existing shares
$stmt = $pdo->prepare("
  SELECT u.email, bu.role
  FROM board_users bu
  JOIN users u ON bu.user_id = u.id
  WHERE bu.board_id = ?
");
$stmt->execute([$board_id]);
$sharedUsers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Share Board</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Share Board</h1>
    <!-- ✅ Go back to Dashboard -->
    <a href="../dashboard/index.php" class="text-blue-600 underline">← Go Back to Dashboard</a>
  </div>

  <form method="POST" action="share_board_action.php" class="bg-white p-4 rounded shadow w-full max-w-lg">
    <input type="hidden" name="board_id" value="<?= $board_id ?>">
    
    <label for="email" class="block font-medium">Email of user to share with:</label>
    <input type="email" name="email" id="email" required class="w-full border p-2 rounded mb-3">
    
    <label for="role" class="block font-medium">Role:</label>
    <select name="role" id="role" class="w-full border p-2 rounded mb-3">
      <option value="viewer">Viewer</option>
      <option value="editor">Editor</option>
    </select>
    
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Share Board</button>
  </form>

  <div class="mt-6 max-w-lg">
    <h2 class="text-lg font-semibold mb-2">Currently Shared With:</h2>
    <ul class="space-y-2">
      <?php if (empty($sharedUsers)): ?>
        <li class="text-gray-500">No users have been added yet.</li>
      <?php else: ?>
        <?php foreach ($sharedUsers as $user): ?>
          <li class="bg-white p-3 rounded shadow">
            <?= htmlspecialchars($user['email']) ?> — <strong><?= ucfirst($user['role']) ?></strong>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</body>
</html>
