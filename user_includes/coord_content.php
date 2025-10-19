<?php
    include 'db_connect.php';

// Regular page: load intellectual properties with optional filters
$role = $_SESSION['role'] ?? '';
$department_id = $_SESSION['department_id'] ?? '';
$campus_id = $_SESSION['campus_id'] ?? '';

$conditions = [];
$params = [];
$types = "";

// Apply filters if coordinator
if ($role === 'Coordinator') {
    if (!empty($department_id)) {
        $conditions[] = "ip.department_id = ?";
        $types .= "i";
        $params[] = $department_id;
    }
    if (!empty($campus_id)) {
        $conditions[] = "ip.campus_id = ?";
        $types .= "i";
        $params[] = $campus_id;
    }
}

$where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Optional status filter
$status_filter = $_GET['status'] ?? '';

if (!empty($status_filter)) {
    if ($status_filter === 'For Revision') {
        if (!empty($where)) {
            $where .= " AND ip.status = 'Pending' AND (ip.remarks IS NOT NULL AND ip.remarks != '')";
        } else {
            $where = "WHERE ip.status = 'Pending' AND (ip.remarks IS NOT NULL AND ip.remarks != '')";
        }
    } else {
        if (!empty($where)) {
            $where .= " AND ip.status = ?";
        } else {
            $where = "WHERE ip.status = ?";
        }
        $params[] = $status_filter;
        $types .= "s";
    }
}

if (empty($where) && $role !== 'Admin') {
    $where = "WHERE 1=0";
}

$query = "
SELECT ip.*, c.campus_name, d.department_name
FROM intellectual_properties ip
LEFT JOIN campuses c ON ip.campus_id = c.campus_id
LEFT JOIN departments d ON ip.department_id = d.department_id
$where
";

$stmt = $conn->prepare($query);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$campus_name_display = "N/A";
$department_display = "N/A";

