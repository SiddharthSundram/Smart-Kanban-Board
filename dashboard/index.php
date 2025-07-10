<?php
require_once '../auth/auth_middleware.php';
require_once '../config/config.php';
require_once '../utils/helpers.php';
$boardsSql = "SELECT * FROM boards WHERE owner_id = ? AND is_archived = 0";

$params = [$_SESSION['user_id']];

// Check if we are showing archived boards
$showArchived = isset($_GET['archived']) && $_GET['archived'] == 1;
$boardsSql = "SELECT * FROM boards WHERE owner_id = ?";
$params = [$_SESSION['user_id']];

if (!$showArchived) {
  $boardsSql .= " AND is_archived = 0";
} else {
  $boardsSql .= " AND is_archived = 1";
}


if (!empty($_GET['search']) || !empty($_GET['priority']) || !empty($_GET['label'])) {
  $boardsSql .= " AND id IN (
    SELECT DISTINCT b.id
    FROM boards b
    JOIN columns c ON c.board_id = b.id
    JOIN tasks t ON t.column_id = c.id
    WHERE b.owner_id = ?
  ";

  $params[] = $_SESSION['user_id']; // Required again for subquery

  if (!empty($_GET['search'])) {
    $boardsSql .= " AND t.title LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
  }

  if (!empty($_GET['priority'])) {
    $boardsSql .= " AND t.priority = ?";
    $params[] = $_GET['priority'];
  }

  if (!empty($_GET['label'])) {
    $boardsSql .= " AND t.label_color = ?";
    $params[] = $_GET['label'];
  }

  $boardsSql .= ")";
}

