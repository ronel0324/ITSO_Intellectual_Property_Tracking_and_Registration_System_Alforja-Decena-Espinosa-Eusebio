<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

// Collect form inputs
$title = $_POST['title'] ?? '';
$authors = $_POST['authors'] ?? [];
if (!is_array($authors)) $authors = [$authors];
$authorsList = implode(", ", $authors);

$email = $_POST['email'] ?? '';
$applicant_name = $_POST['applicant_name'] ?? '';
$classification = $_POST['classification'] ?? '';
$status = $_POST['status'] ?? '';
$date_submitted_to_itso = $_POST['date_submitted_to_itso'] ?? null;
$submitted = isset($_POST['submitted_to_ipophil']) ? 1 : 0;
$date_submitted_to_ipophil = $_POST['date_submitted_to_ipophil'] ?? null;
$department_id = $_POST['department_id'] ?? '';
$campus_id = $_POST['campus_id'] ?? null;

date_default_timezone_set('Asia/Manila');

if (!empty($date_submitted_to_itso)) {
    $date_submitted_to_itso .= ' ' . date('H:i:s'); 
} else {
    $date_submitted_to_itso = date('Y-m-d H:i:s');
}

// Expiration calculation
function calculateExpiration($classification, $date_submitted_to_ipophil) {
    if (empty($date_submitted_to_ipophil)) return null;
    $years = [
        "Copyright" => 50,
        "Patent" => 20,
        "Trademark" => 10,
        "Utility Model" => 7,
        "Industrial Design" => 5
    ];
    return date('Y-m-d', strtotime("+{$years[$classification]} years", strtotime($date_submitted_to_ipophil)));
}

$expiration_date = calculateExpiration($classification, $date_submitted_to_ipophil);

// File upload function
function uploadFile($fileInput) {
    if (!empty($_FILES[$fileInput]['name'])) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES[$fileInput]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInput]["tmp_name"], $targetFilePath)) return $fileName;
        else echo "Failed to upload file: $fileName<br>";
    }
    return null;
}

$application_form    = uploadFile("application_form");
$endorsement_letter  = uploadFile("endorsement_letter");
$application_fee     = uploadFile("application_fee");
$issued_certificate  = uploadFile("issued_certificate");
$project_file        = uploadFile("project_file");

$conn->query("SET time_zone = '+08:00'");

$sql = "INSERT INTO intellectual_properties 
(title, authors, email, applicant_name, classification, endorsement_letter, status, application_form, submitted, application_fee, issued_certificate, project_file, date_submitted_to_itso, date_submitted_to_ipophil, expiration_date, department_id, campus_id) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param(
    "ssssssssissssssii", 
    $title, 
    $authorsList, 
    $email, 
    $applicant_name, 
    $classification, 
    $endorsement_letter, 
    $status, 
    $application_form, 
    $submitted, 
    $application_fee, 
    $issued_certificate, 
    $project_file, 
    $date_submitted_to_itso, 
    $date_submitted_to_ipophil, 
    $expiration_date, 
    $department_id,
    $campus_id
);

if ($stmt->execute()) {
    $lastId = $stmt->insert_id;
    $year = date("Y");
    $tracking_id = "ITSO-" . $year . "-" . str_pad($lastId, 3, "0", STR_PAD_LEFT);

    $updateSql = "UPDATE intellectual_properties SET tracking_id = ? WHERE ip_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $tracking_id, $lastId);
    $updateStmt->execute();
    $updateStmt->close();

    $_SESSION['tracking_id'] = $tracking_id;
    $_SESSION['success_message'] = "Successfully added! Tracking ID: $tracking_id";

    // Redirect based on role
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Chairperson') {
        header("Location: admin_dashboard.php?page=create_application");
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Coordinator') {
        header("Location: coordinator_dashboard.php?page=create_application");
    } else {
        header("Location: login.php");
    }
    exit;
} else {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