if ($result->num_rows > 0) {
    $first_row = $result->fetch_assoc();
    $campus_name_display = $first_row['campus_name'] ?? "N/A";
    $department_display = $first_row['department_name'] ?? "N/A";
    $result->data_seek(0);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Coordinator Dashboard</title>
<link rel="icon" type="image/png" href="ITSO.png">
<link rel="stylesheet" href="assets/css/coord_content.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</head>

<body>

<div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 5px; position: relative;">
    <!-- Filter Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
      class="bi bi-funnel" viewBox="0 0 16 16" id="filterIcon"
      style="position: absolute; top: 60px; right: 40px; color: #28453D; cursor: pointer; z-index: 999;">
      <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 
      0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 
      1A.5.5 0 0 1 6 14.5V8.692L1.628 
      3.834A.5.5 0 0 1 1.5 3.5zm1 
      .5v1.308l4.372 4.858A.5.5 0 0 
      1 7 8.5v5.306l2-.666V8.5a.5.5 
      0 0 1 .128-.334L13.5 
      3.308V2z"/>
    </svg>

    <!-- 🧩 Filter List -->
    <div id="filterMenu" style="
      display: none;
      position: absolute;
      top: 55px;
      right: 35px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      z-index: 1000;
      width: 160px;
    ">
      <div class='filter-option' data-value="">All</div>
      <div class='filter-option' data-value="For Revision">For Revision</div>
      <div class='filter-option' data-value="Pending">Pending</div>
      <div class='filter-option' data-value="Ongoing">Ongoing</div>
      <div class='filter-option' data-value="Completed">Completed</div>
    </div>
</div>

<div class="search-bell">
<input type="text" id="searchInput" placeholder="Search applications...">
<!-- 🔔 Notification Bell -->
<ion-icon name="notifications-outline" id="notifBell" style="font-size: 26px; cursor: pointer; color:#28453D; margin-left: 50px;"></ion-icon>
<span id="notifCount" class="notif-count">0</span>

<div id="notifDropdown" class="notif-dropdown">
    <h4>Notifications</h4>
    <div id="notifList">
        <div class="notif-empty">Loading...</div>
</div>
</div>
</div>


<div class="table-container" style="position: relative;">
  <h2>All Intellectual Properties (LSPU - <?php echo htmlspecialchars($campus_name_display); ?> | <?php echo htmlspecialchars($department_display); ?>)</h2>
  <div style="max-height: 80vh; overflow-y: auto;">
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Authors</th>
        <th>Applicant</th>
        <th>Classification</th>
        <th>Status</th>
        <th>Date Submitted to ITSO</th>
        <th>Date Submitted to IPOPHIL</th>
        <th>Expiration Date</th>
        <th>Remarks</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <?php
        $status_class = '';
        switch (strtolower($row['status'])) {
          case 'completed': $status_class = 'status-completed'; break;
          case 'pending': $status_class = 'status-pending'; break;
          case 'ongoing': $status_class = 'status-ongoing'; break;
        }
      ?>
      <tr data-ipid="<?= $row['ip_id']; ?>">
        <td>
          <div style="font-weight: 600; color: #28453D;"><?= htmlspecialchars($row['title']); ?></div>
          <div style="font-size: 12px; color: #6b7280; margin-top: 3px;">
            <span style="cursor: pointer; color: #0bb5b5c3; font-weight: 500;" onclick="copyTrackingId(this, '<?= htmlspecialchars($row['tracking_id']); ?>')">
              <ion-icon name="copy-outline" style="font-size: 13px; vertical-align: middle;"></ion-icon>
              <span><?= htmlspecialchars($row['tracking_id']); ?></span>
            </span>
          </div>
        </td>
        <td><?= htmlspecialchars($row['authors']); ?></td>
        <td><?= htmlspecialchars($row['applicant_name']); ?></td>
        <td><?= htmlspecialchars($row['classification']); ?></td>
        <td><span class="status-badge <?= $status_class; ?>"><?= htmlspecialchars($row["status"]); ?></span></td>
        <td><?= !empty($row['date_submitted_to_itso']) ? date('F d, Y', strtotime($row['date_submitted_to_itso'])) : 'No Date'; ?></td>
        <td><?= !empty($row['date_submitted_to_ipophil']) && $row['date_submitted_to_ipophil'] !== "0000-00-00" ? date('F d, Y', strtotime($row['date_submitted_to_ipophil'])) : 'N/A'; ?></td>
        <td>
          <?php
            if (!empty($row['expiration_date']) && $row['expiration_date'] !== '0000-00-00') {
              $exp_date = strtotime($row['expiration_date']);
              $today = time();
              $diff_days = ($exp_date - $today) / (60*60*24);
              $color_class = ($diff_days < 0) ? 'expired' : (($diff_days <= 180) ? 'expiring-soon' : (($diff_days <= 365) ? 'expiring-year' : ''));
              echo "<span class='$color_class'>" . date('F d, Y', $exp_date) . "</span>";
            } else {
              echo 'N/A';
            }
          ?>
        </td>
        <td>
          <?php if (!empty(trim($row['remarks']))): ?>
            <ion-icon name="warning-outline" style="font-size: 14px; color: #eab308; vertical-align: middle; margin-right: 5px;"></ion-icon>
            <?= htmlspecialchars($row['remarks']); ?>
          <?php else: ?>
            <span style="color: #9ca3af; font-style: italic;">No remarks</span>
          <?php endif; ?>
        </td>
        <td>
          <button onclick="openUpdateModal(
            <?= (int)$row['ip_id']; ?>,
            '<?= addslashes($row['classification']); ?>',
            '<?= addslashes($row['endorsement_letter']); ?>',
            '<?= addslashes($row['application_form']); ?>',
            '<?= addslashes($row['application_fee']); ?>',
            '<?= addslashes($row['issued_certificate']); ?>',
            '<?= addslashes($row['project_file']); ?>'
          )" class="update-badge"><ion-icon name="create"></ion-icon></button>

          <button onclick="openViewModal(
            '<?= addslashes($row['tracking_id']); ?>',
            '<?= addslashes($row['title']); ?>',
            '<?= addslashes($row['authors']); ?>',
            '<?= addslashes($row['applicant_name']); ?>',
            '<?= addslashes($row['classification']); ?>',
            '<?= addslashes($row['status']); ?>',
            '<?= addslashes($row['campus_name']); ?>',
            '<?= addslashes($row['remarks']); ?>',
            '<?= addslashes($row['endorsement_letter']); ?>',
            '<?= addslashes($row['application_form']); ?>',
            '<?= addslashes($row['application_fee']); ?>',
            '<?= addslashes($row['issued_certificate']); ?>',
            '<?= addslashes($row['project_file']); ?>'
          )" class="view-badge"><ion-icon name="eye"></ion-icon></button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal">
  <div class="modal-content modern-modal">
    <div class="modal-header">
      <h2>Update Files</h2>
      <button class="close-btn" onclick="closeModal()"><ion-icon name="close-outline"></ion-icon></button>
    </div>

    <form id="updateForm" action="process_update.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" id="ip_id" name="ip_id">
      <input type="hidden" id="classification" name="classification">
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Endorsement Letter</label>
            <div id="endorsement_name" class="file-name-display"></div>
            <input type="file" name="endorsement_letter" class="file-input">
          </div>
          <div class="form-group">
            <label>Application Form</label>
            <div id="appform_name" class="file-name-display"></div>
            <input type="file" name="application_form" class="file-input">
          </div>
          <div class="form-group">
            <label>Application Fee</label>
            <div id="appfee_name" class="file-name-display"></div>
            <input type="file" name="application_fee" class="file-input">
          </div>
          <div class="form-group">
            <label>Issued Certificate</label>
            <div id="issuedcert_name" class="file-name-display"></div>
            <input type="file" name="issued_certificate" class="file-input">
          </div>
          <div class="form-group">
            <label>Project File</label>
            <div id="project_name" class="file-name-display"></div>
            <input type="file" name="project_file" class="file-input">
          </div>
          <div class="form-group full-width">
            <label>Date Submitted to IPOPHIL</label>
            <input type="date" name="date_submitted_to_ipophil" class="date-input">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
        <button type="submit" class="submit-btn">Update Files</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
  <div class="modal-content modern-modal">
    <div class="modal-header">
      <h2>View Application</h2>
      <button class="close-btn" onclick="closeViewModal()"><ion-icon name="close-outline"></ion-icon></button>
    </div>
    <div class="modal-body">
      <div class="view-grid">
        <div class="view-item"><label>Tracking ID</label><p id="view_tracking_id"></p></div>
        <div class="view-item"><label>Title</label><p id="view_title"></p></div>
        <div class="view-item"><label>Authors</label><p id="view_authors"></p></div>
        <div class="view-item"><label>Applicant</label><p id="view_applicant"></p></div>
        <div class="view-item"><label>Classification</label><p id="view_classification"></p></div>
        <div class="view-item"><label>Status</label><p id="view_status"></p></div>
        <div class="view-item"><label>Campus</label><p id="view_campus"></p></div>
        <div class="view-item full-width"><label>Remarks</label><p id="view_remarks"></p></div>
        <hr style="grid-column: span 2; border: 1px solid rgba(255,255,255,0.2); margin: 15px 0;">
        <div class="view-item full-width"><label>Endorsement Letter</label><p id="view_endorsement"></p></div>
        <div class="view-item full-width"><label>Application Form</label><p id="view_appform"></p></div>
        <div class="view-item full-width"><label>Application Fee</label><p id="view_appfee"></p></div>
        <div class="view-item full-width"><label>Issued Certificate</label><p id="view_issuedcert"></p></div>
        <div class="view-item full-width"><label>Project File</label><p id="view_projectfile"></p></div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="cancel-btn" onclick="closeViewModal()">Close</button>
    </div>
  </div>
