<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';

$board_id = $_GET['board_id'] ?? null;
if (!$board_id) die("Missing board ID");

// Fetch archived columns
$stmt = $pdo->prepare("SELECT * FROM columns WHERE board_id = ? AND is_archived = 1 ORDER BY position");
$stmt->execute([$board_id]);
$archivedColumns = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Archived Columns</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">🗃️ Archived Columns</h2>

    <?php if (empty($archivedColumns)): ?>
      <p class="text-gray-600">No archived columns found for this board.</p>
    <?php else: ?>
      <ul class="space-y-4">
        <?php foreach ($archivedColumns as $col): ?>
          <li class="flex justify-between items-center bg-gray-50 p-3 rounded">
            <span><?= htmlspecialchars($col['name']) ?></span>
            <a href="unarchive_column.php?id=<?= $col['id'] ?>&board_id=<?= $board_id ?>" 
               class="text-green-600 hover:underline"
               onclick="return confirm('Restore this column?')">
               🔄 Restore
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <div class="mt-6">
      <a href="../dashboard/boards/view.php?id=<?= $board_id ?>" class="text-blue-600 hover:underline">← Back to Board</a>
    </div>
  </div>
</body>
</html>
