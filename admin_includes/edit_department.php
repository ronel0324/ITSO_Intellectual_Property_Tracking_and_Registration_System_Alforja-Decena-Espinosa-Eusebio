<?php
include '../db_connect.php';
session_start();

$campus_id = $_SESSION['campus_id'] ?? 0;
$department_id = $_POST['department_id'] ?? 0;
$department_name = trim($_POST['department_name'] ?? '');

if(!$department_id || !$department_name){
    echo json_encode(['success'=>false, 'message'=>'Missing data']);
    exit;
}

$stmt = $conn->prepare("UPDATE departments SET department_name=? WHERE department_id=? AND campus_id=?");
$stmt->bind_param("sii", $department_name, $department_id, $campus_id);

if($stmt->execute()){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>'DB error']);
}
?>

