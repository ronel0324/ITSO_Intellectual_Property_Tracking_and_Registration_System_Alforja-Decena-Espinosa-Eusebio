<?php
include 'db_connect.php';

// ✅ Format date or show "N/A"
function formatDateOrNA($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '1970-01-01') {
        return "<span style='color: gray;'>N/A</span>";
    }
    return date('M d, Y', strtotime($date));
}

// ✅ Add expiration color coding
function getExpirationClass($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '1970-01-01') {
        return 'na';
    }

    $today = new DateTime();
    $expiration = new DateTime($date);
    $diff = $today->diff($expiration)->days;
    $isExpired = $expiration < $today;

    if ($isExpired) return 'expired';
    elseif ($diff <= 30) return 'expiring30';
    elseif ($diff <= 60) return 'expiring60';
}

$sql = "
  SELECT ip.*, c.campus_name 
  FROM intellectual_properties ip
  LEFT JOIN campuses c ON ip.campus_id = c.campus_id
  WHERE ip.status = 'Completed'
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completed Applications</title>

  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

  <style>
    .expired { color: red; font-weight: 600; }
    .expiring30 { color: #ff6600b5; font-weight: 600; }
    .expiring60 { color: #acac00a8; font-weight: 600; }
    .na { color: gray; font-weight: 600; }
    .no-data { text-align: center; margin: 20px 0; color: #777; }

    .tracking-id {
      cursor: pointer;
      color: #0077ff;
      transition: 0.2s;
    }
    .tracking-id:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="table-container table-completed">
  <div class="table-header">
    <div>Title</div>
    <div>Classification</div>
    <div>Status</div>
    <div>Campus</div>
    <div>Submitted to ITSO</div>
    <div>Submitted to IPOPHIL</div>
    <div>Expiration Date</div>
  </div>

  <div class="table-body">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $class = getExpirationClass($row['expiration_date']);
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
                    <span class='status-badge status-completed'>{$row['status']}</span>
                </div>
                <div data-label='Campus'>{$row['campus_name']}</div>
                <div data-label='Date Submitted to ITSO'>" . formatDateOrNA($row['date_submitted_to_itso']) . "</div>
                <div data-label='Date Submitted to IPOPHIL'>" . formatDateOrNA($row['date_submitted_to_ipophil']) . "</div>
                <div data-label='Expiration Date' class='{$class}'>" . formatDateOrNA($row['expiration_date']) . "</div>
            </div>";
        }
    } else {
        echo "<p class='no-data'>No completed applications</p>";
    }
    ?>
  </div>
</div>

<script>
function copyToClipboard(text, element) {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(() => showCopiedFeedback(element));
  } else {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand("copy");
      showCopiedFeedback(element);
    } catch (err) {
      console.error("Fallback copy failed:", err);
    }
    document.body.removeChild(textarea);
  }
}

function showCopiedFeedback(element) {
  const originalHTML = element.innerHTML;
  element.innerHTML = "Copied!";
  element.style.color = "green";
  setTimeout(() => {
    element.innerHTML = originalHTML;
    element.style.color = "";
  }, 1500);
}
</script>
</body>
</html>