</div>

<!-- Floating Chat Button -->
<div id="chatWidget">
    <div id="chatToggle">💬</div>
    <div id="chatBox">
        <div id="chatHeader">
            <span>ITSO Tracker</span>
            <span id="closeChat" style="cursor:pointer;">&times;</span>
        </div>
        <div id="chatMessages"></div>
        <div id="chatFAQButtons" style="margin:10px 0;">
    <button onclick="sendFAQ('how_to_register')">How to Register</button>
    <button onclick="sendFAQ('track_status')">Track My IP Status</button>
</div>

        <div id="chatInputContainer">
            <input type="text" id="chatInput" placeholder="Enter Tracking ID here...">
            <button id="sendChat"><ion-icon name="send-outline"></ion-icon></button>
        </div>
    </div>
</div>
</body>

<script>
// ------------ Utility helpers & modal functions ------------
function openUpdateModal(ip_id, classification, endorsement, appform, appfee, issuedcert, projectfile) {
  const ipIdEl = document.getElementById('ip_id');
  const classificationEl = document.getElementById('classification');
  if (ipIdEl) ipIdEl.value = ip_id;
  if (classificationEl) classificationEl.value = classification;

  document.getElementById('endorsement_name').innerHTML = endorsement && endorsement !== '0' && endorsement !== 'X' ? "Current file: <b>" + endorsement + "</b>" : "<span style='color:#9ca3af;'>No file uploaded</span>";
  document.getElementById('appform_name').innerHTML = appform && appform !== '0' && appform !== 'X' ? "Current file: <b>" + appform + "</b>" : "<span style='color:#9ca3af;'>No file uploaded</span>";
  document.getElementById('appfee_name').innerHTML = appfee && appfee !== '0' && appfee !== 'X' ? "Current file: <b>" + appfee + "</b>" : "<span style='color:#9ca3af;'>No file uploaded</span>";
  document.getElementById('issuedcert_name').innerHTML = issuedcert && issuedcert !== '0' && issuedcert !== 'X' ? "Current file: <b>" + issuedcert + "</b>" : "<span style='color:#9ca3af;'>No file uploaded</span>";
  document.getElementById('project_name').innerHTML = projectfile && projectfile !== '0' && projectfile !== 'X' ? "Current file: <b>" + projectfile + "</b>" : "<span style='color:#9ca3af;'>No file uploaded</span>";

  const updateModal = document.getElementById('updateModal');
  if (updateModal) updateModal.style.display = 'flex';
}

