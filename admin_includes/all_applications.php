<!DOCTYPE html>
<html lang="en">
<head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>


<style>
.tab-content {
  transition: opacity 0.3s ease-in-out;
}
.hidden {
  display: none;
}

.tab-button {
  transition: all 0.3s ease-in-out;
}
.tab-button.active {
  background-color: #0051ff8a;
  color: #fff;
}

@supports (-webkit-clip-path: polygon(0 0, 0 100%, 100% 100%, 100% 0)) or (clip-path: polygon(0 0, 0 100%, 100% 100%, 100% 0)) {
  .arrow-tabs {
    display: flex;
    overflow-x: auto;
    width: 100%;
    justify-content: center;
  }

.arrow-tab {
  position: relative;
  padding: 14px 40px;
  text-align: center;
  color: #333;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  user-select: none;
  min-width: 150px;
  background-color: #fff;
  outline: 2px solid #3D6157;
  outline-offset: -2px;
  border: none;
  -webkit-clip-path: polygon(0% 0%, 84% 0%, 100% 50%, 84% 100%, 0% 100%, 16% 50%);
  clip-path: polygon(0% 0%, 84% 0%, 100% 50%, 84% 100%, 0% 100%, 16% 50%);
  margin-right: -20px;
}

.arrow-tab::before {
  content: "";
  position: absolute;
  inset: 0;
  border: 2px solid #3D6157;
  clip-path: inherit;
  pointer-events: none;
  border-radius: inherit;
}

.arrow-tab:hover {
  background-color: #f3f3f3;
  box-shadow: 0 0 6px rgba(61, 97, 87, 0.4);
}

.arrow-tab:nth-child(1):hover {
  background-color: #3D6157; /* light */
  box-shadow: 0 0 8px rgba(61, 97, 87, 0.6);
}

.arrow-tab:nth-child(2):hover {
  background-color: #7BA094; /* lightest */
  box-shadow: 0 0 8px rgba(123, 160, 148, 0.6);
}

.arrow-tab:nth-child(3):hover {
  background-color: #28453D; /* base (deep) */
  box-shadow: 0 0 8px rgba(40, 69, 61, 0.8);
}

.arrow-tab.active {
  background-color: #28453D;
  color: #fff;
  border-color: #28453D;
  box-shadow: 0 6px 10px rgba(40, 69, 61, 0.4);
}

/* Rounded first & last */
.arrow-tab:first-child {
  border-radius: 10px 0 0 10px;
  -webkit-clip-path: polygon(0% 0%, 84% 0%, 100% 50%, 84% 100%, 0% 100%);
  clip-path: polygon(0% 0%, 84% 0%, 100% 50%, 84% 100%, 0% 100%);
}

.arrow-tab:last-child {
  border-radius: 0 10px 10px 0;
  -webkit-clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 16% 50%);
  clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 16% 50%);
  margin-right: 0;
}

.glass-modal-overlay {
  display: none; /* start hidden */
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 10, 0.7);
  backdrop-filter: blur(10px);
  z-index: 999;
  justify-content: center;
  align-items: center;
}

.glass-modal {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255,255,255,0.25);
  backdrop-filter: blur(25px);
  border-radius: 18px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.5);
  width: 90%;
  max-width: 850px;
  padding: 30px;
  color: #fff;
  position: relative;
  animation: popIn 0.4s ease;
  overflow: hidden;
}

@keyframes popIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

.glass-modal-body {
  max-height: 75vh;
  overflow-y: auto;
  scrollbar-width: thin;
}

.close-glass {
  position: absolute;
  top: -5px;
  right: 12px;
  font-size: 1.8rem;
  color: #fff;
  cursor: pointer;
  transition: 0.3s ease;
  z-index: 10;
}

.close-glass:hover {
  color: #ffffffb8;
  transform: scale(1.2);
}

.glass-modal-body h3 {
  color: #ffffffb8;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
  font-weight: 600;
  margin-bottom: 10px;
}

.glass-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  border-radius: 12px;
  overflow: hidden;
  font-size: 0.9rem;
}

.glass-table th, .glass-table td {
  padding: 12px 10px;
  text-align: left;
}

.glass-table th {
  background: rgba(255,255,255,0.15);
  color: #ffffffb8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.glass-table tr:nth-child(even) {
  background: rgba(255,255,255,0.05);
}

.glass-table tr:hover {
  background: rgba(255,255,255,0.1);
  transition: 0.2s;
}

