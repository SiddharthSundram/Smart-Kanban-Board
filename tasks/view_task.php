<?php
require_once '../config/config.php';
require_once '../auth/auth_middleware.php';

$taskId = $_GET['id'] ?? null;
if (!$taskId) die("Task ID missing");

// Fetch task with related column and board
$stmt = $pdo->prepare("
  SELECT t.*, c.name AS column_name, c.id AS column_id, b.id AS board_id
  FROM tasks t
  JOIN columns c ON t.column_id = c.id
  JOIN boards b ON c.board_id = b.id
  WHERE t.id = ?
");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) die("Task not found");

// Fetch users for dropdowns
$users = $pdo->query("SELECT id, full_name FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!--  Modal Wrapper -->
<div class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
  <div class="bg-white p-6 rounded shadow w-full max-w-screen-lg h-auto max-h-[90vh] overflow-y-auto relative">

    <!-- Close Button -->
    <button onclick="closeModal('taskModal')" class="absolute top-4 right-4 text-gray-400 hover:text-black text-2xl font-bold">&times;</button>

    <h2 class="text-2xl font-semibold mb-2">Edit Task</h2>
    <p class="text-sm text-gray-600 mb-4">📌 Column: <strong><?= htmlspecialchars($task['column_name']) ?></strong></p>

    <form method="POST" action="../../tasks/update_task.php" enctype="multipart/form-data" class="space-y-4" id="taskUpdateForm">
      <input type="hidden" name="task_id" value="<?= $task['id'] ?>">

      <div class="grid grid-cols-2 gap-4">
        <!-- Title -->
        <div class="col-span-2">
          <label class="block text-sm font-medium">Title</label>
          <input name="title" value="<?= htmlspecialchars($task['title']) ?>" class="w-full border p-2 rounded" required>
        </div>

        <!-- Description -->
        <div class="col-span-2">
          <label class="block text-sm font-medium">Description</label>
          <textarea name="description" rows="4" class="w-full border p-2 rounded"><?= htmlspecialchars($task['description']) ?></textarea>
        </div>

        <!-- Priority -->
        <div>
          <label class="block text-sm font-medium">Priority</label>
          <select name="priority" class="w-full border p-2 rounded">
            <?php foreach (['low', 'medium', 'high', 'urgent'] as $level): ?>
              <option value="<?= $level ?>" <?= $task['priority'] === $level ? 'selected' : '' ?>><?= ucfirst($level) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Label Color -->
        <div>
          <label class="block text-sm font-medium">Label Color</label>
          <select name="label_color" class="w-full border p-2 rounded">
            <?php foreach (['red', 'blue', 'green', 'yellow', 'purple', 'gray'] as $color): ?>
              <option value="<?= $color ?>" <?= $task['label_color'] === $color ? 'selected' : '' ?>><?= ucfirst($color) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Start/Due Date -->
        <div>
          <label class="block text-sm font-medium">Start Date</label>
          <input type="date" name="start_date" value="<?= $task['start_date'] ?>" class="w-full border p-2 rounded">
        </div>
        <div>
          <label class="block text-sm font-medium">Due Date</label>
          <input type="date" name="due_date" value="<?= $task['due_date'] ?>" class="w-full border p-2 rounded">
        </div>

        <!-- Assignees -->
        <div>
          <label class="block text-sm font-medium">Assignees</label>
          <select name="assignee_ids[]" multiple class="w-full border p-2 rounded">
            <?php foreach ($users as $user): ?>
              <option value="<?= $user['id'] ?>" <?= in_array($user['id'], explode(',', $task['assignee_ids'] ?? '')) ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['full_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Followers -->
        <div>
          <label class="block text-sm font-medium">Followers</label>
          <select name="follower_ids[]" multiple class="w-full border p-2 rounded">
            <?php foreach ($users as $user): ?>
              <option value="<?= $user['id'] ?>" <?= in_array($user['id'], explode(',', $task['follower_ids'] ?? '')) ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['full_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Cover Type -->
        <div>
          <label class="block text-sm font-medium">Cover Type</label>
          <select name="cover_type" class="w-full border p-2 rounded" onchange="toggleCoverInput(this.value)">
            <option value="color" <?= $task['cover_type'] === 'color' ? 'selected' : '' ?>>Color</option>
            <option value="image" <?= $task['cover_type'] === 'image' ? 'selected' : '' ?>>Image</option>
          </select>
        </div>

        <!-- Cover Value -->
        <div>
          <div id="coverColorInput" style="display: <?= $task['cover_type'] === 'color' ? 'block' : 'none' ?>">
            <label class="block text-sm font-medium">Cover Color</label>
            <input type="color" name="cover_value_color" value="<?= htmlspecialchars($task['cover_value'] ?? '#ffffff') ?>" class="w-full h-10 rounded border">
          </div>
          <div id="coverImageInput" style="display: <?= $task['cover_type'] === 'image' ? 'block' : 'none' ?>">
            <label class="block text-sm font-medium">Cover Image URL</label>
            <input type="text" name="cover_value_image" value="<?= htmlspecialchars($task['cover_value']) ?>" class="w-full border p-2 rounded">
          </div>
        </div>

        <!-- File Upload -->
        <div class="col-span-2">
          <label class="block text-sm font-medium">Attachment (Optional)</label>
          <input type="file" name="attachment" class="w-full border p-2 rounded">
        </div>

        <!-- Subtasks Placeholder -->
        <!-- <div class="col-span-2">
          <div class="bg-gray-100 text-sm p-3 rounded">✅ Subtasks will be shown here with progress bar (soon).</div>
        </div> -->
      </div>

      <!-- Buttons -->
      <div class="flex justify-between items-center mt-4">
        <button type="button"
          onclick="if(confirm('Delete this task?')) location.href='../../tasks/delete_task.php?id=<?= $task['id'] ?>'"
          class="bg-red-600 text-white px-4 py-2 rounded">
          Delete
        </button>
        <div class="flex gap-2">
          <button type="button" onclick="closeModal('taskModal')" class="px-4 py-2">Cancel</button>
          <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </div>
      </div>

      <!-- Column Movement Actions -->
      <div class="flex flex-wrap gap-2 justify-end mt-6">
        <?php if (strtolower($task['column_name']) !== 'done'): ?>
          <a href="../../tasks/mark_task_complete.php?id=<?= $task['id'] ?>" class="bg-green-600 text-white px-4 py-2 rounded">✅ Mark As Done</a>
        <?php endif; ?>
        <?php if (strtolower($task['column_name']) !== 'in progress'): ?>
          <a href="../../tasks/mark_task_in_progress.php?id=<?= $task['id'] ?>" class="bg-yellow-500 text-white px-4 py-2 rounded">⏩ In Progress</a>
        <?php endif; ?>
        <?php if (strtolower($task['column_name']) !== 'to do'): ?>
          <a href="../../tasks/mark_task_todo.php?id=<?= $task['id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded">⬅️ In To Do</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<script>
function toggleCoverInput(type) {
  document.getElementById('coverColorInput').style.display = type === 'color' ? 'block' : 'none';
  document.getElementById('coverImageInput').style.display = type === 'image' ? 'block' : 'none';
}
</script>
<script>
document.querySelector('#taskUpdateForm').addEventListener('submit', function (e) {
  e.preventDefault(); 

  const form = e.target;
  const formData = new FormData(form);

  fetch('../../tasks/update_task.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      alert('✅ Task updated successfully!');
      window.location.href = `../../dashboard/boards/view.php?id=<?= $task['board_id'] ?>`;
    } else {
      alert('❌ ' + data.message);
    }
  })
  .catch(err => {
    console.error(err);
    alert('⚠️ An error occurred.');
  });
});
</script>


