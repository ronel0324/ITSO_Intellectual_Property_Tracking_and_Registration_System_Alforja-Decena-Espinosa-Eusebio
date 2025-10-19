<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

if ($_SESSION['role'] !== 'Chairperson' && $_SESSION['role'] !== 'Director') {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Manager</title>
    <style>
body {
    font-family: sans-serif;
    background-color: #f8fafc;
    color: #333;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    margin-top: 40px;
    color: #28453D;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

table {
    border-collapse: collapse;
    width: 90%;
    margin: 30px auto;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

th {
    background-color: #28453D;
    color: #fff;
    text-transform: uppercase;
    font-size: 14px;
    padding: 14px;
}

td {
    padding: 12px;
    text-align: center;
    font-size: 15px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background-color: #f1f8f6;
    transition: 0.2s ease-in-out;
}

button {
    background-color: #2b7a78; 
    color: white; 
    padding: 8px 15px; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
    transition: 0.3s ease;
    font-size: 14px;
    margin: 2px;
}

button:hover {
    background-color: #3aafa9;
}

button.delete {
    background-color: #f05454;
}

button.delete:hover {
    background-color: #e43f3f; 
}

button.cancel {
    background-color: #ccc;
    color: #333; 
}

button.cancel:hover {
    background-color: #bbb; 
}

.modal {
    display:none; 
    position:fixed; 
    top:0; left:0; 
    width:100%; height:100%; 
    background:rgba(0,0,0,0.5); 
    justify-content:center; 
    align-items:center;
}

.modal > div {
    background:#fff; 
    padding:25px; 
    width:320px; 
    border-radius:12px; 
    box-shadow:0 6px 12px rgba(0,0,0,0.15);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

label {
    display: block;
    margin-bottom: 5px;
    color: #28453D;
    font-weight: 500;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    font-size: 14px;
}

input[type="text"]:focus, input[type="password"]:focus {
    outline: none;
    border-color: #2b7a78;
    box-shadow: 0 0 4px rgba(43,122,120,0.4);
}
</style>

</head>
<body>

<h2>Pending User Accounts</h2>
<table>
    <tr>
        <th>ID</th><th>Username</th><th>Campus</th><th>Department</th><th>Role</th><th>Status</th><th>Actions</th>
    </tr>
    <?php
    $pending = $conn->query("
        SELECT u.*, c.campus_name, d.department_name
        FROM users u
        LEFT JOIN campuses c ON u.campus_id = c.campus_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        WHERE u.status = 'pending' AND u.role != 'Admin'
    ");

    while ($user = $pending->fetch_assoc()):
    ?>
    <tr>
        <td><?= $user['id']; ?></td>
        <td><?= htmlspecialchars($user['username']); ?></td>
        <td><?= $user['campus_name']; ?></td>
        <td><?= !empty($user['department_name']) ? htmlspecialchars($user['department_name']) : 'N/A'; ?></td>
        <td><?= $user['role']; ?></td>
        <td><?= $user['status']; ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit">Approve</button>
            </form>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit">Reject</button>
            </form>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" onclick="return confirm('Delete this user?')">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<br><br><br>

<h2>Approved Accounts</h2>
<table>
    <tr>
        <th>ID</th><th>Username</th><th>Campus</th><th>Department</th><th>Role</th><th>Actions</th>
    </tr>
    <?php
    $approved = $conn->query("
        SELECT u.*, c.campus_name, d.department_name
        FROM users u
        LEFT JOIN campuses c ON u.campus_id = c.campus_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        WHERE u.status = 'approved' AND u.role != 'Admin'
    ");

    while ($user = $approved->fetch_assoc()):
    ?>
    <tr>
        <td><?= $user['id']; ?></td>
        <td><?= htmlspecialchars($user['username']); ?></td>
        <td><?= $user['campus_name']; ?></td>
        <td><?= !empty($user['department_name']) ? htmlspecialchars($user['department_name']) : 'N/A'; ?></td>
        <td><?= $user['role']; ?></td>
        <td>
            <form action="edit_account.php" method="GET">
                <input type="hidden" name="id" value="<?= $user['id']; ?>">
                <button type="button" onclick="openModal('<?= $user['id']; ?>', '<?= $user['username']; ?>')">Edit</button>
            </form>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="delete" onclick="return confirm('Delete this user?')"><ion-icon name="trash-outline"></ion-icon></button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; 
    width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; width:300px; border-radius:8px; position:relative;">
        <h3>Edit Account</h3>
        <form method="POST" action="update_account.php">
            <input type="hidden" name="id" id="editId">
            
            <label>Username:</label>
            <input type="text" name="username" id="editUsername" required><br><br>

            <label>New Password:</label>
            <input type="password" name="password"><br><br>

            <button type="submit">Update</button>
            <button type="button" class="cancel" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
    function openModal(id, username) {
        document.getElementById("editId").value = id;
        document.getElementById("editUsername").value = username;
        document.getElementById("editModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    window.onclick = function(event) {
        const modal = document.getElementById("editModal");
        if (event.target === modal) {
            closeModal();
        }
    }

    function reloadTables() {
  fetch(window.location.href)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");

      // Kunin ulit yung tbody sections
      const pendingRows = doc.querySelectorAll("table:nth-of-type(1) tbody tr");
      const approvedRows = doc.querySelectorAll("table:nth-of-type(2) tbody tr");

      // Replace content nang hindi nire-reload buong page
      document.querySelector("table:nth-of-type(1) tbody").innerHTML = "";
      document.querySelector("table:nth-of-type(2) tbody").innerHTML = "";

      pendingRows.forEach(row => document.querySelector("table:nth-of-type(1) tbody").appendChild(row));
      approvedRows.forEach(row => document.querySelector("table:nth-of-type(2) tbody").appendChild(row));
    });
}

// Auto-refresh every 1 second
setInterval(reloadTables, 1000);
</script>


</body>
</html>