#scrollTopBtn {
  display: none;
  position: fixed;
  bottom: 90px;
  right: 20px;
  z-index: 99999;
  color: black;
  border: solid 1px;
  padding: 12px 14px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  transition: opacity 0.3s ease, transform 0.2s ease;
}
#scrollTopBtn:hover {
  background-color: #28453D;
  color: white;
  transform: scale(1.1);
}

</style>
</head>
<body>

 <div class="search-notif-container">
        <!-- Search Bar -->
        <div class="search-box">
            <input type="text" placeholder="Search here">
            <ion-icon name="search-outline"></ion-icon>
        </div> 

        <!-- Notification Bell -->
        <div class="notif-bell">
            <button onclick="document.getElementById('notifModal').style.display='flex'">
                <ion-icon name="notifications"></ion-icon>
                <?php if ($notif_count > 0): ?>
                    <span class="notif-count"><?= $notif_count ?></span>
                <?php endif; ?>
            </button>
        </div>
</div>

<!-- Notification Modal -->
<!-- Modern Glass Notification Modal -->
<div id="notifModal" class="glass-modal-overlay">
  <div class="glass-modal">
    <span class="close-glass" onclick="document.getElementById('notifModal').style.display='none'">&times;</span>
    <div class="glass-modal-body">

      <h3>Upcoming Expirations (within 180 days)</h3>
      <?php if (!empty($upcoming_notifications)): ?>
        <table class="glass-table">
          <tr>
            <th>Tracking ID</th>
            <th>Title</th>
            <th>Classification</th>
            <th>Expiration Date</th>
            <th>Days Left</th>
          </tr>
          <?php foreach($upcoming_notifications as $notif): ?>
            <tr>
              <td><?= htmlspecialchars($notif['tracking_id']) ?></td>
              <td><?= htmlspecialchars($notif['title']) ?></td>
              <td><?= htmlspecialchars($notif['classification']) ?></td>
              <td style="color: <?= ($notif['days_left'] <= 30) ? '#ff6b6b' : '#ffe66d' ?>; font-weight: bold;">
                <?= date("F j, Y", strtotime($notif['expiration_date'])) ?>
              </td>
              <td style="color: <?= ($notif['days_left'] <= 30) ? '#ff6b6b' : '#ffe66d' ?>; font-weight: bold;">
                <?= $notif['days_left'] ?> days
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>No upcoming expirations.</p>
      <?php endif; ?>

      <h3 style="margin-top: 25px;">Expired Applications</h3>
      <?php if (!empty($expired_notifications)): ?>
        <table class="glass-table">
          <tr>
            <th>Tracking ID</th>
            <th>Title</th>
            <th>Classification</th>
            <th>Expiration Date</th>
            <th>Days Past</th>
          </tr>
          <?php foreach($expired_notifications as $notif): ?>
            <tr>
              <td><?= htmlspecialchars($notif['tracking_id']) ?></td>
              <td><?= htmlspecialchars($notif['title']) ?></td>
              <td><?= htmlspecialchars($notif['classification']) ?></td>
              <td style="color: #ff4d4d;">
                <?= date("F j, Y", strtotime($notif['expiration_date'])) ?>
              </td>
              <td style="color: #ff4d4d;">
                <?= abs($notif['days_left']) ?> days ago
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>No expired applications.</p>
      <?php endif; ?>

    </div>
  </div>
</div>


<!-- Table Section -->
    <div class="recentEntry">
      

        
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

<!-- Section Header
<h3 class="mt-5 text-xl font-semibold text-gray-700">All Intellectual Properties Applications</h3> -->

<!-- Tab Buttons -->
<div class="arrow-tabs mt-10">
  <div class="arrow-tab active" onclick="showTab('tab-pending', this)">Pending</div>
  <div class="arrow-tab" onclick="showTab('tab-ongoing', this)">Ongoing</div>
  <div class="arrow-tab" onclick="showTab('tab-completed', this)">Completed</div>
</div>

<!-- Tab Content -->
<div class="tab-content mt-4" id="tab-pending" style="display: block;">
  <?php include 'table_pending.php'; ?>
</div>

<div class="tab-content mt-4" id="tab-ongoing" style="display: none;">
  <?php include 'table_ongoing.php'; ?>
</div>

<div class="tab-content mt-4" id="tab-completed" style="display: none;">
  <?php include 'table_completed.php'; ?>
</div>

<!-- Scroll to Top Button -->
<button id="scrollTopBtn" title="Go to top">
  <ion-icon name="caret-up-circle-outline"></ion-icon>
