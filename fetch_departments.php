<?php
include 'db_connect.php';

$campus_id = isset($_GET['campus_id']) ? (int) $_GET['campus_id'] : 0;

$stmt = $conn->prepare("SELECT department_id, department_name FROM departments WHERE campus_id = ?");
$stmt->bind_param("i", $campus_id);
$stmt->execute();
$result = $stmt->get_result();

$departments = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($departments);
?>
