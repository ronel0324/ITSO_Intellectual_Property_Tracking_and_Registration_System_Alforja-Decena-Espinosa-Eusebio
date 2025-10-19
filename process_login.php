<?php
ini_set('session.cookie_lifetime', 0);

session_start();

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT u.*, c.campus_name 
            FROM users u 
            LEFT JOIN campuses c ON u.campus_id = c.campus_id
            WHERE u.username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            // Check account status
            if ($row['status'] === 'approved') {
                // Store all user info in session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['department_id'] = $row['department_id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['campus_id'] = $row['campus_id'];
                $_SESSION['campus_name'] = $row['campus_name'];

                // Redirect based on role
                if ($row['role'] == "Chairperson" || $row['role'] == "Director") {
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
            exit();
        }
    } else {
        echo "<script>alert('No user found. Please check your username.'); window.location.href='login.php';</script>";
        exit();
    }

    $stmt->close();
}
$conn->close();
?>
