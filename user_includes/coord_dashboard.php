<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../db_connect.php';

// ---- Coordinator filters ----
$role = $_SESSION['role'] ?? '';
$department_id = $_SESSION['department_id'] ?? '';
$campus_id = $_SESSION['campus_id'] ?? '';

$hasFilter = ($role === 'Coordinator' && !empty($department_id) && $campus_id !== '');
$filter_sql = $hasFilter ? " AND department_id = ? AND campus_id = ?" : "";

// ---- Monthly counts ----
$year = date('Y');
$monthly_counts = array_fill(1, 12, 0);

for ($m = 1; $m <= 12; $m++) {
    $sql = "SELECT COUNT(*) AS cnt 
            FROM intellectual_properties 
            WHERE YEAR(date_submitted_to_itso) = ? 
              AND MONTH(date_submitted_to_itso) = ? {$filter_sql}";
    $stmt = $conn->prepare($sql);
    if ($hasFilter) {
        $stmt->bind_param('iisi', $year, $m, $department_id, $campus_id);
    } else {
        $stmt->bind_param('ii', $year, $m);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $monthly_counts[$m] = (int)($res->fetch_assoc()['cnt'] ?? 0);
    $stmt->close();
}

// ---- Status counts ----
$status_counts = [
    'Pending' => 0,
    'Ongoing' => 0,
    'Completed' => 0,
    'Total' => 0
];

$sql = "SELECT status, COUNT(*) AS cnt 
        FROM intellectual_properties 
        WHERE 1=1 {$filter_sql} 
        GROUP BY status";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $status = $r['status'];
    $cnt = (int)$r['cnt'];
    if (isset($status_counts[$status])) $status_counts[$status] = $cnt;
}
$stmt->close();

