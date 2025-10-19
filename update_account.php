<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $hashedPassword, $id);
    } else {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Account updated successfully.'); window.location.href='admin_dashboard.php?page=acc_manager';</script>";
    } else {
        echo "Error updating account.";
    }

    $stmt->close();
}
$conn->close();
?>
