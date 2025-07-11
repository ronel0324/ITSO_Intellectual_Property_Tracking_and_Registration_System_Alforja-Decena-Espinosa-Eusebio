<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            if ($row['status'] === 'approved') {
                $_SESSION['username'] = $row['username'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['role'] = $row['role'];

                if ($row['role'] == "Admin") {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } elseif ($row['status'] === 'pending') {
                echo "<script>alert('Your account is still pending approval by the ITSO admin.'); window.location.href='login.php';</script>";
                exit();
            } elseif ($row['status'] === 'rejected') {
                echo "<script>alert('Your account has been rejected. Please contact the ITSO admin.'); window.location.href='login.php';</script>";
                exit();
            } else {
                echo "<script>alert('Unknown account status. Please contact support.'); window.location.href='login.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid password. Please try again.'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('No user found. Please check your username.'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
