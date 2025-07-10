<?php
require_once 'auth_middleware.php';
require_once '../config/config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $bio = $_POST['bio'] ?? '';

    $avatar = null;
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $targetDir = '../assets/images/avatars/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatar = 'assets/images/avatars/' . $fileName;
        }
    }

    $sql = "UPDATE users SET full_name = ?, bio = ?" . ($avatar ? ", avatar = ?" : "") . " WHERE id = ?";
    $params = $avatar ? [$full_name, $bio, $avatar, $user_id] : [$full_name, $bio, $user_id];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $message = "Profile updated successfully.";
}

$stmt = $pdo->prepare("SELECT full_name, bio, avatar, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 min-h-screen p-6">

  <!-- Location indicator -->
  <div class="max-w-6xl mx-auto mb-6">
    <p class="text-sm text-gray-500"><span class="text-gray-800 text-3xl font-semibold">Profile</span></p>
  </div>

  <!-- Profile Card -->
  <div class="max-w-xl mx-auto bg-white shadow-md rounded-lg p-6 relative">
    <?php if (isset($message)): ?>
      <p class="text-green-600 mb-4 font-medium"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="flex items-center space-x-4">
      <img src="../<?= htmlspecialchars($user['avatar'] ?? 'assets/images/avatars/default.png') ?>" alt="Avatar" class="w-20 h-20 rounded-full object-cover border" />
      <div>
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['full_name']) ?></h2>
        <p class="text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
      </div>
    </div>

    <div class="mt-4">
      <h3 class="text-lg font-semibold">Bio</h3>
      <p class="text-gray-700"><?= nl2br(htmlspecialchars($user['bio'])) ?: 'No bio provided.' ?></p>
    </div>

    <div class="mt-6 flex justify-between">
      <button onclick="openModal('editProfileModal')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit Profile</button>
      <a href="../dashboard/index.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">← Dashboard</a>
    </div>
  </div>

  <!-- Edit Profile Modal -->
  <div id="editProfileModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">Edit Profile</h2>
      <form method="POST" enctype="multipart/form-data">
        <label class="block mb-4">
          Full Name:
          <input type="text" name="full_name" required value="<?= htmlspecialchars($user['full_name']) ?>" class="w-full border p-2 rounded" />
        </label>
        <label class="block mb-4">
          Bio:
          <textarea name="bio" class="w-full border p-2 rounded"><?= htmlspecialchars($user['bio']) ?></textarea>
        </label>
        <label class="block mb-4">
          Avatar:
          <input type="file" name="avatar" accept="image/*" />
        </label>
        <div class="text-right space-x-2">
          <button type="button" onclick="closeModal('editProfileModal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal(id) {
      document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }
  </script>
</body>
</html>