$boardsSql .= " ORDER BY position ASC";
$stmt = $pdo->prepare($boardsSql);
$stmt->execute($params);
$boards = $stmt->fetchAll();


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

  <!-- Top Navigation -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
    <div class="space-x-4 flex items-center">
      <a href="../auth/profile.php" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800 transition">👤 Profile</a>

      <!-- Archive Board Navigation -->
      <!-- <a href="index.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">📂 Active Boards</a>
      <a href="index.php?archived=1" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">🗃️ Archived Boards</a> -->


      <!-- Notification -->
      <div class="relative">
        <button onclick="toggleNotifications()" class="relative bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
          🔔
          <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-600 text-xs text-white rounded-full px-1 hidden">!</span>
        </button>
        <div id="notificationList" class="absolute right-0 mt-2 w-72 bg-white border shadow-lg rounded hidden z-50 max-h-96 overflow-y-auto">
          <div class="p-3 text-center text-sm text-gray-500">Loading...</div>
        </div>
      </div>


      <a href="../analytics/dashboard_analytics.php" class="px-4 py-2 rounded bg-green-500 hover:bg-green-600 text-white transition">📊 Analytics</a>
      <a href="../auth/logout.php" class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-white transition">🚪 Logout</a>
    </div>
  </div>

  <!-- Create Board -->
  <div class="mb-4">
    <a href="boards/create.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded shadow-md font-medium transition">➕ Create New Board</a>
  </div>

  <!-- Boards Section -->

  <!-- Filters Section -->
  <!-- 🔍 Filter UI -->
  <!-- 🔍 Filter UI -->
  <div class="bg-white rounded shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Search by Title -->
      <input type="text" name="search" placeholder="🔍 Search by task title"
        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
        class="border p-2 rounded">

      <!-- Priority Dropdown -->
      <select name="priority" class="border p-2 rounded">
        <option value="">🎯 All Priorities</option>
        <?php foreach (['low', 'medium', 'high', 'urgent'] as $p): ?>
          <option value="<?= $p ?>" <?= ($_GET['priority'] ?? '') === $p ? 'selected' : '' ?>>
            <?= ucfirst($p) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Label Color Dropdown -->
      <select name="label" class="border p-2 rounded">
        <option value="">🎨 All Labels</option>
        <?php foreach (['red', 'blue', 'green', 'yellow', 'purple', 'gray'] as $color): ?>
          <option value="<?= $color ?>" <?= ($_GET['label'] ?? '') === $color ? 'selected' : '' ?>>
            <?= ucfirst($color) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Buttons Row (side-by-side right aligned) -->
      <div class="md:col-span-3 flex justify-end gap-4 pt-2">
        <a href="index.php"
          class="text-sm px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
          ❌ Clear Filters
        </a>
        <button type="submit"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
          🔎 Apply Filters
        </button>
      </div>
    </form>
  </div>

  <?php
  $showArchived = isset($_GET['archived']) && $_GET['archived'] == 1;
  ?>
  <div class="mb-4 flex justify-between items-center">
    <h2 class="text-lg font-semibold text-gray-700">Your Boards</h2>
    <div class="space-x-2">
      <a href="index.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">📂 Active Boards</a>
      <a href="index.php?archived=1" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">🗃️ Archived Boards</a>
    </div>
  </div>

  <?php if (empty($boards)): ?>
    <p class="text-gray-600">You don't have any <?= $showArchived ? 'archived' : '' ?> boards yet.</p>
  <?php else: ?>
    <div id="board-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($boards as $board): ?>
        <div class="p-4 bg-white rounded-lg shadow hover:shadow-md transition <?= $showArchived ? 'border-l-4 border-yellow-400' : 'hover:bg-blue-50' ?>" data-id="<?= $board['id'] ?>">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-medium text-gray-800"><?= htmlspecialchars($board['name']) ?></h3>
            <div class="space-x-2 text-sm">
              <?php if (!$showArchived): ?>
                <button onclick="openEditBoardModal(<?= $board['id'] ?>, '<?= htmlspecialchars($board['name'], ENT_QUOTES) ?>')" class="text-blue-600 hover:text-blue-800 transition" title="Edit Board">✏️</button>
                <button onclick="confirmDeleteBoard(<?= $board['id'] ?>)" class="text-red-600 hover:text-red-800 transition" title="Delete Board">🗑️</button>
                <button onclick="confirmCloneBoard(<?= $board['id'] ?>)" class="text-green-600 hover:text-green-800 transition" title="Clone Board">📋</button>
                <button onclick="confirmArchiveBoard(<?= $board['id'] ?>)" class="text-yellow-600 hover:text-yellow-800 transition" title="Archive Board">🗃️</button>

              <?php else: ?>
                <button onclick="confirmUnarchiveBoard(<?= $board['id'] ?>)" class="text-green-600 hover:text-green-800">♻️ Unarchive</button>

              <?php endif; ?>
            </div>
          </div>
          <p class="text-sm text-gray-500 mb-2">Board ID: <?= $board['id'] ?></p>
          <div class="flex justify-between items-center text-sm">
            <?php if (!$showArchived): ?>
              <a href="boards/view.php?id=<?= $board['id'] ?>" class="text-green-600 hover:underline transition">🗂 View</a>
              <a href="../boards/share_board.php?id=<?= $board['id'] ?>" class="text-purple-600 hover:underline transition">🤝 Share</a>
            <?php else: ?>
              <span class="text-gray-500 italic">Archived</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>


  <!-- Edit Modal -->
  <div id="editBoardModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow w-96">
      <h2 class="text-xl font-bold mb-4">Edit Board</h2>
      <form method="POST" action="../boards/update_board.php">
        <input type="hidden" name="board_id" id="edit-board-id">

        <label class="block mb-1 text-sm font-medium text-gray-700">Board Name</label>
        <input type="text" name="name" id="edit-board-name" class="w-full border p-2 rounded mb-4" required>

        <label class="block mb-1 text-sm font-medium text-gray-700">Visibility</label>
        <select name="visibility" id="edit-board-visibility" class="w-full border p-2 rounded mb-4">
          <option value="private">🔒 Private</option>
          <option value="public">🌐 Public</option>
          <option value="team">👥 Team Only</option>
        </select>

        <div class="text-right space-x-2">
          <button type="button" onclick="closeModal('editBoardModal')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Update</button>
        </div>
      </form>
    </div>
  </div>


  <!-- JS Logic -->
  <script>
    function confirmUnarchiveBoard(boardId) {
  if (confirm("♻️ Are you sure you want to unarchive this board? It will become active again.")) {
    window.location.href = "/smart-kanban-board/boards/unarchive_board.php?id=" + boardId;
  }
}


    function confirmArchiveBoard(boardId) {
      if (confirm("🗃️ Are you sure you want to archive this board? You can restore it later from the Archived Boards section.")) {
        window.location.href = "../boards/archive_board.php?id=" + boardId;
      }
    }

    function confirmDeleteBoard(boardId) {
      if (confirm("❌ Are you sure you want to delete this board permanently?")) {
        window.location.href = "../boards/delete_board.php?id=" + boardId;
      }
    }

    function confirmCloneBoard(boardId) {
      if (confirm("📋 Clone this board and all its columns/tasks?")) {
        window.location.href = "../boards/clone_board.php?id=" + boardId;
      }
    }


    function openEditBoardModal(id, name, visibility = 'private') {
      document.getElementById('edit-board-id').value = id;
      document.getElementById('edit-board-name').value = name;
      document.getElementById('edit-board-visibility').value = visibility;
      document.getElementById('editBoardModal').classList.remove('hidden');
    }

    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }

    function confirmDeleteBoard(id) {
      if (confirm("Are you sure you want to delete this board?")) {
        window.location.href = '../boards/delete_board.php?id=' + id;
      }
    }

    function confirmCloneBoard(id) {
      if (confirm("Clone this board?")) {
        window.location.href = '../boards/clone_board.php?id=' + id;
      }
    }

    async function toggleNotifications() {
      const list = document.getElementById('notificationList');
      list.classList.toggle('hidden');
      if (!list.classList.contains('hidden')) loadNotifications();
    }

    async function loadNotifications() {
      const res = await fetch('../notifications/get_notifications.php');
      const notifications = await res.json();
      const list = document.getElementById('notificationList');
      const badge = document.getElementById('notifBadge');

      console.log('User Notifications:', notifications); // ✅ Check if it logs

      list.innerHTML = '';
      badge.classList.toggle('hidden', notifications.length === 0);

      if (notifications.length === 0) {
        list.innerHTML = '<div class="p-3 text-gray-500 text-center">No new notifications</div>';
        return;
      }

      notifications.forEach(n => {
        console.log("Processing notification:", n);

        let message = '🔔 Notification';
        let data = {};

        try {
          data = JSON.parse(n.data || '{}');
        } catch (e) {
          console.error("Failed to parse notification data:", n.data);
        }

        switch (n.type) {
          case 'task_created':
            message = `📝 Task created: ${data.title}`;
            break;
          case 'task_updated':
            message = `✏️ Task updated: ${data.title}`;
            break;
          case 'task_completed':
            message = `✅ Task completed: ${data.title}`;
            break;
          case 'task_due_soon':
            message = `⏰ Task due soon: ${data.title}`;
            break;
          case 'task_assigned':
            message = `📌 Task assigned: ${data.title}`;
            break;
          case 'comment':
            message = `💬 New comment on task ${data.task_id}`;
            break;
        }

        const div = document.createElement('div');
        div.className = 'p-3 border-b hover:bg-gray-100 cursor-pointer text-sm';
        div.textContent = message;

        // ✅ Force log and attach click even if keys missing
        div.onclick = async () => {
          console.log("Redirecting with data:", data);
          if (!data.task_id || !data.board_id) return;

          // Mark as read before redirecting
          await fetch('../notifications/mark_as_read.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `notif_id=${encodeURIComponent(n.id)}`
          });

          // Redirect to task view
          window.location.href = `../dashboard/boards/view.php?id=${data.board_id}&highlight_task=${data.task_id}`;
        };


        list.appendChild(div);
      });

    }




    window.addEventListener('DOMContentLoaded', () => {
      const boardList = document.getElementById('board-list');
      new Sortable(boardList, {
        animation: 150,
        onEnd: evt => {
          const ids = Array.from(boardList.children).map(el => el.dataset.id);
          fetch('../boards/reorder_boards.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              order: ids
            })
          }).then(res => res.json()).then(console.log).catch(console.error);
        }
      });

      // Preload notification badge
      fetch('../notifications/get_notifications.php')
        .then(res => res.json())
        .then(data => {
          if (data.length > 0) document.getElementById('notifBadge').classList.remove('hidden');
        });
    });
  </script>

</body>

</html>