<?php
include 'db_connect.php';

// Format date or show "N/A"
function formatDateOrNA($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '1970-01-01') {
        return "<span style='color: gray;'>N/A</span>";
    }
    return date('M d, Y', strtotime($date));
}

// Add expiration color coding
function getExpirationClass($date) {
    if (empty($date) || $date === '0000-00-00' || $date === '1970-01-01') return 'na';
    $today = new DateTime();
    $expiration = new DateTime($date);
    $diff = $today->diff($expiration)->days;
    $isExpired = $expiration < $today;
    if ($isExpired) return 'expired';
    elseif ($diff <= 30) return 'expiring30';
    elseif ($diff <= 60) return 'expiring60';
}

function calculateExpirationDate($classification, $dateSubmitted) {
    if (empty($dateSubmitted) || $dateSubmitted === '0000-00-00') return null;

    $yearsToAdd = 0;
    switch (strtolower($classification)) {
        case 'copyright': $yearsToAdd = 50; break;
        case 'patent': $yearsToAdd = 20; break;
        case 'trademark': $yearsToAdd = 10; break;
        // add other classifications as needed
        default: $yearsToAdd = 0;
    }

    if ($yearsToAdd > 0) {
        return date('Y-m-d', strtotime("+$yearsToAdd years", strtotime($dateSubmitted)));
    }
    return null;
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
/* Table styles */
.expired { color: red; font-weight: 600; }
.expiring30 { color: #ff6600b5; font-weight: 600; }
.expiring60 { color: #acac00a8; font-weight: 600; }
.na { color: gray; font-weight: 600; }
.no-data { text-align: center; margin: 20px 0; color: #777; }

.tracking-id { cursor: pointer; color: #0077ff; transition: 0.2s; }
.tracking-id:hover { text-decoration: underline; }

/* Modal styles */
#ipophilModal {
  display: none;
  position: fixed;
  top:0; left:0;
  width:100%; height:100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 10px;
}

#ipophilModal .modal-content {
  background: #fff;
  border-radius: 10px;
  padding: 25px 30px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  text-align: center;
  animation: fadeIn 0.3s ease;
}

#ipophilModal h3 { margin-bottom: 20px; font-size: 20px; color: #333; }
.modal-input { width:100%; padding:10px; font-size:16px; border:1px solid #ccc; border-radius:6px; margin-bottom:20px; box-sizing:border-box; }
.modal-buttons { display:flex; justify-content:flex-end; gap:10px; }
.modal-btn { padding:8px 18px; font-size:14px; border:none; border-radius:6px; cursor:pointer; transition: all 0.2s ease; }
.cancel-btn { background:#f0f0f0; color:#333; }
.cancel-btn:hover { background:#e0e0e0; }
.save-btn { background:#0077ff; color:#fff; }
.save-btn:hover { background:#005fcc; }

@keyframes fadeIn { from {opacity:0; transform:translateY(-20px);} to {opacity:1; transform:translateY(0);} }

@media (max-width: 500px) {
  #ipophilModal .modal-content { padding:20px; }
  .modal-buttons { flex-direction: column; }
  .modal-btn { width:100%; }
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
    <div>Action</div>
  </div>

  <div class="table-body">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date_ipophil = $row['date_submitted_to_ipophil'];
$expiration = $row['expiration_date'];

// If expiration is empty, calculate from classification + IPOPHIL date
if (empty($expiration) || $expiration === '0000-00-00') {
    if (!empty($date_ipophil) && $date_ipophil !== '0000-00-00') {
        $expiration = calculateExpirationDate($row['classification'], $date_ipophil);
    } else {
        $expiration = null; // still show N/A
    }
}

$class = getExpirationClass($expiration);
            $tracking = htmlspecialchars($row['tracking_id'], ENT_QUOTES);
            $date_ipophil = $row['date_submitted_to_ipophil'];
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
                <div data-label='Date Submitted to IPOPHIL'>" . formatDateOrNA($date_ipophil) . "</div>
                <div data-label='Expiration Date' class='{$class}'>" . formatDateOrNA($expiration) . "</div>
                <div data-label='Action'>
                  <button onclick='openIPOPHILModal({$row['ip_id']}, \"{$date_ipophil}\")' 
                          title='" . (empty($date_ipophil) ? "Add Date" : "Update Date") . "' 
                          style='background:none; border:none; cursor:pointer; font-size:18px;'>
                      <ion-icon name='create-outline'></ion-icon>
                  </button>
              </div>
            </div>";
        }
    } else {
        echo "<p class='no-data'>No completed applications</p>";
    }
    ?>
  </div>
</div>

<!-- Modal -->
<div id="ipophilModal">
  <div class="modal-content">
    <h3>Set Date Submitted to IPOPHIL</h3>
    <input type="date" id="ipophilDateInput" class="modal-input">
    <input type="hidden" id="ipophilIpId">
    <div class="modal-buttons">
      <button class="modal-btn cancel-btn" onclick="closeIPOPHILModal()">Cancel</button>
      <button class="modal-btn save-btn" onclick="saveIPOPHILDate()">Save</button>
    </div>
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
    try { document.execCommand("copy"); showCopiedFeedback(element); }
    catch (err) { console.error("Fallback copy failed:", err); }
    document.body.removeChild(textarea);
  }
}
function showCopiedFeedback(element) {
  const originalHTML = element.innerHTML;
  element.innerHTML = "Copied!";
  element.style.color = "green";
  setTimeout(() => { element.innerHTML = originalHTML; element.style.color = ""; }, 1500);
}

// Modal functions
function openIPOPHILModal(ipId, currentDate) {
  document.getElementById('ipophilIpId').value = ipId;
  document.getElementById('ipophilDateInput').value = currentDate && currentDate !== '0000-00-00' ? currentDate : '';
  document.getElementById('ipophilModal').style.display = 'flex';
}
function closeIPOPHILModal() {
  document.getElementById('ipophilModal').style.display = 'none';
}
function saveIPOPHILDate() {
  const ipId = document.getElementById('ipophilIpId').value;
  const date = document.getElementById('ipophilDateInput').value;
  if (!date) return alert('Please select a date');
  fetch('admin_includes/update_ipophil_date.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ ip_id: ipId, date_submitted_to_ipophil: date })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) location.reload();
    else alert('Failed to update date');
  });
}
</script>

</body>
</html>
