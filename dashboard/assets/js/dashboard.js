document.addEventListener('DOMContentLoaded', () => {
  // Example: Initialize SortableJS for drag-drop columns & cards
  if (typeof Sortable !== 'undefined') {
    const columnsContainer = document.querySelector('#columns-container');
    if (columnsContainer) {
      new Sortable(columnsContainer, {
        animation: 150,
        onEnd: evt => {
          // After reorder, send new order to backend
          const positions = Array.from(columnsContainer.children).map((col, i) => ({
            column_id: col.dataset.id,
            position: i
          }));
          fetch('/columns/reorder_columns.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ positions })
          });
        }
      });
    }

    // Similarly, initialize drag-drop for task cards within columns
    document.querySelectorAll('.task-list').forEach(taskList => {
      new Sortable(taskList, {
        group: 'tasks',
        animation: 150,
        onAdd: evt => {
          const taskId = evt.item.dataset.id;
          const newColumnId = evt.to.dataset.columnId;
          fetch('/tasks/move_task.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task_id: taskId, new_column_id: newColumnId })
          });
        }
      });
    });
  }

  // Modal handling (open/close)
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', e => {
      const modalId = btn.dataset.modalOpen;
      document.getElementById(modalId).classList.remove('hidden');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', e => {
      const modalId = btn.dataset.modalClose;
      document.getElementById(modalId).classList.add('hidden');
    });
  });

  // Notifications poll every 30 seconds
  async function fetchNotifications() {
    const res = await fetch('/notifications/get_notifications.php');
    if (res.ok) {
      const data = await res.json();
      const notifCountElem = document.getElementById('notif-count');
      if (data.length > 0) {
        notifCountElem.textContent = data.length;
        notifCountElem.classList.remove('hidden');
      } else {
        notifCountElem.classList.add('hidden');
      }
      // TODO: Display notifications UI
    }
  }
  fetchNotifications();
  setInterval(fetchNotifications, 30000);
});
