<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/database.php';
require_once '../../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitize($_POST['name']);
  $visibility = $_POST['visibility'] ?? 'private';
  $user_id = $_SESSION['user_id'];

  $db = Database::getInstance()->getConnection();

  // 1. Create board
  $stmt = $db->prepare("INSERT INTO boards (name, visibility, owner_id) VALUES (?, ?, ?)");
  $stmt->execute([$name, $visibility, $user_id]);
  $board_id = $db->lastInsertId();

  // ✅ 2. Add owner as collaborator with role 'owner'
  $stmt = $db->prepare("INSERT INTO board_users (board_id, user_id, role) VALUES (?, ?, 'owner')");
  $stmt->execute([$board_id, $user_id]);

  // 3. Auto-create default columns
  $defaultColumns = ['To Do', 'In Progress', 'Done'];
  foreach ($defaultColumns as $position => $colName) {
    $stmt = $db->prepare("INSERT INTO columns (board_id, name, position) VALUES (?, ?, ?)");
    $stmt->execute([$board_id, $colName, $position]);
  }

  // 4. Redirect to dashboard
  redirect('../index.php');
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Create Board</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
  <form method="POST" class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
      📋 Create New Board
    </h2>

    <div class="mb-4">
      <label class="block mb-1 font-medium text-gray-700">Board Name</label>
      <input
        type="text"
        name="name"
        placeholder="e.g. Project Alpha"
        required
        class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

    <div class="mb-6">
      <label class="block mb-1 font-medium text-gray-700">Visibility</label>
      <select name="visibility" class="border p-2 rounded w-full" required>
        <option value="private">🔒 Private</option>
        <option value="public">🌐 Public</option>
        <option value="team">👥 Team Only</option>
      </select>

    </div>

    <div class="flex justify-end space-x-3">
      <a href="../index.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-gray-700 transition">Cancel</a>
      <button
        type="submit"
        class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
        ➕ Create
      </button>
    </div>
  </form>
</body>

</html>