// Total IPs
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE 1=1 {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
$status_counts['Total'] = (int)($res->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

// ---- User count (only same dept & campus) ----
$sql = "SELECT COUNT(*) AS cnt 
        FROM users 
        WHERE status = 'approved' {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
$user_count = (int)($res->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

// ---- Pie chart ----
$sql_granted = "SELECT COUNT(*) AS cnt 
                FROM intellectual_properties 
                WHERE status = 'Completed' {$filter_sql}";
$stmt = $conn->prepare($sql_granted);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
$granted = (int)($res->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

$sql_revision = "SELECT COUNT(*) AS cnt 
                 FROM intellectual_properties 
                 WHERE remarks IS NOT NULL AND TRIM(remarks) <> '' {$filter_sql}";
$stmt = $conn->prepare($sql_revision);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
$for_revision = (int)($res->fetch_assoc()['cnt'] ?? 0);
$stmt->close();

// ---- Recent notifications ----
$recent_notifications = [];
$sql = "SELECT id, title, message, created_at 
        FROM notifications 
        WHERE 1=1 {$filter_sql} 
        ORDER BY created_at DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $recent_notifications[] = $r;
$stmt->close();

// ---- Recent IP submissions ----
$recent_ips = [];
$sql = "SELECT ip_id, tracking_id, title, applicant_name, date_submitted_to_itso, status 
        FROM intellectual_properties 
        WHERE 1=1 {$filter_sql} 
        ORDER BY date_submitted_to_itso DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $recent_ips[] = $r;
$stmt->close();

// ---- Combine into unified activity feed ----
$activities = [];
foreach ($recent_notifications as $n) {
    $activities[] = [
        'type' => 'notification',
        'title' => $n['title'],
        'message' => $n['message'],
        'date' => $n['created_at']
    ];
}
foreach ($recent_ips as $ip) {
    $activities[] = [
        'type' => 'ip_submission',
        'title' => $ip['title'],
        'message' => 'New IP application submitted by ' . $ip['applicant_name'] . ' (' . $ip['status'] . ')',
        'date' => $ip['date_submitted_to_itso'] ?? date('Y-m-d H:i:s')
    ];
}
usort($activities, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
$activities = array_slice($activities, 0, 3);


$campus_name_display = "N/A";

if (!empty($campus_id)) {
    $stmt = $conn->prepare("SELECT campus_name FROM campuses WHERE campus_id = ?");
    $stmt->bind_param('i', $campus_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $campus_name_display = $row['campus_name'];
    }
    $stmt->close();
}
$department_fullnames = [
    'CCS' => 'College of Computer Studies',
    'CTE' => 'College of Teacher Education',
    'CFND' => 'College of Food Nutrition and Dietetics',
    'CIT' => 'College of Industrial Technology',
    'CA' => 'College of Agriculture',
    'CAS' => 'College of Arts and Sciences',
    'CBAA' => 'College of Business Administration and Accountancy',
    'CE' => 'College of Engineering',
    'CCJE' => 'College of Criminal Justice Education',
    'CF' => 'College of Forestry',
    'CHMT' => 'College of Hospitality Management and Tourism',
    'CNAH' => 'College of Nursing and Allied Health'
];
$department_display = $department_fullnames[$department_id] ?? $department_id;

// After your monthly counts loop
$months_labels = [];
$month_values = [];

for ($m = 1; $m <= 12; $m++) {
    $months_labels[] = date('M', mktime(0, 0, 0, $m, 1));
    $month_values[] = $monthly_counts[$m];
}

// ---- Completed This Month ----
$sql = "SELECT COUNT(*) AS cnt 
        FROM intellectual_properties 
        WHERE status = 'Completed'
          AND YEAR(date_submitted_to_itso) = YEAR(CURDATE())
          AND MONTH(date_submitted_to_itso) = MONTH(CURDATE())
          {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) {
    $stmt->bind_param('si', $department_id, $campus_id);
}
$stmt->execute();
$res = $stmt->get_result();
$status_counts['CompletedThisMonth'] = (int)($res->fetch_assoc()['cnt'] ?? 0);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Coordinator Dashboard</title>
<link rel="stylesheet" href="assets/css/coord_content.css">
<link rel="icon" type="image/png" href="ITSO.png">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>


<!-- TOP ROW -->
<div class="stats-row">
  <div class="stat-card pending">
    <div class="stat-title">Pending Review <ion-icon name="alert-circle-outline"></ion-icon></div>
    <div class="stat-value" id="pendingIPs"><?= (int)($status_counts['Pending'] ?? 0) ?></div>
    <div class="stat-subtext">Awaiting action</div>
  </div>

  <div class="stat-card ongoing">
    <div class="stat-title">Ongoing <ion-icon name="sync-circle-outline"></ion-icon></div>
    <div class="stat-value" id="ongoingIPs"><?= (int)($status_counts['Ongoing'] ?? 0) ?></div>
    <div class="stat-subtext">Under review</div>
  </div>

  <div class="stat-card completed">
    <div class="stat-title">Completed <ion-icon name="checkmark-circle-outline"></ion-icon></div>
    <div class="stat-value" id="completedIPs"><?= (int)($status_counts['Completed'] ?? 0) ?></div>
    <div class="stat-subtext">+<?= (int)($status_counts['CompletedThisMonth'] ?? 0) ?> this month</div>
  </div>

  <div class="stat-card user">
    <div class="stat-title">Active Users <ion-icon name="people-circle-outline"></ion-icon></div>
    <div class="stat-value" id="totalIPs"><?= (int)$user_count ?></div>
    <div class="stat-subtext">Coordinators</div>
  </div>
</div>



<!-- MIDDLE + BOTTOM GRID -->
<div class="main-grid">
  <!-- Big left pie chart -->
  <div class="pie-box">
    <canvas id="pieChart"></canvas>
  </div>

  <div class="right-section">
  <!-- Top right - line chart -->
  <div class="line-box">
    <canvas id="monthsChart"></canvas>
  </div>

  <div class="activity-box">
  <!-- Bottom right - recent activity -->
  <div class="recent-activity" id="recentActivityContainer">
    <div class="title">RECENT ACTIVITY</div>

    <?php
    $activities = [];

    foreach ($recent_notifications as $n) {
        $activities[] = [
            'type' => 'notification',
            'title' => $n['title'],
            'message' => $n['message'],
            'date' => $n['created_at']
        ];
    }

    foreach ($recent_ips as $ip) {
        $activities[] = [
            'type' => 'ip_submission',
            'title' => $ip['title'],
            'message' => $ip['applicant_name'] . ' — ' . $ip['status'],
            'date' => $ip['date_submitted_to_itso'] ?? date('Y-m-d H:i:s')
        ];
    }

    usort($activities, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    $activities = array_slice($activities, 0, 3);
    ?>

    <?php if (count($activities) === 0): ?>
      <div style="color:#d1d5db;font-style:italic">No recent activity</div>
    <?php else: ?>
      <?php foreach ($activities as $a): ?>
        <div class="recent-item">
          <div><?= $a['type'] === 'notification' ? '🔔' : '📄' ?> <strong><?= htmlspecialchars($a['title']); ?></strong></div>
          <div class="msg"><?= htmlspecialchars($a['message']); ?></div>
          <div class="date"><?= date("M d, Y h:i A", strtotime($a['date'])); ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
      </div>
      </div>

<script>
const monthsLabels = <?= json_encode($months_labels) ?>;
const monthsData = <?= json_encode($month_values) ?>;

const ctx = document.getElementById('monthsChart').getContext('2d');

// Create gradient for area fill
const gradient = ctx.createLinearGradient(0, 0, 0, 250);
gradient.addColorStop(0, 'rgba(253, 186, 116, 0.35)'); 
gradient.addColorStop(1, 'rgba(253, 186, 116, 0)');  

new Chart(ctx, {
  type: 'line',
  data: {
    labels: monthsLabels,
    datasets: [{
      label: 'All Intellectual Properties',
      data: monthsData,
      fill: true,
      backgroundColor: gradient,  
      borderColor: 'rgba(249, 115, 22, 0.9)',
      borderWidth: 2,
      tension: 0.4,               
      pointBackgroundColor: 'rgba(249, 115, 22, 0.9)',
      pointBorderColor: '#fff',
      pointRadius: 4,
      pointHoverRadius: 6,
      pointHoverBorderWidth: 2,
      pointStyle: 'circle'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    devicePixelRatio: window.devicePixelRatio,
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          color: '#475569',
          font: { family: 'Inter, sans-serif', size: 13, weight: '500' }
        }
      },
      tooltip: {
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        titleColor: '#0f172a',
        bodyColor: '#1e293b',
        borderColor: 'rgba(0,0,0,0.08)',
        borderWidth: 1,
        displayColors: false,
        padding: 12,
        cornerRadius: 8
      }
    },
    scales: {
      x: {
        ticks: { color: '#475569', font: { family: 'Inter, sans-serif', size: 12 } },
        grid: { color: 'rgba(226, 232, 240, 0.35)', lineWidth: 0.8 }
      },
      y: {
        beginAtZero: true,
        ticks: { color: '#475569', font: { family: 'Inter, sans-serif', size: 12 }, stepSize: 1 },
        grid: { color: 'rgba(226, 232, 240, 0.35)', lineWidth: 0.8 }
      }
    },
    elements: {
      line: { borderJoinStyle: 'round' },
      point: { borderWidth: 1 }
    }
  }
});

// Pie Chart
const pieCtx = document.getElementById('pieChart').getContext('2d');

window.grantedChart = new Chart(pieCtx, {
  type: 'pie',
  data: {
    labels: ['Granted', 'For Revision'],
    datasets: [{
      label: 'Application Status',
      data: [<?= (int)$granted ?>, <?= (int)$for_revision ?>],
      backgroundColor: [
        'rgba(134,239,173,0.85)', 
        'rgba(253,164,175,0.85)'
      ],
      borderColor: [
        'rgba(134,239,172,1)',
        'rgba(253,164,175,1)'
      ],
      borderWidth: 2,
      hoverOffset: 10
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'bottom',
        labels: {
          color: '#334155',
          font: { size: 14 }
        }
      },
      tooltip: {
        enabled: true,
        callbacks: {
          label: function (context) {
            return `${context.label}: ${context.parsed}`;
          }
        }
      }
    }
  }
});

// Unified fetch function
function updateDashboard() {
  fetch('user_includes/fetch_dashboard.php')
    .then(res => res.json())
    .then(data => {

      // ----- Update Table Status Badges -----
      data.tableStatuses.forEach(row => {
        const tr = document.querySelector(`tr[data-ipid='${row.ip_id}']`);
        if (tr) {
          const badge = tr.querySelector('.status-badge');
          badge.textContent = row.status;
          badge.classList.remove('status-pending','status-ongoing','status-completed');
          switch(row.status.toLowerCase()) {
            case 'pending': badge.classList.add('status-pending'); break;
            case 'ongoing': badge.classList.add('status-ongoing'); break;
            case 'completed': badge.classList.add('status-completed'); break;
          }
        }
      });

      // ----- Update Card Stats -----
      const cards = data.cards;
      document.getElementById('totalIPs').textContent = cards.total;
      document.getElementById('pendingIPs').textContent = cards.pending;
      document.getElementById('ongoingIPs').textContent = cards.ongoing;
      document.getElementById('completedIPs').textContent = cards.completed;

      // Update subtext for "Completed this month"
      const completedSubtext = document.querySelector('.stat-card.completed .stat-subtext');
      if (completedSubtext) {
        completedSubtext.textContent = `+${cards.completedThisMonth} this month`;
      }

      // ----- Update Pie/Bar Chart -----
      if (window.grantedChart) {
        window.grantedChart.data.datasets[0].data = [cards.granted, cards.forRevision];
        window.grantedChart.update();
      }

      // ----- Update Recent Activity -----
      const container = document.getElementById('recentActivityContainer');
      container.innerHTML = '';

      if (data.recent && data.recent.length) {
        data.recent.forEach(item => {
          const div = document.createElement('div');
          div.classList.add('recent-item');

          // Format date safely
          let dateStr = '';
          if (item.created_at) {
            const d = new Date(item.created_at);
            if (!isNaN(d)) {
              dateStr = d.toLocaleString('en-US', { month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', hour12:true });
            } else {
              dateStr = item.created_at;
            }
          }

          div.innerHTML = `
            <div>${item.type === 'notification' ? '🔔' : '📄'} <strong>${item.title}</strong></div>
            <div class="msg">${item.message}</div>
            <div class="date">${dateStr}</div>
          `;
          container.appendChild(div);
        });
      } else {
        container.innerHTML = '<div style="color:#d1d5db;font-style:italic">No recent activity</div>';
      }

    })
    .catch(err => console.error('Error fetching dashboard:', err));
}

updateDashboard();

setInterval(updateDashboard, 15000);

</script>

</body>
</html>
