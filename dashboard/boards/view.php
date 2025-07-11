<?php
require_once '../../auth/auth_middleware.php';
require_once '../../config/config.php';
require_once '../../utils/helpers.php';
// require_once '../../auth/board_access.php'; // Enforces visibility/role

$board_id = $_GET['id'] ?? null;
if (!$board_id) die("Invalid board");

// Get board name
$stmt = $pdo->prepare("SELECT name, owner_id FROM boards WHERE id = ?");

$stmt->execute([$board_id]);
$board = $stmt->fetch();
if (!$board) die("Board not found");
$isOwner = ($_SESSION['user_id'] == $board['owner_id']);

// Get columns
// $stmt = $pdo->prepare("SELECT * FROM columns WHERE board_id = ? ORDER BY position");
$stmt = $pdo->prepare("SELECT * FROM columns WHERE board_id = ? AND is_archived = 0 ORDER BY position");

$stmt->execute([$board_id]);
$columns = $stmt->fetchAll();

// Get tasks grouped by column
$stmt = $pdo->prepare("SELECT tasks.*, columns.id as col_id FROM tasks JOIN columns ON tasks.column_id = columns.id WHERE columns.board_id = ?");
$stmt->execute([$board_id]);
$tasks = $stmt->fetchAll();

$tasksByColumn = [];
foreach ($tasks as $task) {
  $tasksByColumn[$task['col_id']][] = $task;
}

$highlightTask = $_GET['highlight_task'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($board['name']) ?> - Board</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <style>
    @keyframes shake {

      0%,
      100% {
        transform: translateX(0);
      }

      20%,
      60% {
        transform: translateX(-10px);
      }

      40%,
      80% {
        transform: translateX(10px);
      }
    }

    .shake {
      animation: shake 0.4s;
    }

    .ring-2 {
      animation: pulseHighlight 1.2s ease-out;
    }

    @keyframes pulseHighlight {
      0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
      }

      100% {
        box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
      }
    }
  </style>
</head>

