<?php require_once '../auth/auth_middleware.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/luxon@2.0.2/build/global/luxon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.1.0"></script>
</head>
<body class="bg-gray-100 p-6 min-h-screen">

  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold">📊 Analytics Dashboard</h1>
    <a href="../dashboard/index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
      ⬅ Go to Dashboard
    </a>
  </div>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="text-gray-500">Total Tasks</p>
      <h2 class="text-2xl font-bold" id="totalTasks">--</h2>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="text-gray-500">Completed</p>
      <h2 class="text-2xl font-bold text-green-600" id="completedTasks">--</h2>
    </div>
    <div class="bg-white p-4 rounded shadow text-center">
      <p class="text-gray-500">Overdue</p>
      <h2 class="text-2xl font-bold text-red-600" id="overdueTasks">--</h2>
    </div>
  </div>

  <!-- Charts -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-2">Tasks Per Column</h2>
      <div class="h-64">
        <canvas id="colChart"></canvas>
      </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-2">Completion Rate</h2>
      <div class="h-64">
        <canvas id="rateChart"></canvas>
      </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-2">Burn Down Chart</h2>
      <div class="h-64">
        <canvas id="burnChart"></canvas>
      </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-2">Task Timeline</h2>
      <div class="h-64">
        <canvas id="timeChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart JS Logic -->
  <script>
    async function loadData() {
      const res = await fetch('dashboard_data.php');
      return await res.json();
    }

    function initCharts(d) {
      document.getElementById('totalTasks').textContent = d.total_tasks;
      document.getElementById('completedTasks').textContent = d.completed_tasks;
      document.getElementById('overdueTasks').textContent = d.overdue_tasks;

      // Tasks per column
      if (d.columns && d.columns.length) {
        new Chart(document.getElementById('colChart'), {
          type: 'bar',
          data: {
            labels: d.columns.map(c => c.name),
            datasets: [{
              label: 'Tasks',
              data: d.columns.map(c => c.count),
              backgroundColor: '#3B82F6'
            }]
          },
          options: { responsive: true, plugins: { legend: { display: false } } }
        });
      }

      // Completion rate
      new Chart(document.getElementById('rateChart'), {
        type: 'doughnut',
        data: {
          labels: ['Completed', 'Overdue', 'Remaining'],
          datasets: [{
            data: [
              d.completed_tasks,
              d.overdue_tasks,
              d.total_tasks - d.completed_tasks - d.overdue_tasks
            ],
            backgroundColor: ['#10B981', '#EF4444', '#FBBF24']
          }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
      });

      // Burn down chart
      if (d.burndown && d.burndown.length) {
        new Chart(document.getElementById('burnChart'), {
          type: 'line',
          data: {
            labels: d.burndown.map(p => p.date),
            datasets: [
              {
                label: 'Ideal Burn',
                data: d.burndown.map(p => p.ideal),
                borderColor: '#60A5FA',
                fill: false,
                tension: 0.3
              },
              {
                label: 'Actual Remaining',
                data: d.burndown.map(p => p.actual),
                borderColor: '#F87171',
                fill: false,
                tension: 0.3
              }
            ]
          },
          options: {
            responsive: true,
            scales: {
              x: {
                title: { display: true, text: 'Date' }
              },
              y: {
                beginAtZero: true,
                title: { display: true, text: 'Tasks Remaining' }
              }
            }
          }
        });
      }

      // Task timeline
      if (d.timeline && d.timeline.length) {
        new Chart(document.getElementById('timeChart'), {
          type: 'line',
          data: {
            labels: d.timeline.map(t => t.date),
            datasets: [{
              label: 'Tasks Created',
              data: d.timeline.map(t => t.count),
              borderColor: '#34D399',
              fill: true,
              backgroundColor: 'rgba(52,211,153,0.1)',
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              x: {
                type: 'time',
                time: { unit: 'day' },
                title: { display: true, text: 'Date' }
              },
              y: {
                beginAtZero: true,
                title: { display: true, text: 'Tasks Created' }
              }
            }
          }
        });
      }
    }

    window.onload = async () => {
      try {
        const data = await loadData();
        initCharts(data);
      } catch (err) {
        console.error('Failed to load analytics:', err);
      }
    };
  </script>
</body>
</html>
