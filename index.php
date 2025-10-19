<?php
session_start();

include 'db_connect.php';
include 'functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'coord_dashboard';

if (!isset($_SESSION['department_id'])) {
    echo "<script>
        alert('Unauthorized access!');
        window.location.href = 'login.php';
    </script>";
    exit();
}

$department_id = $_SESSION['department_id'];


$sql = "SELECT * FROM intellectual_properties WHERE department_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $department_id);
$stmt->execute();
$result = $stmt->get_result();

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
    <title>Coordinator Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link rel="stylesheet" href="assets/css/coord_style.css">
    <link rel="icon" type="image/png" href="assets/ITSO.png">  

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>
<body>

    <div class="navigation" id="sidebar">
            <ul>
                <li class="logo">
                    <img src="assets/imgs/itsolog.png" alt="logos">
                </li>

                <li class="<?php echo ($page == 'coord_dashboard') ? 'active' : ''; ?>">
                    <a href="index.php?page=coord_dashboard">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                

                <li class="<?php echo ($page == 'coord_content') ? 'active' : ''; ?>">
                    <a href="index.php?page=coord_content">
                        <span class="icon">
                            <ion-icon name="file-tray-full-outline"></ion-icon>
                        </span>
                        <span class="title">Intellectual Properties</span>
                    </a>
                </li>

                <li class="<?php echo ($page == 'create_application') ? 'active' : ''; ?>">
                    <a href="index.php?page=create_application">
                        <span class="icon">
                            <ion-icon name="create-outline"></ion-icon>
                        </span>
                        <span class="title">Create Application</span>
                    </a>
                </li>

                <li class="<?php echo ($page == 'app_form') ? 'active' : ''; ?>">
                    <a href="index.php?page=app_form">
                        <span class="icon">
                            <ion-icon name="apps-outline"></ion-icon>
                        </span>
                        <span class="title">Application Form</span>
                    </a>
                </li>
            
                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>

                <hr><br>

                <div class="user-info">
    <ion-icon name="person-circle-outline"></ion-icon>
    <span>
      <?php 
        echo htmlspecialchars($_SESSION['username']); 
      ?>
      (<strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>)
    </span>
  </div>
            </ul>
</div>

    <div class="main" id="mainContent" style="flex: 1; overflow: auto; padding: 20px; margin-top: -15px;">
    <div class="topbar">
    <div class="toggle" id="toggleSidebarBtn">
        <ion-icon name="menu-outline"></ion-icon>
    </div>



</div>
        <div id="content">
            <?php
            if ($page == "coord_dashboard") {
                include 'user_includes\coord_dashboard.php';
            } elseif ($page == "coord_content") {
                include 'user_includes\coord_content.php';
            } elseif ($page == "create_application") {
                include 'user_includes\create_application.php';
            } elseif ($page == "app_form") {
                include 'includes\app_form.html';
            } else {
                echo "<p>Page not found.</p>";
            }
            ?>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.querySelector(".toggle");
  const navigation = document.querySelector(".navigation");
  const mainContent = document.querySelector(".main");
  const userInfo = document.querySelector(".user-info");

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      navigation.classList.toggle("active");
      mainContent.classList.toggle("active");
      userInfo.classList.toggle("collapsed");
    });
  }

  /*notification bell coord_content */
  
  const notifBell = document.getElementById('notifBell');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifCount = document.getElementById('notifCount');
  const notifList = document.getElementById('notifList');

  async function loadNotifications() {
    try {
      const res = await fetch('user_includes/notifications.php?ajax=1');
      const data = await res.json();
      const unreadCount = data.filter(n => n.is_read === 0).length;

      if (unreadCount > 0) {
        notifCount.textContent = unreadCount;
        notifCount.style.display = 'inline';
      } else {
        notifCount.style.display = 'none';
      }

      notifList.innerHTML = data.map(n => `
        <li style="padding:6px; border-bottom:1px solid #ccc;">
          <strong>${n.title}</strong><br>
          <span style="font-size:12px;">${n.message}</span><br>
          <small>${n.time}</small>
        </li>
      `).join('');
    } catch (err) {
      console.error('Notification load failed:', err);
    }
  }

  notifBell?.addEventListener('click', async () => {
    if (!notifDropdown.classList.contains('open')) {
      try {
        const res = await fetch('user_includes/notifications.php?mark_read=1');
        const data = await res.json();
        const unreadCount = data.filter(n => n.is_read === 0).length;

        if (unreadCount > 0) {
          notifCount.textContent = unreadCount;
          notifCount.style.display = 'inline';
        } else {
          notifCount.style.display = 'none';
        }

        notifList.innerHTML = data.map(n => `
          <li style="padding:6px; border-bottom:1px solid #ccc;">
            <strong>${n.title}</strong><br>
            <span style="font-size:12px;">${n.message}</span><br>
            <small>${n.time}</small>
          </li>
        `).join('');
      } catch (err) {
        console.error('Error marking notifications as read:', err);
      }
    }
    notifDropdown.classList.toggle('open');
  });

  function updateStatuses() {
    loadNotifications();
  }

  setInterval(updateStatuses, 15000);
  loadNotifications();
});


</script>
</body>
</html>
