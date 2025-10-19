<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "ip_monitoring";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// AUTO-CREATE ACCOUNT (superadmin)
$checkBackup = "SELECT * FROM users WHERE username = 'superadmin' LIMIT 1";
$result = $conn->query($checkBackup);

if ($result && $result->num_rows == 0) {
    $defaultUsername = 'superadmin';
    $defaultPassword = password_hash('superadmin2025', PASSWORD_DEFAULT);
    $defaultEmail = 'superadmin@example.com'; // optional
    $defaultStatus = 'Approved'; // auto-approved

    // Insert backup account (auto-approved)
    $insert = $conn->prepare("INSERT INTO users (username, password, status) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $defaultUsername, $defaultPassword, $defaultStatus);
    $insert->execute();
}

$departmentNames = [
    'CCS' => 'College of Computer Studies',
    'CFND' => 'College of Food Nutrition and Dietetics',
    'CIT' => 'College of Industrial Technology',
    'CTE' => 'College of Teacher Education',
    'CA' => 'College of Agriculture',
    'CAS' => 'College of Arts and Sciences',
    'CBAA' => 'College of Business Administration and Accountancy',
    'COE' => 'College of Engineering',
    'CCJE' => 'College of Criminal Justice Education',
    'COF' => 'College of Fisheries',
    'CHMT' => 'College of Hospitality Management and Tourism',
    'CNAH' => 'College of Nursing and Allied Health',
];

?>
