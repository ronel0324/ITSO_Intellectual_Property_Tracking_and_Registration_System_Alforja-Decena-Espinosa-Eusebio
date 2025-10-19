<?php

$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;

// Query for monthly data (filtered by selected year)
$monthlyData = [];
$query = "
SELECT MONTH(date_submitted_to_itso) AS month, COUNT(*) AS total 
FROM intellectual_properties 
WHERE date_submitted_to_itso IS NOT NULL 
  AND YEAR(date_submitted_to_itso) = $selectedYear
GROUP BY MONTH(date_submitted_to_itso)
";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $monthlyData[(int)$row['month']] = (int)$row['total'];
}

// Ensure all 12 months are present
for ($m = 1; $m <= 12; $m++) {
    if (!isset($monthlyData[$m])) {
        $monthlyData[$m] = 0;
    }
}

// Sort by month
ksort($monthlyData);

$all_count = $conn->query("SELECT COUNT(*) AS total FROM intellectual_properties")->fetch_assoc()['total'];
$approved_count = $conn->query("SELECT COUNT(*) AS total FROM intellectual_properties WHERE status='completed'")->fetch_assoc()['total'];
$pending_count = $conn->query("SELECT COUNT(*) AS total FROM intellectual_properties WHERE status='pending'")->fetch_assoc()['total'];
$active_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE status='approved'")->fetch_assoc()['total'];

?>

<div style="background: #e2f6fc56; padding: 25px; border-radius: 10px;">
    
<!-- Card Section -->
<div class="cardBox">

    <!-- All Data -->
    <div class="card all">
        <div>
            <div class="numbers"><?php echo $all_count; ?></div>
            <div class="cardName">All Data</div>
        </div>
        <div class="iconBx">
            <ion-icon name="file-tray-full-outline"></ion-icon>
        </div>
    </div>

    <!-- Approved -->
    <div class="card completed">
        <div>
            <div class="numbers"><?php echo $approved_count; ?></div>
            <div class="cardName">Approved</div>
        </div>
        <div class="iconBx">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
        </div>
    </div>

    <!-- Pending Review -->
    <div class="card pending">
        <div>
            <div class="numbers"><?php echo $pending_count; ?></div>
            <div class="cardName">Pending Review</div>
        </div>
        <div class="iconBx">
            <ion-icon name="alert-circle-outline"></ion-icon>
        </div>
    </div>

    <!-- Active Users -->
    <div class="card ongoing">
        <div>
            <div class="numbers"><?php echo $active_users; ?></div>
            <div class="cardName">Active Users</div>
        </div>
        <div class="iconBx">
            <ion-icon name="people-circle-outline"></ion-icon>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="charts" style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
    <div style="flex: 1; min-width: 250px; background: #f0f0f035; padding: 10px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.42);">
        <h4 style="text-align:center; font-size:14px; margin-bottom:8px;">📊 Applications Overview (Pie)</h4>
        <canvas id="pieChart" style="max-height:220px;"></canvas>
    </div>

    <div style="flex: 1; min-width: 250px; background: #f0f0f035; padding: 10px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.42);">
    <?php
// Fetch available years from database for dropdown
$years = [];
$yearQuery = "SELECT DISTINCT YEAR(date_submitted_to_itso) AS year 
              FROM intellectual_properties 
              WHERE date_submitted_to_itso IS NOT NULL 
              ORDER BY year DESC";
$yearResult = $conn->query($yearQuery);
while ($row = $yearResult->fetch_assoc()) {
    $years[] = $row['year'];
}
?>

<!-- Year filter -->
<form method="GET" style="margin-bottom: 10px; text-align:center;">
    <label for="year" style="font-size:14px;"><ion-icon name="calendar-outline"></ion-icon> Select Year:</label>
    <select name="year" id="year" onchange="this.form.submit()" 
        style="padding:4px 8px; border-radius:6px; border:1px solid #ccc;">
        <?php foreach ($years as $year): ?>
            <option value="<?php echo $year; ?>" 
                <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                <?php echo $year; ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>    
    <h4 style="text-align:center; font-size:14px; margin-bottom:8px;"></h4>
        <canvas id="barChart" style="max-height:220px;"></canvas>
    </div>
</div>

<!-- Recent Activity Section -->
<section class="recent-activity">
  <div class="section-header">
    <h2>Recent Activity</h2>
  </div>

<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

// Combine New Applications + Approved Applications
$query = "
(SELECT tracking_id AS id, title AS name, classification AS type, date_submitted_to_itso AS date_action, 'New Application Submitted' AS activity_type
 FROM intellectual_properties
 WHERE date_submitted_to_itso IS NOT NULL)
UNION ALL
(SELECT tracking_id AS id, title AS name, classification AS type, updated_at AS date_action, CONCAT('Status updated to ', status) AS activity_type
 FROM intellectual_properties
 WHERE updated_at IS NOT NULL)
ORDER BY date_action DESC
LIMIT 5
";


$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activity = htmlspecialchars($row['activity_type']);
        $name = htmlspecialchars($row['name'] ?? '');
        $type = htmlspecialchars($row['type'] ?? '');
        $date = !empty($row['date_action']) ? date("F d, Y", strtotime($row['date_action'])) : 'N/A';

        echo "
        <div class='activity-card'>
          <div class='activity-icon'>
            <i class='fas fa-bell'></i>
          </div>
          <div class='activity-details'>
            <h3>{$activity}</h3>
            <p><strong>Tracking ID:</strong> {$row['id']}</p>
            <p><strong>Title:</strong> {$name}</p>
            <p><strong>Type:</strong> {$type}</p>
            <p class='submitted'><ion-icon name='calendar-outline' style='vertical-align: middle; margin-right: 5px;'></ion-icon>{$date}</p>
          </div>
        </div>
        ";
    }
} else {
    echo '<p class="no-activity">No recent activities found.</p>';
}
?>
</section>

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

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script>
    // PHP counts passed into JS
    const completed = <?php echo $approved_count; ?>;
    const pending = <?php echo $rejected_count; ?>;
    const ongoing = <?php echo $ongoing_count; ?>;
    const total = <?php echo $all_count; ?>;

    // Pie Chart
    new Chart(document.getElementById("pieChart"), {
        type: "pie",
        data: {
            labels: ["Completed", "Pending", "Ongoing"],
            datasets: [{
                data: [completed, pending, ongoing],
                backgroundColor: ["#2ecc7090", "#f1c40f90", "#3498db90"],
                borderColor: "#fff",
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });

    // Bar Chart
const ctxBar = document.getElementById('barChart').getContext('2d');

new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: [
            "Jan","Feb","Mar","Apr","May","Jun",
            "Jul","Aug","Sep","Oct","Nov","Dec"
        ],
        datasets: [{
            label: "Submissions per Month",
            data: <?php echo json_encode(array_values($monthlyData)); ?>,
            backgroundColor: "rgba(54, 162, 235, 0.6)",
            borderColor: "rgba(54, 162, 235, 0.6)",
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: "📈 Intellectual Properties Submitted per Month",
                font: { size: 14 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
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

function toggleNotifDropdown() {
  const dropdown = document.getElementById('notifDropdown');
  dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

// auto-close dropdown pag click sa labas
window.addEventListener('click', function(e) {
  if (!e.target.closest('.notif-bell')) {
    document.getElementById('notifDropdown').style.display = 'none';
  }
});
</script>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
