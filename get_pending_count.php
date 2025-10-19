<?php
include 'db_connect.php';
$sql = "SELECT COUNT(*) AS total FROM users WHERE status='pending' AND role != 'Admin'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
echo $row['total'];
?>