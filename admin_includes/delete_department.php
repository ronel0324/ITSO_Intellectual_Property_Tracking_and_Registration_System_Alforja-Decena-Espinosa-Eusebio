<?php
include '../db_connect.php';
session_start();

$campus_id = $_SESSION['campus_id'] ?? 0;
$department_id = $_POST['department_id'] ?? 0;

if (!$campus_id || !$department_id) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ? AND campus_id = ?");
$stmt->bind_param("ii", $department_id, $campus_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