<body class="p-6 bg-gray-100">

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold"><?= htmlspecialchars($board['name']) ?> - Kanban Board</h1>

    <div class="space-x-2">
      <!-- Back to dashboard  -->
      <a href="../../dashboard/index.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">← Dashboard</a>

      <!-- Edit Board Button -->
      <a href="../../columns/manage_archived.php?board_id=<?= $board_id ?>"
        class="bg-pink-600 text-white px-4 py-2 rounded hover:bg-pink-800">
        🗃️ View Archived Columns
      </a>

      <!-- Invite Button -->
      <?php if ($isOwner): ?>
        <a href="../../boards/invite.php?id=<?= $board_id ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
          ➕ Invite
        </a>
      <?php endif; ?>
      <!-- Edit Board Button -->
      <button onclick="openModal('addTaskModal')" class="bg-blue-600 text-white px-4 py-2 rounded">+ Add Task</button>
      <!-- Add Column Button -->
      <button onclick="openModal('addColumnModal')" class="bg-green-600 text-white px-4 py-2 rounded">+ Add Column</button>
    </div>
  </div>

  <!-- Column Container -->
  <div id="column-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

    <?php foreach ($columns as $column): ?>
      <?php $colTaskCount = count($tasksByColumn[$column['id']] ?? []); ?>
      <div class="p-3 rounded shadow flex flex-col relative" data-column-id="<?= $column['id'] ?>" style="background-color: <?= htmlspecialchars($column['color']) ?>;">

          <h2 class="font-semibold mb-2 text-lg flex justify-between items-center">
            <span>
              <span class="cursor-move column-handle" title="Drag Column">⠿</span>
              <?= htmlspecialchars($column['name']) ?>
              <?php if ($column['wip_limit']): ?>
                <span class="text-sm <?= ($colTaskCount >= $column['wip_limit']) ? 'text-red-600' : 'text-gray-600' ?> ml-1">(<?= $colTaskCount ?>/<?= $column['wip_limit'] ?>)</span>
              <?php endif; ?>
            </span>
            <span class="space-x-2">
              <button onclick="openEditColumnModal(<?= $column['id'] ?>, '<?= htmlspecialchars($column['name'], ENT_QUOTES) ?>', <?= $column['wip_limit'] ?? 'null' ?>)" class="text-blue-600 hover:text-blue-800" title="Edit Column">✎</button>
              <button onclick="if(confirm('Delete this column and its tasks?')) location.href='../../columns/delete_column.php?id=<?= $column['id'] ?>&board_id=<?= $board_id ?>'" class="text-red-600 hover:text-red-800" title="Delete Column">✕</button>

              <!-- ✅ Archive button added below -->
              <a href='../../columns/archive_column.php?id=<?= $column['id'] ?>&board_id=<?= $board_id ?>'
                class="text-yellow-500 hover:text-yellow-700"
                title="Archive Column"
                onclick="return confirm('Are you sure you want to archive this column?');">
                🗃️
              </a>
            </span>
          </h2>

          <div id="column-<?= $column['id'] ?>" class="task-column flex-1 min-h-[100px] bg-gray-100 p-2 rounded space-y-2 <?= ($colTaskCount >= $column['wip_limit'] && $column['wip_limit']) ? 'border-2 border-red-600' : '' ?>" data-column-id="<?= $column['id'] ?>" title="<?= ($colTaskCount >= $column['wip_limit'] && $column['wip_limit']) ? "WIP limit reached: $colTaskCount / {$column['wip_limit']}" : '' ?>">
            <?php if (!empty($tasksByColumn[$column['id']])): ?>
              <?php foreach ($tasksByColumn[$column['id']] as $task): ?>
                <div onclick="openTaskModal(<?= $task['id'] ?>)"
                  class="task-card p-3 bg-white rounded shadow cursor-pointer <?= ($highlightTask == $task['id']) ? 'ring-2 ring-blue-500' : '' ?>"
                  data-task-id="<?= $task['id'] ?>">
                  <h3 class="font-medium"><?= htmlspecialchars($task['title']) ?></h3>
                  <p class="text-sm text-gray-500">Priority: <?= htmlspecialchars($task['priority']) ?></p>
                  <p class="text-sm text-gray-400">Due: <?= $task['due_date'] ?></p>
                </div>

              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      </div>

      <!-- Add Task Modal -->
      <div id="addTaskModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow w-96">
          <h2 class="text-xl font-bold mb-4">Add Task</h2>
          <form method="POST" action="../../tasks/create_task.php">
            <input type="hidden" name="board_id" value="<?= $board_id ?>">
            <input name="title" placeholder="Title" class="w-full border p-2 rounded mb-2" required>
            <textarea name="description" placeholder="Description" class="w-full border p-2 rounded mb-2"></textarea>
            <select name="priority" class="w-full border p-2 rounded mb-2">
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
            <label>Due Date:</label>
            <input type="date" name="due_date" class="w-full border p-2 rounded mb-2">
            <label>Column:</label>
            <select name="column_id" class="w-full border p-2 rounded mb-4">
              <?php foreach ($columns as $col): ?>
                <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="text-right">
              <button type="button" onclick="closeModal('addTaskModal')" class="mr-2 px-4 py-2">Cancel</button>
              <button class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Column Modal -->
      <div id="addColumnModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow w-96">
          <h2 class="text-xl font-bold mb-4">Add Column</h2>
          <form method="POST" action="../../columns/create_column.php">
            <input type="hidden" name="board_id" value="<?= $board_id ?>">

            <label class="block mb-1 font-medium text-gray-700">Column Name</label>
            <input name="name" placeholder="Column Name" class="w-full border p-2 rounded mb-3" required>

            <label class="block mb-1 font-medium text-gray-700">WIP Limit (optional)</label>
            <input name="wip_limit" type="number" min="0" placeholder="WIP Limit" class="w-full border p-2 rounded mb-3">

            <label class="block mb-1 font-medium text-gray-700">Column Color</label>
            <input name="color" type="color" value="#ffffff" class="w-full border p-2 rounded mb-4">

            <div class="text-right">
              <button type="button" onclick="closeModal('addColumnModal')" class="mr-2 px-4 py-2">Cancel</button>
              <button class="bg-green-600 text-white px-4 py-2 rounded">Create</button>
            </div>
          </form>
        </div>
      </div>


      <!-- Edit Column Modal -->
      <div id="editColumnModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div class="bg-white p-6 rounded shadow w-96">
          <h2 class="text-xl font-bold mb-4">Edit Column</h2>
          <form method="POST" action="../../columns/edit_column.php">
            <input type="hidden" name="column_id" id="edit-column-id">
            <input type="hidden" name="board_id" value="<?= $board_id ?>">

            <label class="block font-medium text-gray-700 mb-1">Column Name</label>
            <input name="name" id="edit-column-name" placeholder="Column Name" class="w-full border p-2 rounded mb-2" required />

            <label class="block font-medium text-gray-700 mb-1">WIP Limit</label>
            <input name="wip_limit" id="edit-column-wip" type="number" min="0" placeholder="WIP Limit (optional)" class="w-full border p-2 rounded mb-2">

            <label class="block font-medium text-gray-700 mb-1">Column Color</label>
            <input name="color" id="edit-column-color" type="color" class="w-full border p-2 rounded mb-4" value="#ffffff">

            <div class="text-right">
              <button type="button" onclick="closeModal('editColumnModal')" class="mr-2 px-4 py-2">Cancel</button>
              <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
          </form>
        </div>
      </div>


      <!-- Task Modal -->
      <div id="taskModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div id="modalContent" class="w-full max-w-xl bg-white rounded shadow p-6"></div>
      </div>

      <script>
        function openTaskModal(taskId) {
          fetch(`../../tasks/view_task.php?id=${taskId}`)
            .then(res => res.text())
            .then(html => {
              document.getElementById('modalContent').innerHTML = html;
              openModal('taskModal');
            });
        }

        function openModal(id) {
          document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
          document.getElementById(id).classList.add('hidden');
        }

        function openEditColumnModal(id, name, wipLimit, color = '#ffffff') {
          document.getElementById('edit-column-id').value = id;
          document.getElementById('edit-column-name').value = name;
          document.getElementById('edit-column-wip').value = wipLimit ?? '';
          document.getElementById('edit-column-color').value = color;
          openModal('editColumnModal');
        }


        document.querySelectorAll('.task-column').forEach(column => {
          new Sortable(column, {
            group: 'tasks',
            animation: 150,

            onEnd: async function(evt) {
              const taskId = evt.item.dataset.taskId;
              const newColumn = evt.to;
              const newColumnId = newColumn.dataset.columnId;
              const taskCards = newColumn.querySelectorAll('.task-card');
              const newTaskCount = taskCards.length;

              try {
                const res = await fetch(`../../columns/get_wip_limit.php?column_id=${newColumnId}`);
                const data = await res.json();
                const wipLimit = parseInt(data.wip_limit);

                if (wipLimit && newTaskCount > wipLimit) {
                  alert(`⚠️ WIP limit exceeded (${newTaskCount}/${wipLimit}). Task not moved.`);
                  evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]); // move task back

                  // Shake animation on the column to indicate rejection
                  newColumn.classList.add('shake');
                  setTimeout(() => newColumn.classList.remove('shake'), 400);

                  return;
                }

                // Move the task in DB
                await fetch('../../tasks/move_task.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                    task_id: taskId,
                    new_column_id: newColumnId
                  })
                });

                // Update WIP counter in UI
                updateWipCount(newColumnId);
                if (evt.from !== newColumn) {
                  const oldColumnId = evt.from.dataset.columnId;
                  updateWipCount(oldColumnId);
                }

              } catch (err) {
                console.error('Error during task move or WIP check:', err);
              }
            }
          });
        });

        // Live update WIP count and style
        function updateWipCount(columnId) {
          const colEl = document.querySelector(`[data-column-id="${columnId}"]`);
          const headerSpan = colEl.closest('.relative').querySelector('h2 > span');
          const taskCount = colEl.querySelectorAll('.task-card').length;

          fetch(`../../columns/get_wip_limit.php?column_id=${columnId}`)
            .then(res => res.json())
            .then(data => {
              if (data.wip_limit) {
                const limit = parseInt(data.wip_limit);
                // Update WIP count text
                const wipText = ` (${taskCount}/${limit})`;
                // Remove old WIP text if any
                let headerHtml = headerSpan.innerHTML.replace(/\(\d+\/\d+\)/, '');
                headerSpan.innerHTML = headerHtml + `<span class="text-sm ${taskCount >= limit ? 'text-red-600' : 'text-gray-600'} ml-1">${wipText}</span>`;

                // Add or remove border and title for WIP limit reached
                if (taskCount >= limit) {
                  colEl.classList.add('border-2', 'border-red-600');
                  colEl.setAttribute('title', `WIP limit reached: ${taskCount} / ${limit}`);
                } else {
                  colEl.classList.remove('border-2', 'border-red-600');
                  colEl.removeAttribute('title');
                }
              }
            });
        }


        let lastFetch = 0;

        function fetchBoardUpdates() {
          const boardId = <?= json_encode($_GET['id'] ?? null) ?>;
          if (!boardId) return;

          fetch(`../../realtime/poll_updates.php?board_id=${boardId}&last_fetch=${lastFetch}`)
            .then(res => res.json())
            .then(data => {
              if (data.updated) {
                console.log('🔁 Board changed. Reloading...');
                location.reload();
              }
              lastFetch = data.timestamp;
            })
            .catch(err => console.error('Polling error:', err));
        }

        setInterval(fetchBoardUpdates, 120000); // every 120 seconds



        new Sortable(document.getElementById('column-container'), {
          animation: 150,
          handle: '.column-handle', 
          onEnd: function(evt) {
            const columns = document.querySelectorAll('#column-container > div');
            const positions = [];

            columns.forEach((col, index) => {
              const columnId = col.dataset.columnId;
              if (columnId) {
                positions.push({
                  column_id: columnId,
                  position: index
                });
              }
            });

            fetch('../../columns/update_column_positions.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                  positions: positions
                })
              }).then(res => res.json())
              .then(data => {
                console.log(data.message);
              }).catch(err => console.error('Failed to update positions', err));
          }
        });
      </script>
</body>

</html>