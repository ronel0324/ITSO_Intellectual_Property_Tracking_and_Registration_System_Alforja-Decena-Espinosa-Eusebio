<?php
include 'db_connect.php';
$sql = "
  SELECT ip.*, c.campus_name, d.department_name
  FROM intellectual_properties ip
  LEFT JOIN campuses c ON ip.campus_id = c.campus_id
  LEFT JOIN departments d ON ip.department_id = d.department_id
  WHERE ip.status = 'Pending'
";
$result = $conn->query($sql);
?>

<div class="table-container">
  <!-- Table Header -->
  <div class="table-header">
    <div>Title</div>
    <div>Classification</div>
    <div>Status</div>
    <div>Campus</div>
    <div>Department</div>
    <div></div>
  </div>

  <!-- Table Body -->
  <div class="table-body">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tracking = htmlspecialchars($row['tracking_id'], ENT_QUOTES);
            echo "
            <div class='table-row-card'>
                <div data-label='Title'>
                    <strong>{$row['title']}</strong><br>
                    <small class='tracking-id' onclick='copyToClipboard(\"$tracking\", this)'>
                        <ion-icon name=\"copy-outline\" style=\"font-size: 12px;\"></ion-icon>
                        {$row['tracking_id']}
                    </small>
                </div>
                <div data-label='Classification'>{$row['classification']}</div>
                <div data-label='Status'>
                    <span class='status-badge status-pending'>{$row['status']}</span>
                </div>
                <div data-label='Campus'>{$row['campus_name']}</div>
                
                <div data-label='Action'>
                    <form method='POST' action='update_status.php' style='display:inline;'>
                        <input type='hidden' name='ip_id' value='{$row['ip_id']}'>
                        <input type='hidden' name='new_status' value='Ongoing'>
                        <!-- redirect pabalik sa buong layout -->
                        <input type='hidden' name='source' value='admin_dashboard.php?page=all_applications'>
                        <button type='submit' class='accept-btn'> <ion-icon name='checkmark-circle-outline'></ion-icon> </button>
                    </form>

                    <form method='POST' action='delete.php' onsubmit='return confirm(\"Delete this record?\")' style='display:inline;'>
                        <input type='hidden' name='ip_id' value='{$row['ip_id']}'>
                        <button type='submit' class='delete-btn'> <ion-icon name='trash-outline'></ion-icon> </button>
                    </form>";

            if (!empty($row['remarks'])) {
                echo "<p class='remarks'>⚠ {$row['remarks']}</p>";
            }

            echo "</div>
            </div>";
        }
    } else {
        echo "<p class='no-data'>No pending applications</p>";
    }
    ?>
  </div>
</div>
