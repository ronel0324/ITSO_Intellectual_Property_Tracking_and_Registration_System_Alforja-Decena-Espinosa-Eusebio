<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ip_id'])) {
    $ip_id = intval($_POST['ip_id']);

    $sql = "DELETE FROM intellectual_properties WHERE ip_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ip_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?page=dashboard_content");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
