<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();
$board_id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM boards WHERE id = ?");
$stmt->execute([$board_id]);
$board = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $visibility = $_POST['visibility'];

    $stmt = $db->prepare("UPDATE boards SET name = ?, visibility = ? WHERE id = ?");
    $stmt->execute([$name, $visibility, $board_id]);

    redirect('../index.php');
}
?>

<form method="POST" class="p-6 bg-white shadow rounded max-w-md mx-auto mt-10">
    <h2 class="text-xl font-bold mb-4">Edit Board</h2>
    <input type="text" name="name" value="<?= $board['name'] ?>" required class="w-full border p-2 mb-3">
    <select name="visibility" class="w-full border p-2 mb-3">
        <option value="private" <?= $board['visibility'] === 'private' ? 'selected' : '' ?>>Private</option>
        <option value="public" <?= $board['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
    </select>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
</form>
d