function closeModal() {
  const updateModal = document.getElementById('updateModal');
  if (updateModal) updateModal.style.display = 'none';
}

function openViewModal(tracking_id, title, authors, applicant, classification, status, campus, remarks, endorsement, appform, appfee, issuedcert, projectfile) {
  document.getElementById('view_tracking_id').textContent = tracking_id || '';
  document.getElementById('view_title').textContent = title || '';
  document.getElementById('view_authors').textContent = authors || '';
  document.getElementById('view_applicant').textContent = applicant || '';
  document.getElementById('view_classification').textContent = classification || '';
  document.getElementById('view_status').textContent = status || '';
  document.getElementById('view_campus').textContent = campus || '';
  document.getElementById('view_remarks').textContent = remarks || '';

  document.getElementById('view_endorsement').innerHTML = getFileLink(endorsement);
  document.getElementById('view_appform').innerHTML = getFileLink(appform);
  document.getElementById('view_appfee').innerHTML = getFileLink(appfee);
  document.getElementById('view_issuedcert').innerHTML = getFileLink(issuedcert);
  document.getElementById('view_projectfile').innerHTML = getFileLink(projectfile);

  const viewModal = document.getElementById('viewModal');
  if (viewModal) viewModal.style.display = 'flex';
}

function closeViewModal() {
  const viewModal = document.getElementById('viewModal');
  if (viewModal) viewModal.style.display = 'none';
}

function getFileLink(filename) {
  if (filename && filename !== 'X' && filename !== '0') {
    return `<a href="uploads/${filename}" target="_blank" style="color:#fff;text-decoration:underline;">${filename}</a>`;
  } else {
    return `<span style="color:#ccc;">No file uploaded</span>`;
  }
}

