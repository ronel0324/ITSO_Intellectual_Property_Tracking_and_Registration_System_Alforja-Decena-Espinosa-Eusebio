<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department_id = $_POST['department_id'];
    $role = $_POST['role'];
    $campus_id = $_POST['campus'];
    $status = "pending";

    $check_sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: register.php?error=" . urlencode("This username is already taken."));
        exit();
    }

    $sql = "INSERT INTO users (username, campus_id, password, department_id, role, status)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissss", $username, $campus_id, $password, $department_id, $role, $status);

    if ($stmt->execute()) {
        header("Location: login.php?success=" . urlencode("Registered successfully! You can now log in."));
        exit();
    } else {
        header("Location: register.php?error=" . urlencode("Something went wrong. Please try again."));
        exit();
    }

    $stmt->close();
}
$conn->close();
?>