</button>
    
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart.js configurations
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.querySelector(".search-box input");

  function filterTables() {
    const filter = searchInput.value.toLowerCase();
    const allTabs = document.querySelectorAll(".tab-content");

    allTabs.forEach(tab => {
      const rows = tab.querySelectorAll(".table-row-card");
      const noData = tab.querySelector(".no-data");
      let visibleCount = 0;

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(filter);
        row.style.display = match ? "" : "none";
        if (match) visibleCount++;
      });

      if (noData) {
        if (filter && visibleCount === 0) {
          noData.style.display = "";
          noData.textContent = "No results found.";
        } else {
          noData.style.display = visibleCount === 0 && !filter ? "" : "none";
          noData.textContent = visibleCount === 0 && !filter ? noData.textContent : "";
        }
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener("input", filterTables);
  }

  // 👇 reset kapag nagpalit ng tab
  const tabButtons = document.querySelectorAll(".tab-button");
  tabButtons.forEach(button => {
    button.addEventListener("click", () => {
      setTimeout(() => filterTables(), 50);
    });
  });
});

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
            message = "To apply for an Intellectual Property, gather your documents, fill the form, and submit it to Create Application.";
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
    addBubble("Welcome to ITSO - LSPU LBC. We are here to help you.", "system");

    setTimeout(() => {
        addBubble("Please enter your Tracking ID below to track your application:", "system");
        chatInput.focus();
    }, 1000);
}

chatToggle.addEventListener('click', () => {
    chatBox.style.display = 'flex';
    showWelcome();
});


function showTab(tabId, element) {
  // 1. Remove active class sa lahat ng tabs
  document.querySelectorAll('.arrow-tab').forEach(tab => {
    tab.classList.remove('active');
  });

  // 2. I-activate yung kasalukuyang tab
  element.classList.add('active');

  // 3. I-hide lahat ng tab-content
  document.querySelectorAll('.tab-content').forEach(content => {
    content.style.display = 'none';
  });

  // 4. Ipakita lang yung pinili mo
  const target = document.getElementById(tabId);
  if (target) {
    target.style.display = 'block';
  }
}

const notifModal = document.getElementById('notifModal');
const modalContent = notifModal.querySelector('.glass-modal');

// When clicking outside modal content
notifModal.addEventListener('click', function(e) {
  // Check if clicked target is the overlay itself
  if (e.target === notifModal) {
    notifModal.style.display = 'none';
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const scrollTopBtn = document.getElementById("scrollTopBtn");

  // Show button when scrolled even a little
  window.addEventListener("scroll", () => {
    if (window.scrollY > 20) {
      scrollTopBtn.style.display = "block";
    } else {
      scrollTopBtn.style.display = "none";
    }
  });

  // Smooth scroll to top
  scrollTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  });
});

async function loadPendingApplications() {
  try {
    const res = await fetch('admin_includes/fetch_pending_applications.php?t=' + new Date().getTime());
    const data = await res.json();
    const tableBody = document.querySelector('#tab-pending .table-body');
    if (!tableBody) return;

    let newHTML = '';
    if (data.length > 0) {
      data.forEach(row => {
        newHTML += `
          <div class="table-row-card">
            <div data-label="Title">
              <strong>${row.title}</strong><br>
              <small class="tracking-id" onclick="copyToClipboard('${row.tracking_id}', this)">
                <ion-icon name="copy-outline" style="font-size: 12px;"></ion-icon>
                ${row.tracking_id}
              </small>
            </div>
            <div data-label="Classification">${row.classification}</div>
            <div data-label="Status"><span class="status-badge status-pending">${row.status}</span></div>
            <div data-label="Campus">${row.campus_name}</div>
            <div data-label="Action">
              <form method="POST" action="update_status.php" style="display:inline;">
                <input type="hidden" name="ip_id" value="${row.ip_id}">
                <input type="hidden" name="new_status" value="Ongoing">
                <input type="hidden" name="source" value="admin_dashboard.php?page=all_applications">
                <button type="submit" class="accept-btn"><ion-icon name="checkmark-circle-outline"></ion-icon></button>
              </form>
              <form method="POST" action="delete.php" onsubmit="return confirm('Delete this record?')" style="display:inline;">
                <input type="hidden" name="ip_id" value="${row.ip_id}">
                <button type="submit" class="delete-btn"><ion-icon name="trash-outline"></ion-icon></button>
              </form>
            </div>
          </div>`;
      });
    } else {
      newHTML = `<p class="no-data">No pending applications</p>`;
    }

    // Update only if changed
    if (tableBody.innerHTML.trim() !== newHTML.trim()) {
      tableBody.innerHTML = newHTML;
    }
  } catch (err) {
    console.error('Error fetching pending apps:', err);
  }
}

// Run every 2 seconds
setInterval(loadPendingApplications, 2000);
loadPendingApplications();


</script>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>