// copy tracking id
function copyTrackingId(element, trackingId) {
  if (!navigator.clipboard) return;
  navigator.clipboard.writeText(trackingId).then(() => {
    let copiedText = document.createElement('span');
    copiedText.textContent = " Copied!";
    copiedText.style.color = "#16a34a";
    copiedText.style.fontSize = "12px";
    copiedText.style.marginLeft = "5px";
    copiedText.style.fontWeight = "500";
    element.parentNode.appendChild(copiedText);
    setTimeout(() => copiedText.remove(), 1500);
  }).catch(()=>{  });
}

const searchInput = document.getElementById("searchInput");
if (searchInput) {
  searchInput.addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("table tbody tr");
    rows.forEach(row => {
      let text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });
}

const updateForm = document.getElementById("updateForm");
if (updateForm) {
  updateForm.addEventListener("submit", function (e) {
    const maxSize = 100 * 1024 * 1024;
    let totalSize = 0;
    const fileInputs = this.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
      if (input.files.length > 0) totalSize += input.files[0].size;
    });
    if (totalSize > maxSize) {
      alert("Total file size exceeds 100MB. Please upload smaller files.");
      e.preventDefault();
    }
  });
}

// Chatbot UI guards (attach only when present)
// Chat Widget Functionality
const chatToggle = document.getElementById('chatToggle');
const chatBox = document.getElementById('chatBox');
const closeChat = document.getElementById('closeChat');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const sendChat = document.getElementById('sendChat');

// Toggle chat open/close
chatToggle.addEventListener('click', () => chatBox.style.display = 'flex');
closeChat.addEventListener('click', () => chatBox.style.display = 'none');

