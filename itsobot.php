<?php
session_start();
header('Content-Type: application/json');
include 'db_connect.php';

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$tracking_id = $_POST['tracking_id'] ?? $_GET['tracking_id'] ?? '';

if (empty($tracking_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide a TRACKING ID."
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT title, status, date_submitted_to_itso, date_submitted_to_ipophil, expiration_date 
                        FROM intellectual_properties 
                        WHERE tracking_id = ? LIMIT 1");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare statement: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $tracking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $title = $row['title'];
    $status = $row['status'];
    $expiration_date = $row['expiration_date'];

    // Format ITSO date
    $itso_date = !empty($row['date_submitted_to_itso']) && $row['date_submitted_to_itso'] !== '0000-00-00'
        ? date("F j, Y", strtotime($row['date_submitted_to_itso']))
        : "N/A";

    // Format IPOPHIL date
    if (empty($row['date_submitted_to_ipophil']) || $row['date_submitted_to_ipophil'] === '0000-00-00') {
        $ipophil_date = "N/A";
    } else {
        $ipophil_date = date("F j, Y", strtotime($row['date_submitted_to_ipophil']));
    }

    // Check kung expired
    if (!empty($expiration_date) && strtotime($expiration_date) < time()) {
        // Optional: auto-update status
        if ($status !== 'Expired') {
            $update = $conn->prepare("UPDATE intellectual_properties SET status='Expired' WHERE tracking_id=?");
            $update->bind_param("s", $tracking_id);
            $update->execute();
            $update->close();
        }

        echo json_encode([
            "success" => true,
            "message" => "Your application '{$title}' has expired on " . date("F j, Y", strtotime($expiration_date)) . "."
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Application '{$title}' is currently '{$status}'. Submitted to ITSO on {$itso_date} and to IPOPHIL on {$ipophil_date}."
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Tracking ID not found. Please check and try again."
    ]);
}

$stmt->close();
$conn->close();
?>
