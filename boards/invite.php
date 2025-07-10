<?php
require_once '../auth/auth_middleware.php';
require_once '../config/database.php';
require_once '../utils/helpers.php';

$db = Database::getInstance()->getConnection();
$current_user_id = $_SESSION['user_id'];

$board_id = $_GET['id'] ?? null;
if (!$board_id) {
    die("Board ID missing.");
}

// Check if current user is owner
$stmt = $db->prepare("SELECT * FROM boards WHERE id = ? AND owner_id = ?");
$stmt->execute([$board_id, $current_user_id]);
$board = $stmt->fetch();

if (!$board) {
    die("You are not the owner of this board or it doesn't exist.");
}

// Handle invite form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // Find user by email
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = "No user found with that email.";
    } else {
        $invitee_id = $user['id'];

        // Check if already added
        $stmt = $db->prepare("SELECT * FROM board_users WHERE board_id = ? AND user_id = ?");
        $stmt->execute([$board_id, $invitee_id]);
        if ($stmt->fetch()) {
            $error = "User already invited.";
        } else {
            // Add user to board
            $stmt = $db->prepare("INSERT INTO board_users (board_id, user_id, role) VALUES (?, ?, ?)");
            $stmt->execute([$board_id, $invitee_id, $role]);
            $success = "User invited successfully.";
        }
    }
}

// Get existing collaborators
$stmt = $db->prepare("
    SELECT u.full_name, u.email, bu.role 
    FROM board_users bu
    JOIN users u ON bu.user_id = u.id
    WHERE bu.board_id = ?
");
$stmt->execute([$board_id]);
$collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invite Users</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="max-w-3xl mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">👥 Invite Collaborators to: <?= htmlspecialchars($board['name']) ?></h1>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= $error ?></div>
    <?php elseif (!empty($success)): ?>
      <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4 mb-6">
      <div>
        <label class="block font-medium mb-1">User Email</label>
        <input type="email" name="email" required placeholder="user@example.com" class="w-full border px-3 py-2 rounded">
      </div>

      <div>
        <label class="block font-medium mb-1">Role</label>
        <select name="role" class="w-full border px-3 py-2 rounded" required>
          <option value="editor">✏️ Editor</option>
          <option value="viewer">👀 Viewer</option>
        </select>
      </div>

      <div class="text-right">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Invite</button>
      </div>
    </form>

    <h2 class="text-xl font-semibold mb-2">🧑‍🤝‍🧑 Collaborators</h2>
    <table class="w-full border text-left">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border">Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($collaborators as $collab): ?>
          <tr>
            <td class="p-2 border"><?= htmlspecialchars($collab['full_name']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($collab['email']) ?></td>
            <td class="p-2 border capitalize"><?= $collab['role'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="mt-6">
      <a href="../dashboard/index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
