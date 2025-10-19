<?php
session_start();

include 'db_connect.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");


if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['Chairperson', 'Director'])) {
    echo "<script>
        alert('Unauthorized access!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

// --- Page filters ---
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard_content';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// --- Statistics ---
$approved_count = 0;
$result_approved = $conn->query("SELECT COUNT(*) as approved_count FROM intellectual_properties WHERE status = 'Completed'");
if ($result_approved && $result_approved->num_rows > 0) {
    $approved_count = $result_approved->fetch_assoc()['approved_count'];
}

$rejected_count = 0;
$result_rejected = $conn->query("SELECT COUNT(*) as rejected_count FROM intellectual_properties WHERE status = 'Pending'");
if ($result_rejected && $result_rejected->num_rows > 0) {
    $rejected_count = $result_rejected->fetch_assoc()['rejected_count'];
}

$ongoing_count = 0;
$result_ongoing = $conn->query("SELECT COUNT(*) as ongoing_count FROM intellectual_properties WHERE status = 'Ongoing'");
if ($result_ongoing && $result_ongoing->num_rows > 0) {
    $ongoing_count = $result_ongoing->fetch_assoc()['ongoing_count'];
}

$all_count = 0;
$result_all = $conn->query("SELECT COUNT(*) as all_count FROM intellectual_properties");
if ($result_all && $result_all->num_rows > 0) {
    $all_count = $result_all->fetch_assoc()['all_count'];
}

// --- Authors ---
$authors = [];
$result_authors = $conn->query("SELECT authors FROM intellectual_properties");
if ($result_authors && $result_authors->num_rows > 0) {
    while ($r = $result_authors->fetch_assoc()) {
        $authors[] = $r['authors'];
    }
}

// --- Generate file view/download links ---
function generateFileLink($filename) {
    if ($filename && file_exists("uploads/" . $filename)) {
        $filePath = "uploads/" . rawurlencode($filename);
        return "
            <a href='$filePath' target='_blank' title='View File' style='color: #000000ff; text-decoration: none; transition: transform 0.2s;'>
                <ion-icon name='eye-outline' style='font-size: 25px;'></ion-icon>
            </a>
            <a href='$filePath' download title='Download File' style='color: #000000ff; text-decoration: none; transition: transform 0.2s; margin-left:8px;'>
                <ion-icon name='download-outline' style='font-size: 25px;'></ion-icon>
            </a>
        ";
    } else {
        return "<span style='color: gray;'>No file</span>";
    }
}

// --- Main query ---
$sql = "SELECT ip.*, c.campus_name, d.department_name 
        FROM intellectual_properties ip
        LEFT JOIN campuses c ON ip.campus_id = c.campus_id
        LEFT JOIN departments d ON ip.department_id = d.department_id";

if (!empty($statusFilter)) {
    $safeStatus = $conn->real_escape_string($statusFilter);
    $sql .= " WHERE ip.status = '$safeStatus'";
}
$result = $conn->query($sql);

// --- Upcoming expirations (0–180 days) ---
$upcoming_query = $conn->query("
    SELECT tracking_id, title, classification, expiration_date,
           DATEDIFF(expiration_date, CURDATE()) AS days_left
    FROM intellectual_properties
    WHERE expiration_date IS NOT NULL
      AND DATEDIFF(expiration_date, CURDATE()) BETWEEN 0 AND 180
    ORDER BY expiration_date ASC
");
$upcoming_notifications = [];
if ($upcoming_query && $upcoming_query->num_rows > 0) {
    while ($row = $upcoming_query->fetch_assoc()) {
        $upcoming_notifications[] = $row;
    }
}

// --- Expired applications (< 0 days) ---
$expired_query = $conn->query("
    SELECT tracking_id, title, classification, expiration_date,
           DATEDIFF(expiration_date, CURDATE()) AS days_left
    FROM intellectual_properties
    WHERE expiration_date IS NOT NULL
      AND DATEDIFF(expiration_date, CURDATE()) < 0
    ORDER BY expiration_date DESC
");
$expired_notifications = [];
if ($expired_query && $expired_query->num_rows > 0) {
    while ($row = $expired_query->fetch_assoc()) {
        $expired_notifications[] = $row;
    }
}

// --- Total notifications ---
$notif_count = count($upcoming_notifications) + count($expired_notifications);

// --- Success Message ---
if (isset($_SESSION['success_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: '" . $_SESSION['success_message'] . "',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        });
    </script>";
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/png" href="assets/ITSO.png">  
</head>
<body>


  <div class="navigation" id="sidebar">
    <ul>
      <li class="logo">
    <a href="#">
        <img src="assets/imgs/itsolog.png" alt="logos">
    </a>
</li>

<li class="<?php echo ($page == 'dashboard_content') ? 'active' : ''; ?>">
  <a href="admin_dashboard.php?page=dashboard_content">
    <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
    <span class="title">Dashboard</span>
  </a>
</li>

<?php if ($_SESSION['role'] === 'Chairperson') : ?>
  <li class="<?php echo ($page == 'all_applications') ? 'active' : ''; ?>">
    <a href="admin_dashboard.php?page=all_applications">
      <span class="icon"><ion-icon name="file-tray-full-outline"></ion-icon></span>
      <span class="title">All Intellectual Properties</span>
    </a>
  </li>

  <li class="<?php echo ($page == 'create_application') ? 'active' : ''; ?>">
    <a href="admin_dashboard.php?page=create_application">
      <span class="icon"><ion-icon name="create-outline"></ion-icon></span>
      <span class="title">Create Application</span>
    </a>
  </li>

  <li class="<?php echo ($page == 'app_form') ? 'active' : ''; ?>">
    <a href="admin_dashboard.php?page=app_form">
      <span class="icon"><ion-icon name="apps-outline"></ion-icon></span>
      <span class="title">Application Forms</span>
    </a>
  </li>
<?php endif; ?>

<li class="<?php echo ($page == 'reports') ? 'active' : ''; ?>">
  <a href="admin_dashboard.php?page=reports">
    <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
    <span class="title">Reports</span>
  </a>
</li>

<li class="<?php echo ($page == 'acc_manager') ? 'active' : ''; ?>">
  <a href="admin_dashboard.php?page=acc_manager">
    <span class="icon"><ion-icon name="people-outline"></ion-icon></span>
    <span class="title">Manage Accounts</span>
    <?php
      include 'db_connect.php';
      $sql = "SELECT COUNT(*) AS total FROM users WHERE status = 'Pending'";
      $result = $conn->query($sql);
      $count = 0;
      if ($result && $row = $result->fetch_assoc()) {
          $count = $row['total'];
      }
      if ($count > 0) {
          echo "<span class='badge'>$count</span>";
      }
    ?>
  </a>
</li>

<?php if ($_SESSION['role'] === 'Director') : ?>
  <li class="<?php echo ($page == 'departments') ? 'active' : ''; ?>">
    <a href="admin_dashboard.php?page=departments">
      <span class="icon"><ion-icon name="layers-outline"></ion-icon></span>
      <span class="title">Departments</span>
    </a>
  </li>
<?php endif; ?>



<li class="<?php echo ($page == 'Sign Out') ? 'active' : ''; ?>">
  <a href="logout.php">
    <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
    <span class="title">Sign Out</span>
  </a>
</li>

<hr>
<div class="user-info">
    <ion-icon name="person-circle-outline"></ion-icon>
    <span>
      <?php 
        echo htmlspecialchars($_SESSION['username']); 
      ?>
      (<strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>)
    </span>
  </div>

</div>

  <div class="main" id="main">
        <div class="topbar">
          <div class="left-section">
      <div class="toggle" id="toggleBtn">☰</div>
      <div class="welcome-text">
      Welcome, 
      <?php echo htmlspecialchars($_SESSION['username']); ?> 
      (<?php echo htmlspecialchars($_SESSION['role']); ?>)
    </div>
    </div>
    </div>
    
    
    <div id="content">
<?php
if ($page == "dashboard_content") {
    include 'admin_includes\dashboard_content.php';
} elseif ($page == "create_application" && $_SESSION['role'] === 'Chairperson') {
    include 'admin_includes\create_application1.php';
} elseif ($page == "all_applications" && $_SESSION['role'] === 'Chairperson') {
    include 'admin_includes\all_applications.php';
} elseif ($page == "app_form" && $_SESSION['role'] === 'Chairperson') {
    include 'includes\app_form.html';
} elseif ($page == "acc_manager") {
    include 'admin_includes\manage_account.php';
} elseif ($page == "reports") {
    include 'admin_includes\reports.php';
} elseif ($page == "departments" && $_SESSION['role'] === 'Director') {
    include 'admin_includes/director_departments.php';
} else {
    echo "<p>Page not found or access denied.</p>";
}
?>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
  const toggle = document.getElementById("toggleBtn");
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main");
  const userInfo = document.querySelector(".user-info");

  if (toggle && sidebar && main) {
    toggle.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      main.classList.toggle("active");
      toggle.classList.toggle("active");
      userInfo.classList.toggle("collapsed"); 
    });
  }

  function updateBadge() {
    fetch('get_pending_count.php')
      .then(response => response.text())
      .then(count => {
        const badge = document.querySelector('.badge');
        const manageLink = document.querySelector('a[href="manage_account.php"]');
        if (count > 0) {
          if (badge) {
            badge.textContent = count;
          } else {
            const newBadge = document.createElement('span');
            newBadge.className = 'badge';
            newBadge.textContent = count;
            manageLink.appendChild(newBadge);
          }
        } else {
          if (badge) badge.remove();
        }
      });
  }

  setInterval(updateBadge, 1000);
</script>

</body>
</html>
