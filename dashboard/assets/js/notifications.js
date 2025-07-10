<script>
async function loadNotifications() {
  const res = await fetch('../notifications/get_notifications.php');
  const notifications = await res.json();

  const container = document.getElementById('notificationList');
  container.innerHTML = '';
  container.classList.remove('hidden');

  if (notifications.length === 0) {
    container.innerHTML = '<div class="p-2 text-gray-500">No new notifications</div>';
    return;
  }

  notifications.forEach(n => {
    const data = JSON.parse(n.data);
    let message = '🔔 New notification';

    if (n.type === 'task_created') {
      message = `📝 Task created: ${data.title}`;
    }

    const div = document.createElement('div');
    div.className = 'p-2 border-b hover:bg-gray-100 cursor-pointer';
    div.innerText = message;
    container.appendChild(div);
  });
}
</script>
