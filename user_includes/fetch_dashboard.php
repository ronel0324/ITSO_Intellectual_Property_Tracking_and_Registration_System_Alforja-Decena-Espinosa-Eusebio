<?php
session_start();
include '../db_connect.php';

// ---- Coordinator filters ----
$role = $_SESSION['role'] ?? '';
$department_id = $_SESSION['department_id'] ?? '';
$campus_id = $_SESSION['campus_id'] ?? '';

$hasFilter = ($role === 'Coordinator' && !empty($department_id) && $campus_id !== '');
$filter_sql = $hasFilter ? " AND department_id = ? AND campus_id = ?" : "";

// Response array
$response = [];

// ---- Table Statuses ----
$tableStatuses = [];
$sql = "SELECT ip_id, status FROM intellectual_properties WHERE 1=1 {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $tableStatuses[] = $row;
$stmt->close();
$response['tableStatuses'] = $tableStatuses;

// ---- Card Stats ----
$cards = [];

// Total
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE 1=1 {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$cards['total'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Pending
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE status='Pending' {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$cards['pending'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Ongoing
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE status='Ongoing' {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$cards['ongoing'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Completed
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE status='Completed' {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$cards['completed'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

// Granted = Completed
$cards['granted'] = $cards['completed'];

// Completed This Month (based on updated_at)
$sql = "SELECT COUNT(*) AS cnt 
        FROM intellectual_properties 
        WHERE status = 'Completed'
          AND YEAR(updated_at) = YEAR(CURDATE())
          AND MONTH(updated_at) = MONTH(CURDATE())
          {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) {
    $stmt->bind_param('si', $department_id, $campus_id);
}
$stmt->execute();
$cards['completedThisMonth'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();



// For Revision = Pending with remarks
$sql = "SELECT COUNT(*) AS cnt FROM intellectual_properties WHERE status='Pending' AND remarks IS NOT NULL AND TRIM(remarks)<>'' {$filter_sql}";
$stmt = $conn->prepare($sql);
if ($hasFilter) $stmt->bind_param('si', $department_id, $campus_id);
$stmt->execute();
$cards['forRevision'] = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$response['cards'] = $cards;

// ---- Recent Notifications ----
$recent = $conn->prepare("SELECT title, message, created_at FROM notifications WHERE 1=1 {$filter_sql} ORDER BY created_at DESC LIMIT 5");
if ($hasFilter) $recent->bind_param('si', $department_id, $campus_id);
$recent->execute();
$res = $recent->get_result();
$recentActivities = [];
while ($row = $res->fetch_assoc()) $recentActivities[] = $row;
$recent->close();
$response['recent'] = $recentActivities;

// ---- Return JSON ----
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
