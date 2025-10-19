<?php
session_start();
include __DIR__ . '/../db_connect.php';

$department_id = $_SESSION['department_id'] ?? '';

/* -------------------- MARK ALL AS READ -------------------- */
if (isset($_GET['mark_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE department_id = ?");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    echo json_encode(['success' => true]); // so frontend knows it worked
    exit;
}


/* -------------------- FETCH NOTIFICATIONS -------------------- */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE department_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'time' => date("M d, Y h:i A", strtotime($row['created_at'])),
            'is_read' => $row['is_read']
        ];
    }

    echo json_encode($notifications);
    exit;
}

/* HTML page load for coordinator */
$department_id = $_SESSION['department_id'] ?? '';
$campus = $_SESSION['campus_id'] ?? '';

$conditions = [];
$params = [];
$types = "";

if (!empty($department_id)) {
    $conditions[] = "ip.department_id = ?";
    $types .= "i";
    $params[] = $department_id;
}
if (!empty($campus)) {
    $conditions[] = "ip.campus_id = ?";
    $types .= "i";
    $params[] = $campus;
}

$where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

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
$department_name_display = "N/A";

if ($result->num_rows > 0) {
    $first_row = $result->fetch_assoc();
    $campus_name_display = $first_row['campus_name'] ?? "N/A";
    $department_name_display = $first_row['department_name'] ?? "N/A";
    $result->data_seek(0);
}
?>

