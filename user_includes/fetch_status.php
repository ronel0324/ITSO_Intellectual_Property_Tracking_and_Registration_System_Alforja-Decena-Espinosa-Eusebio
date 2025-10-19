<?php
session_start();
include __DIR__ . '/../db_connect.php';
header('Content-Type: application/json');

// Only allow coordinators
$role = $_SESSION['role'] ?? '';
if ($role !== 'Coordinator') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$department_id = $_SESSION['department_id'] ?? '';
$campus_id = $_SESSION['campus_id'] ?? '';

// Fetch all IP records for this coordinator including remarks
$query = "SELECT ip_id, status, remarks FROM intellectual_properties WHERE department_id = ? AND campus_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $department_id, $campus_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
