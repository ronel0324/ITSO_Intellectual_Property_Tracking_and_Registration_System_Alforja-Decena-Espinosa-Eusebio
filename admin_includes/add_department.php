<?php
include '../db_connect.php';
session_start();
$data = json_decode(file_get_contents('php://input'), true);

$campus_id = $_SESSION['campus_id'] ?? 0;
$department_name = trim($data['department_name']);

if(!$department_name) { echo json_encode(['success'=>false,'message'=>'Empty name']); exit; }

$stmt = $conn->prepare("INSERT INTO departments (campus_id, department_name) VALUES (?, ?)");
$stmt->bind_param("is", $campus_id, $department_name);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'DB error']);
}
?>