// Function to add chat bubbles
function addBubble(message, type) {
    if(type === 'system') {
        const typingBubble = document.createElement('div');
        typingBubble.className = 'bubble systemBubble typing';

        const avatar = document.createElement('img');
        avatar.src = 'assets/ITSO.png';
        avatar.alt = 'bot';

        const dots = document.createElement('div');
        dots.className = 'typing-dots';
        dots.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';

        typingBubble.appendChild(avatar);
        typingBubble.appendChild(dots);
        chatMessages.appendChild(typingBubble);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        setTimeout(() => {
            typingBubble.remove();
            const bubbleContainer = document.createElement('div');
            bubbleContainer.className = 'bubble systemBubble';

            const text = document.createElement('div');
            text.innerText = message;
            text.style.padding = '8px 12px';
            text.style.borderRadius = '15px';
            text.style.maxWidth = '70%';
            text.style.wordWrap = 'break-word';
            text.style.backgroundColor = '#d4edda';
            text.style.color = '#155724';

            bubbleContainer.appendChild(avatar);
            bubbleContainer.appendChild(text);

            chatMessages.appendChild(bubbleContainer);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 1000);

    } else {
        const bubbleContainer = document.createElement('div');
        bubbleContainer.className = 'bubble userBubble';

        const avatar = document.createElement('img');
        avatar.src = 'assets/imgs/user.png';
        avatar.alt = 'user';

        const text = document.createElement('div');
        text.innerText = message;
        text.style.padding = '8px 12px';
        text.style.borderRadius = '15px';
        text.style.maxWidth = '70%';
        text.style.wordWrap = 'break-word';
        text.style.backgroundColor = '#d1e7ff';
        text.style.color = '#000';

        bubbleContainer.appendChild(text);
        bubbleContainer.appendChild(avatar);

        chatMessages.appendChild(bubbleContainer);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Function to send message to backend
function sendMessage() {
    const input = chatInput.value.trim();
    if(!input) {
        addBubble("Please enter an Tracking ID", "error");
        return;
    }

    addBubble("You: " + input, "user");
    chatInput.value = '';

        fetch('itsobot.php?tracking_id=' + encodeURIComponent(input))
            .then(res => res.json())
            .then(data => {
                if(data.success) addBubble(data.message, "system");
                else addBubble(data.message, "error");
            })
            .catch(err => addBubble("Error: Unable to fetch data.", "error"));
}

// FAQ Button Handlers
function sendFAQ(type) {
    let message = "";

    switch(type) {
        case 'how_to_register':
            message = "To apply for an Intellectual Property, gather your documents: •Application-Form •Project-File •Endorsement-Letter, and submit it to Create Application.";
            break;
        case 'track_status':
            message = "To track your status, enter your Tracking ID in the chat input below and press Send.";
            chatBox.style.display = "flex";
            chatInput.focus();
            chatInput.placeholder = "Enter your IP ID here...";
            chatInput.value = "";
            break;
    }

    addBubble(message, "system");
}

sendChat.addEventListener('click', sendMessage);

chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

// Show welcome message + prompt when chat opens
function showWelcome() {
    addBubble("Welcome to LSPU-ITSO. We are here to help you.", "system");

    setTimeout(() => {
        addBubble("Please enter your Tracking ID below to track your application:", "system");
        chatInput.focus();
    }, 1000);
}

chatToggle.addEventListener('click', () => {
    chatBox.style.display = 'flex';
    showWelcome();
});

// ------------- Close modals  -------------
window.addEventListener('click', function(event) {
  const updateModal = document.getElementById('updateModal');
  const viewModal = document.getElementById('viewModal');
  if (updateModal && event.target === updateModal) closeModal();
  if (viewModal && event.target === viewModal) closeViewModal();
});



document.addEventListener('DOMContentLoaded', () => {
  const icon = document.getElementById('filterIcon');
  const menu = document.getElementById('filterMenu');
  const options = document.querySelectorAll('.filter-option');

  // Toggle visibility of filter menu
  icon.addEventListener('click', (e) => {
    e.stopPropagation();
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  });

  // Hide when clicking outside
  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && e.target !== icon) {
      menu.style.display = 'none';
    }
  });

  // Filter action
  options.forEach(opt => {
    opt.addEventListener('click', () => {
      const value = opt.getAttribute('data-value');
      const url = new URL(window.location.href);
      if (value) {
        url.searchParams.set('status', value);
      } else {
        url.searchParams.delete('status');
      }
      window.location.href = url.toString();
    });
  });

  // Highlight current filter
  const current = new URLSearchParams(window.location.search).get('status');
  options.forEach(opt => {
    if (opt.getAttribute('data-value') === current) {
      opt.classList.add('active');
    }
  });
});

function updateStatuses() {
  fetch('user_includes/fetch_status.php')
    .then(res => res.json())
    .then(rows => {
      if (rows.error) return;
      rows.forEach(row => {
        const tr = document.querySelector(`tr[data-ipid='${row.ip_id}']`);
        if (tr) {
          // ----- Update status badge -----
          const badge = tr.querySelector('.status-badge');
          if (badge) {
            badge.textContent = row.status;

            // Reset classes
            badge.classList.remove('status-pending','status-ongoing','status-completed');

            // Apply new class
            switch(row.status.toLowerCase()) {
              case 'pending': badge.classList.add('status-pending'); break;
              case 'ongoing': badge.classList.add('status-ongoing'); break;
              case 'completed': badge.classList.add('status-completed'); break;
            }
          }

          // ----- Update remarks -----
          const remarksTd = tr.querySelector('td:nth-child(9)'); // Remarks column is 9th
          if (remarksTd) {
            if (row.remarks && row.remarks.trim() !== '') {
              remarksTd.innerHTML = `<ion-icon name="warning-outline" style="font-size: 14px; color: #eab308; vertical-align: middle; margin-right: 5px;"></ion-icon>${row.remarks}`;
            } else {
              remarksTd.innerHTML = `<span style="color: #9ca3af; font-style: italic;">No remarks</span>`;
            }
          }
        }
      });
    })
    .catch(err => console.error('Error fetching status:', err));
}

updateStatuses();
setInterval(updateStatuses, 15000);

// Fetch and update unread count
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

</script>

</html>
